<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Sale::with('customer')->select('sales.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('customer_name', function ($row) {
                    return $row->customer->name ?? 'N/A';
                })
                ->editColumn('sale_date', function ($row) {
                    return date('d M, Y', strtotime($row->sale_date));
                })
                ->editColumn('total_amount', fn($row) => number_format($row->total_amount, 2))
                ->editColumn('paid_amount', fn($row) => number_format($row->paid_amount, 2))
                ->editColumn('due_amount', function ($row) {
                    $class = $row->due_amount > 0 ? 'text-danger' : 'text-success';
                    return '<span class="' . $class . ' fw-bold">' . number_format($row->due_amount, 2) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    // Determine if we should show the Pay Button


                    return '
                <div class="btn-group shadow-sm">
                    <a href="' . route('sales.show', $row->id) . '" class="btn btn-sm btn-outline-info" title="View Invoice">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="' . route('sales.edit', $row->id) . '" class="btn btn-sm btn-outline-primary" title="Edit Sale">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $row->id . '" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>';
                })
                ->rawColumns(['action', 'due_amount'])
                ->make(true);
        }

        return view('sales.index');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        return view('sales.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'customer_id'  => 'required|exists:customers,id',
            'sale_date'    => 'required|date',
            'product_id'   => 'required|array|min:1',
            'qty'          => 'required|array',
            'qty.*'        => 'required|numeric|min:1',
            'price'        => 'required|array',
            'discount'     => 'nullable|numeric|min:0',
        ]);

        try {
            $sale = DB::transaction(function () use ($request) {

                // --- NEW: FETCH CUSTOMER AND LOCK FOR CREDIT CHECK ---
                $customer = \App\Models\Customer::lockForUpdate()->findOrFail($request->customer_id);

                $calculatedSubtotal = 0;
                $itemsToProcess = [];

                // 2. Initial Loop: Validate Stock
                foreach ($request->product_id as $key => $p_id) {
                    $qty = (float) $request->qty[$key];
                    $unitPrice = (float) $request->price[$key];
                    $lineTotal = $qty * $unitPrice;
                    $calculatedSubtotal += $lineTotal;

                    $product = \App\Models\Product::lockForUpdate()->findOrFail($p_id);

                    if ($product->stock < $qty) {
                        throw new \Exception("Stock insufficient for: {$product->name}");
                    }

                    $itemsToProcess[] = [
                        'product_id' => $p_id,
                        'quantity'   => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal'   => $lineTotal,
                    ];
                }

                // 3. Financial Calculations
                $discount   = (float) ($request->discount ?? 0);
                $totalAmount = round($calculatedSubtotal - $discount, 2);
                $paidAmount  = 0;
                $dueAmount  = $totalAmount;


                // 4. Payment Status
                $paymentStatus = 'pending';

                // 5. Create the Master Sale
                $sale = Sale::create([
                    'invoice_no'     => 'INV-' . time() . mt_rand(100, 999),
                    'customer_id'    => $customer->id,
                    'sale_date'      => $request->sale_date,
                    'total_amount'   => $totalAmount,
                    'discount'       => $discount,
                    'paid_amount'    => $paidAmount,
                    'due_amount'     => $dueAmount,
                    'payment_status' => $paymentStatus,
                    'remarks'        => $request->remarks,
                    'user_id'        => Auth::id(),
                ]);

                // 6. Process Items and Stock Movements
                foreach ($itemsToProcess as $item) {
                    $sale->items()->create($item);

                    \App\Services\StockService::updateStock(
                        $item['product_id'],
                        $item['quantity'],
                        'sale',
                        $sale->invoice_no,
                        "Sale recorded: {$sale->invoice_no}"
                    );
                }

                return $sale;
            });

            return redirect()->route('sales.index')
                ->with('success', "Sale Invoice {$sale->invoice_no} created successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Eager load everything to prevent "N+1" issues on the invoice
        $sale = Sale::with(['customer', 'user', 'items.product'])->findOrFail($id);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::with(['items', 'payments'])->findOrFail($id);

        $request->validate([
            'customer_id'  => 'required|exists:customers,id',
            'sale_date'    => 'required|date',
            'product_id'   => 'required|array|min:1',
            'qty'          => 'required|array',
            'qty.*'        => 'required|numeric|min:1',
            'price'        => 'required|array',
            'discount'     => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $sale) {

                // --- STEP A: REVERSE OLD STOCK ---
                foreach ($sale->items as $oldItem) {
                    \App\Services\StockService::reverseStock(
                        $oldItem->product_id,
                        $oldItem->quantity,
                        'sale',
                        $sale->invoice_no
                    );
                }

                // --- STEP B: PREPARE NEW TOTALS & VALIDATE STOCK ---
                $calculatedSubtotal = 0;
                $newItemsData = [];

                foreach ($request->product_id as $key => $p_id) {
                    $qty = (float) $request->qty[$key];
                    $unitPrice = (float) $request->price[$key];
                    $lineTotal = $qty * $unitPrice;
                    $calculatedSubtotal += $lineTotal;

                    $product = Product::lockForUpdate()->findOrFail($p_id);
                    if ($product->stock < $qty) {
                        throw new \Exception("Stock insufficient for: {$product->name}.");
                    }

                    $newItemsData[] = [
                        'product_id' => $p_id,
                        'quantity'   => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal'   => $lineTotal,
                    ];
                }

                // --- STEP C: FINANCIAL RECONCILIATION ---
                $discount = (float) ($request->discount ?? 0);
                $totalAmount = round($calculatedSubtotal - $discount, 2);

                //paid amount 
                $historyPaid = $sale->paid_amount;

                // If the user entered a NEW paid_amount in the edit form, 
                // you must decide if that replaces history or adds to it.
                // Usually, in "Edit", we keep the history.
                $finalPaid = round($historyPaid, 2);

                // Logic: If total bill decreased below what was already paid
                if ($finalPaid >= $totalAmount) {
                    // You might want to throw an error or handle as credit
                    // For now, we cap it so due_amount isn't negative
                    $dueAmount = 0;
                } else {
                    $dueAmount = round($totalAmount - $finalPaid, 2);
                }

                $paymentStatus = ($dueAmount <= 0) ? 'paid' : (($finalPaid > 0) ? 'partial' : 'pending');

                // --- STEP D: REFRESH SALE RECORD ---
                $sale->items()->delete();

                $sale->update([
                    'customer_id'    => $request->customer_id,
                    'sale_date'      => $request->sale_date,
                    'total_amount'   => $totalAmount,
                    'discount'       => $discount,
                    'paid_amount'    => $finalPaid,
                    'due_amount'     => $dueAmount,
                    'payment_status' => $paymentStatus,
                    'remarks'        => $request->remarks,
                ]);

                // --- STEP E: CREATE NEW ITEMS & DEDUCT STOCK ---
                foreach ($newItemsData as $item) {
                    $sale->items()->create($item);

                    \App\Services\StockService::updateStock(
                        $item['product_id'],
                        $item['quantity'],
                        'sale',
                        $sale->invoice_no,
                        "Updated Sale Invoice: {$sale->invoice_no}"
                    );
                }
            });

            return redirect()->route('sales.index')->with('success', 'Sale updated and inventory adjusted. Payments preserved.');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // 1. Find the sale with items to avoid N+1 issues
        $sale = \App\Models\Sale::with('items')->findOrFail($id);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($sale) {
                // 2. Loop through items to reverse stock
                foreach ($sale->items as $item) {
                    // Using your StockService to increment product stock and log movement
                    \App\Services\StockService::reverseStock(
                        $item->product_id,
                        $item->quantity,
                        'sale', // Original type was a sale
                        $sale->invoice_no
                    );
                }

                // 3. Delete the sale (Ensure SaleItem has onDelete('cascade') in migration)
                $sale->delete();
            });

            // 4. Handle Response for AJAX (DataTable) or standard Request
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale deleted and stock restored successfully.'
                ]);
            }

            return redirect()->route('sales.index')->with('success', 'Sale deleted and stock restored.');
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
}
