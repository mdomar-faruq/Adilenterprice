<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
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

                // 3. Financial Calculations
                $discount    = (float) ($request->discount ?? 0);
                $totalAmount = round($calculatedSubtotal - $discount, 2);
                $paidAmount  = $request->paid_amount ?? 0;
                $dueAmount   = $totalAmount - $paidAmount;

                // 4. Create the Master Sale (Removed customer_id, Added Delivery/SR/Route)
                $sale = Sale::create([
                    'invoice_no'     => 'INV-' . strtoupper(Str::random(4)) . time(),
                    'delivery_id'    => $request->delivery_id,
                    'sr_id'          => $request->sr_id,
                    'route_no'       => $request->route_no,
                    'sale_date'      => $request->sale_date,
                    'total_amount'   => $totalAmount,
                    'discount'       => $discount,
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

                //6. DSR Due / customer Due
                \App\Models\SalesDueCustomer::create([
                    'sale_id'     => $sale->id,
                    'customer_id' => $sale->delivery_id,
                    'due_amount'  => $sale->due_amount,
                    'note'        => null,
                ]);


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
        $sale = Sale::with(['items.product', 'customerDues.customer'])->findOrFail($id);
        $employees = Employee::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('sales.edit', compact('sale', 'employees', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $sale) {
                // 1. Reverse ALL old stock movements first
                foreach ($sale->items as $item) {
                    \App\Services\StockService::reverseStock(
                        $item->product_id,
                        $item->quantity,
                        'sale_edit_reverse',
                        $sale->invoice_no
                    );
                }

                // 2. Remove old Items and old Customer Dues
                $sale->items()->delete();
                $sale->customerDues()->delete();

                // 3. Calculate Financials
                $totalAmount = 0;
                if ($request->has('items')) {
                    foreach ($request->items as $item) {
                        $totalAmount += ($item['unit_price'] * $item['quantity']);
                    }
                }
                $grandTotal = $totalAmount - ($request->discount ?? 0);

                // 4. Update Main Sale Record
                $sale->update([
                    'sale_date'      => $request->sale_date,
                    'sr_id'          => $request->sr_id,
                    'delivery_id'    => $request->delivery_id,
                    'route_no'       => $request->route_no,
                    'total_amount'   => $grandTotal,
                    'discount'       => $request->discount ?? 0,
                    'paid_amount'    => $request->paid_amount ?? 0,
                    'due_amount'     => $grandTotal - ($request->paid_amount ?? 0),
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

                // 6. Create New Customer Due Allocation
                //6. DSR Due / customer Due
                \App\Models\SalesDueCustomer::create([
                    'sale_id'     => $sale->id,
                    'customer_id' => $request->delivery_id,
                    'due_amount'  => $grandTotal - ($request->paid_amount ?? 0),
                    'note'        => null,
                ]);
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
}
