<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesDamageItems;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\SalesDueCustomer;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Sale::with(['sr', 'delivery', 'customerDues'])->select('sales.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('sr_name', fn($row) => $row->sr->name ?? 'N/A')
                ->addColumn('delivery_name', fn($row) => $row->delivery->name ?? 'N/A')
                ->editColumn('route_no', fn($row) => $row->route_no ?? 'N/A')
                ->editColumn('sale_date', fn($row) => date('d M, Y', strtotime($row->sale_date)))
                ->editColumn('total_amount', fn($row) => number_format($row->total_amount, 2))
                ->editColumn('paid_amount', fn($row) => number_format($row->paid_amount, 2))

                // This is the total due of the Invoice
                ->editColumn('due_amount', fn($row) => number_format($row->due_amount, 2))

                ->addColumn('action', function ($row) {
                    // Calculate how much is left to assign to customers
                    $alreadyAssigned = $row->customerDues->sum('due_amount');
                    $remainingToAssign = $row->due_amount - $alreadyAssigned;

                    return '
                <div class="btn-group shadow-sm">
                  
                    <a href="' . route('sales.show', $row->id) . '" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="' . route('sales.edit', $row->id) . '" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $row->id . '">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>';
                })
                ->addColumn('due_details', function ($row) {
                    $count = $row->customerDues->count();
                    $total = number_format($row->customerDues->sum('due_amount'), 2);
                    $colorClass = ($row->due_amount > $row->customerDues->sum('due_amount')) ? 'text-danger' : 'text-success';

                    return "<small class='text-muted'>DSR Assigned</small><br>
                        <span class='{$colorClass}'>Assigned: $total</span>";
                })
                ->rawColumns(['action', 'due_amount', 'due_details'])
                ->make(true);
        }
        return view('sales.index');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 1. Fetch Employees instead of Customers
        // You can filter by role if your table has it: Employee::where('role', 'SR')->get()
        $employees = \App\Models\Employee::all();

        // 2. Fetch Products
        $products = \App\Models\Product::where('stock', '>', 0)->get();

        // 3. Pass to view (Variable name 'employees' matches the @foreach in the Blade fix provided earlier)
        return view('sales.create', compact('employees', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $request->validate([
            'delivery_id'  => 'required|exists:employees,id',
            'sr_id'        => 'required|exists:employees,id',
            'route_no'     => 'required|string',
            'sale_date'    => 'required|date',
            'product_id'   => 'required|array|min:1',
            'qty'          => 'required|array',
            'qty.*'        => 'required|numeric|min:1',
            'price'        => 'required|array',
            'discount'     => 'nullable|numeric|min:0',
        ]);

        try {
            $sale = DB::transaction(function () use ($request) {

                $calculatedSubtotal = 0;
                $calculatedExtra = 0;
                $totalDamageAmount = 0;
                $itemsToProcess = [];

                // 2. Initial Loop: Validate Stock & Fetch Products
                foreach ($request->product_id as $key => $p_id) {
                    $qty = (float) $request->qty[$key];
                    $unitPrice = (float) $request->price[$key];
                    $lineTotal = $qty * $unitPrice;
                    $calculatedSubtotal += $lineTotal;

                    // Lock product row to prevent race conditions on stock
                    $product = \App\Models\Product::lockForUpdate()->findOrFail($p_id);

                    if ($product->stock < $qty) {
                        throw new \Exception("Stock insufficient for: {$product->name} (Available: {$product->stock})");
                    }

                    $itemsToProcess[] = [
                        'product_id' => $p_id,
                        'quantity'   => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal'   => $lineTotal,
                    ];
                }

                // 2.1 damege total
                if ($request->has('items_02')) {
                    foreach ($request->items_02 as $item) {
                        $totalDamageAmount += ($item['unit_price_02'] * $item['quantity_02']);
                    }
                }



                // 3. Financial Calculations
                $discount    = (float) ($request->discount ?? 0);
                $targetAmount    = (float) ($request->targetAmount ?? 0);
                $totalAmount = round($calculatedSubtotal - ($discount + $totalDamageAmount), 2);
                if ($targetAmount > 0) {
                    $calculatedExtra = $totalAmount - $targetAmount;
                }
                $paidAmount  = $request->paid_amount ?? 0;
                $dueAmount   = $totalAmount - $paidAmount;


                // 4. Create the Master Sale (Removed customer_id, Added Delivery/SR/Route)
                $sale = Sale::create([
                    'invoice_no'     => 'INV-' . strtoupper(Str::random(4)) . time(),
                    'delivery_id'    => $request->delivery_id,
                    'sr_id'          => $request->sr_id,
                    'route_no'       => $request->route_no,
                    'sale_date'      => $request->sale_date,
                    'discount'       => $discount,
                    'total_amount'   => $totalAmount,
                    'total_damage'   => $totalDamageAmount,
                    'target_amount'   => $targetAmount,
                    'extra_amount'   => $calculatedExtra,
                    'paid_amount'    => $paidAmount,
                    'due_amount'     => $dueAmount,
                    'payment_status' => 'pending',
                    'remarks'        => $request->remarks,
                    'user_id'        => Auth::id(),
                ]);

                // 5. Process Items and Stock Movements via Service
                foreach ($itemsToProcess as $item) {
                    // Save SaleItem
                    $sale->items()->create($item);

                    // Update Stock using Service
                    \App\Services\StockService::updateStock(
                        $item['product_id'],
                        $item['quantity'],
                        'sale',
                        $sale->invoice_no,
                        "Sale recorded: {$sale->invoice_no}"
                    );
                }

                // 5.1. Create New Items & Update Stock using updateStock
                if ($request->has('items_02')) {
                    foreach ($request->items_02 as $itemData) {
                        $sale->damageItems()->create([
                            'product_id' => $itemData['product_id_02'],
                            'unit_price' => $itemData['unit_price_02'],
                            'quantity'   => $itemData['quantity_02'],
                            'subtotal'   => $itemData['unit_price_02'] * $itemData['quantity_02'],
                        ]);

                        // Updated Stock Logic as requested
                        \App\Services\StockService::updateStock(
                            $itemData['product_id_02'],
                            $itemData['quantity_02'],
                            'return',
                            $sale->invoice_no,
                            "Updated Sale Invoice: {$sale->invoice_no}"
                        );
                    }
                }

                //6. DSR Due / customer Due
                if ($dueAmount == 0) {
                    //nothing
                } else {
                    \App\Models\SalesDueCustomer::create([
                        'sale_id'     => $sale->id,
                        'customer_id' => $sale->delivery_id,
                        'due_amount'  => $sale->due_amount,
                        'note'        => null,
                    ]);
                }


                return $sale;
            });

            return redirect()->route('sales.index')
                ->with('success', "Sale Invoice {$sale->invoice_no} created successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    //add sales due customer amount
    public function storeDueCustomer(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'customer_id' => 'required|array',
            'due_amount' => 'required|array',
            'due_amount.*' => 'numeric|min:0.01',
        ]);

        $sale = Sale::findOrFail($request->sale_id);
        $totalAssignedRequest = array_sum($request->due_amount);

        // 1. Check if the assigned amount exceeds what the sale actually owes
        if ($totalAssignedRequest > $sale->due_amount) {
            return response()->json([
                'error' => 'The total assigned customer dues (' . $totalAssignedRequest . ') exceeds the sale due amount (' . $sale->due_amount . ').'
            ], 422);
        }

        // 2. Clear existing dues if you want to overwrite, or simply append
        // $sale->customerDues()->delete(); 

        foreach ($request->customer_id as $key => $c_id) {
            if (!empty($c_id) && $request->due_amount[$key] > 0) {
                \App\Models\SalesDueCustomer::create([
                    'sale_id'     => $request->sale_id,
                    'customer_id' => $c_id,
                    'due_amount'  => $request->due_amount[$key],
                    'note'        => $request->note[$key] ?? null,
                ]);
            }
        }

        return response()->json(['success' => 'Dues assigned successfully.']);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Note: 'customer' is removed because it's no longer a direct relation on Sale.
        // We load 'customerDues.customer' instead.
        $sale = Sale::with([
            'sr',
            'delivery',
            'user',
            'items.product',
            'customerDues.customer'
        ])->findOrFail($id);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $sale = Sale::with(['items.product', 'damageItems.product', 'customerDues.customer'])->findOrFail($id);
        $employees = Employee::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('sales.edit', compact('sale', 'employees', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::with('items', 'damageItems')->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $sale) {
                // 1. Reverse ALL old stock movements first
                foreach ($sale->items as $item) {
                    \App\Services\StockService::reverseStock(
                        $item->product_id,
                        $item->quantity,
                        'sale_edit_reverse',
                        $sale->invoice_no,
                    );
                }
                // 1.1. Reverse ALL old stock movements first
                foreach ($sale->damageItems as $item) {
                    \App\Services\StockService::reverseStock(
                        $item->product_id,
                        $item->quantity,
                        'return',
                        $sale->invoice_no,
                    );
                }

                // 2. Remove old Items and old Customer Dues
                $sale->items()->delete();
                $sale->damageItems()->delete();
                $sale->customerDues()->delete();

                // 3. Calculate Financials
                $totalItemAmount = 0;
                $totalDamageAmount = 0;
                $extraAmount = 0;
                if ($request->has('items')) {
                    foreach ($request->items as $item) {
                        $totalItemAmount += ($item['unit_price'] * $item['quantity']);
                    }
                }
                if ($request->has('items_02')) {
                    foreach ($request->items_02 as $item) {
                        $totalDamageAmount += ($item['unit_price_02'] * $item['quantity_02']);
                    }
                }
                $grandTotal = $totalItemAmount - (($request->discount ?? 0) + $totalDamageAmount);
                $targetAmount = ($request->targetAmount ?? 0);
                if ($targetAmount > 0) {
                    $extraAmount = $grandTotal - $targetAmount;
                }
                $balance = $grandTotal -  $request->paid_amount ?? 0;

                if ($balance == 0) {
                    $paid_status = 'paid';
                } else {
                    $paid_status = 'partial';
                }

                // 4. Update Main Sale Record
                $sale->update([
                    'sale_date'      => $request->sale_date,
                    'sr_id'          => $request->sr_id,
                    'delivery_id'    => $request->delivery_id,
                    'route_no'       => $request->route_no,
                    'total_damage'   => $totalDamageAmount,
                    'total_amount'   => $grandTotal,
                    'target_amount'   => $targetAmount,
                    'extra_amount'   => $extraAmount,
                    'discount'       => $request->discount ?? 0,
                    'paid_amount'    => $request->paid_amount ?? 0,
                    'due_amount'     => $balance,
                    'remarks'        => $request->remarks,
                ]);

                // 5. Create New Items & Update Stock using updateStock
                if ($request->has('items')) {
                    foreach ($request->items as $itemData) {
                        $sale->items()->create([
                            'product_id' => $itemData['product_id'],
                            'unit_price' => $itemData['unit_price'],
                            'quantity'   => $itemData['quantity'],
                            'subtotal'   => $itemData['unit_price'] * $itemData['quantity'],
                        ]);

                        // Updated Stock Logic as requested
                        \App\Services\StockService::updateStock(
                            $itemData['product_id'],
                            $itemData['quantity'],
                            'sale',
                            $sale->invoice_no,
                            "Updated Sale Invoice: {$sale->invoice_no}"
                        );
                    }
                }
                // 5.1. Create New Items & Update Stock using updateStock
                if ($request->has('items_02')) {
                    foreach ($request->items_02 as $itemData) {
                        $sale->damageItems()->create([
                            'product_id' => $itemData['product_id_02'],
                            'unit_price' => $itemData['unit_price_02'],
                            'quantity'   => $itemData['quantity_02'],
                            'subtotal'   => $itemData['unit_price_02'] * $itemData['quantity_02'],
                        ]);

                        // Updated Stock Logic as requested
                        \App\Services\StockService::updateStock(
                            $itemData['product_id_02'],
                            $itemData['quantity_02'],
                            'return',
                            $sale->invoice_no,
                            "Updated Sale Invoice: {$sale->invoice_no}"
                        );
                    }
                }

                // 6. Create New Customer Due Allocation
                //6. DSR Due / customer Due
                if ($paid_status == 'paid') {
                    //nothing
                } else {
                    \App\Models\SalesDueCustomer::create([
                        'sale_id'     => $sale->id,
                        'customer_id' => $request->delivery_id,
                        'due_amount'  => $grandTotal - ($request->paid_amount ?? 0),
                        'note'        => null,
                        'status'        => $paid_status,
                    ]);
                }
            });

            return redirect()->route('sales.index')->with('success', 'Invoice and Stock updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Update Failed: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Eager load items and customerDues to handle everything in one go
        $sale = \App\Models\Sale::with(['items', 'customerDues'])->findOrFail($id);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($sale) {
                // 1. Loop through items to reverse stock
                foreach ($sale->items as $item) {
                    \App\Services\StockService::reverseStock(
                        $item->product_id,
                        $item->quantity,
                        'sale',
                        $sale->invoice_no
                    );
                }

                foreach ($sale->damageItems as $item) {
                    \App\Services\StockService::reverseStock(
                        $item->product_id,
                        $item->quantity,
                        'return',
                        $sale->invoice_no
                    );
                }

                // 2. Explicitly delete customer dues if migration doesn't have cascade delete
                $sale->customerDues()->delete();

                // 3. Delete the sale 
                // Note: Ensure your SaleItem migration has ->onDelete('cascade') 
                // or call $sale->items()->delete() here if it doesn't.
                $sale->delete();
            });

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale deleted, stock restored, and customer dues cleared.'
                ]);
            }

            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
        } catch (\Exception $e) {

            \Log::error('Invoice update failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function dsrLedger()
    {
        $employees = Employee::orderBy('name')->get();
        return view('sales.dsr_select', compact('employees'));
    }
    public function dsrOpeningStore(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'opening_balance' => 'required',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $employee->update([
            'opening_balance' => $request->opening_balance
        ]);
        return redirect()->route('dsr_details.ledger', $employee->id)
            ->with('success', 'Opening balance updated successfully!');
    }

    public function DsrDetailsledger($id)
    {
        $customer = Employee::findOrFail($id);

        // 1. Get Invoices (Debits)
        $invoices = SalesDueCustomer::where('customer_id', $id)
            ->with('sale')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'date'      => $item->created_at,
                    'type'      => 'Invoice',
                    'reference' => '#' . $item->sale->invoice_no,
                    'debit'     => $item->due_amount,
                    'credit'    => 0,
                ];
            });

        // 2. Get Payments (Credits)
        $payments = Payment::where('customer_id', $id)
            ->get()
            ->map(function ($item) {
                return (object)[
                    'date'      => $item->payment_date,
                    'type'      => 'Payment',
                    'reference' => $item->payment_method . ($item->transaction_no ? ' - ' . $item->transaction_no : ''),
                    'debit'     => 0,
                    'credit'    => $item->amount,
                ];
            });

        // 3. Merge, Sort, and Calculate Running Balance
        $merged = $invoices->concat($payments)->sortBy('date');

        $runningBalance = $customer->opening_balance;
        $ledger = $merged->map(function ($row) use (&$runningBalance) {
            $runningBalance += ($row->debit - $row->credit);
            $row->balance = $runningBalance;
            return $row;
        });

        return view('sales.ledger', compact('customer', 'ledger'));
    }

    //
    public function companySalesReport(Request $request)
    {
        if ($request->has(['company_id', 'start_date', 'end_date'])) {
            $request->validate([
                'company_id' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);

            $company_id = $request->company_id;
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $company_id = \App\Models\Company::first()->id;
            $start = $request->get('start_date', date('Y-m-01')); // Default to start of month
            $end = $request->get('end_date', date('Y-m-d'));
        }
        // Fetch sold items filtered by the product's company
        $salesData = \App\Models\SaleItem::with(['product', 'sale'])
            ->whereHas('product', function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            })
            ->whereHas('sale', function ($q) use ($start, $end) {
                $q->whereBetween('sale_date', [$start, $end]);
            })
            ->get();

        // Grouping summary by product for a quick overview
        $productSummary = $salesData->groupBy('product_id')->map(function ($items) {
            return [
                'name' => $items->first()->product->name,
                'total_qty' => $items->sum('quantity'),
                'total_amount' => $items->sum('subtotal'),
            ];
        });

        $company = \App\Models\Company::find($company_id);
        $companies = \App\Models\Company::all();

        return view('sales.company_sales', compact('salesData', 'productSummary', 'company', 'companies', 'start', 'end'));
    }

    //sr wise report
    public function srSalesReport(Request $request)
    {
        if ($request->has(['sr_id', 'start_date', 'end_date'])) {
            $request->validate([
                'sr_id' => 'required|exists:employees,id', // Assuming SRs are in employees table
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);

            $srId = $request->sr_id;
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $srId = \App\Models\Employee::first()->id;
            $start = $request->get('start_date', date('Y-m-01')); // Default to start of month
            $end = $request->get('end_date', date('Y-m-d'));
        }


        // Fetch Sales with their items and products
        $sales = \App\Models\Sale::with(['items.product', 'sr'])
            ->where('sr_id', $srId)
            ->whereBetween('sale_date', [$start, $end])
            ->get();

        // Summary Calculations
        $totalSalesAmount = $sales->sum('total_amount');
        $totalCollection = $sales->sum('paid_amount');
        $totalDue = $sales->sum('due_amount');

        $srs = \App\Models\Employee::all();
        $selectedSr = \App\Models\Employee::find($srId);

        return view('sales.sr_sales', compact('sales', 'totalSalesAmount', 'totalCollection', 'totalDue', 'selectedSr', 'srs', 'start', 'end'));
    }

    // public function productSalesReport(Request $request)
    // {
    //     $start_date = $request->start_date ?? date('Y-m-01'); // Default to start of month
    //     $end_date = $request->end_date ?? date('Y-m-d');

    //     // Assuming you have a SaleItem or InvoiceItem model related to Product
    //     $sales = SaleItem::with('product')
    //         ->select(
    //             'product_id',
    //             DB::raw('SUM(quantity) as total_qty'),
    //             DB::raw('SUM(subtotal) as total_revenue'),
    //             DB::raw('AVG(unit_price) as avg_price')
    //         )
    //         ->whereHas('sale', function ($query) use ($start_date, $end_date) {
    //             $query->whereBetween('sale_date', [$start_date, $end_date]);
    //         })
    //         ->groupBy('product_id')
    //         ->orderBy('total_revenue', 'desc')
    //         ->get();

    //     return view('sales.product_sales', compact('sales', 'start_date', 'end_date'));
    // }

    public function productSalesReport(Request $request)
    {
        $start_date = $request->start_date ?? date('Y-m-01');
        $end_date = $request->end_date ?? date('Y-m-d');

        // 1. Get Sales Data (Subquery)
        $salesData = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_sold_qty'),
                DB::raw('SUM(subtotal) as total_sold_revenue'),
                DB::raw('AVG(unit_price) as avg_price')
            )
            ->whereBetween('sales.sale_date', [$start_date, $end_date])
            ->groupBy('product_id');

        // 2. Get Damage Data (Subquery)
        $damageData = SalesDamageItems::join('sales', 'sales_damage_items.sale_id', '=', 'sales.id')
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_damage_qty'),
                // We calculate damage value based on the sub_total in the damage table
                DB::raw('SUM(subtotal) as total_damage_revenue')
            )
            ->whereBetween('sales.sale_date', [$start_date, $end_date])
            // If your damage table has a date, filter it here too
            ->groupBy('product_id');

        // 3. Final Report Join
        $report = DB::table('products')
            ->leftJoinSub($salesData, 'sales', function ($join) {
                $join->on('products.id', '=', 'sales.product_id');
            })
            ->leftJoinSub($damageData, 'damages', function ($join) {
                $join->on('products.id', '=', 'damages.product_id');
            })
            ->select(
                'products.name',
                DB::raw('COALESCE(sales.total_sold_qty, 0) as sold_qty'),
                DB::raw('COALESCE(damages.total_damage_qty, 0) as damage_qty'),
                DB::raw('(COALESCE(sales.total_sold_qty, 0) - COALESCE(damages.total_damage_qty, 0)) as net_qty'),
                DB::raw('COALESCE(sales.avg_price, 0) as avg_price'),
                DB::raw('COALESCE(sales.total_sold_revenue, 0) as sold_revenue'),
                DB::raw('COALESCE(damages.total_damage_revenue, 0) as damage_revenue'),
                // Net Revenue = Sold Revenue - Damage Revenue
                DB::raw('(COALESCE(sales.total_sold_revenue, 0) - COALESCE(damages.total_damage_revenue, 0)) as net_revenue')
            )
            ->where(function ($query) {
                $query->whereNotNull('sales.product_id')
                    ->orWhereNotNull('damages.product_id');
            })
            ->orderBy('net_revenue', 'desc')
            ->get();

        return view('sales.product_sales', compact('report', 'start_date', 'end_date'));
    }
}
