<?php

namespace App\Http\Controllers;

use App\Models\purchases;
use App\Models\Product;
use App\Models\Company;
use App\Models\purchase_items;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchasesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = purchases::with('company')->select('purchases.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('paid_amount', fn($row) => number_format($row->paid_amount, 2))
                ->editColumn('due_amount', fn($row) => number_format($row->due_amount, 2))
                ->addColumn('action', function ($row) {
                    $btn = '<button class="btn btn-sm btn-info view-details" data-id="' . $row->id . '"><i class="bi bi-eye"></i></button> ';
                    $btn .= '<a href="' . route('purchases.edit', $row->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a> ';
                    $btn .= '<button class="btn btn-sm btn-danger delete-purchase" data-id="' . $row->id . '"> <i class="bi bi-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('purchases.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
        $products = Product::all();
        return view('purchases.create', compact('companies', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // Save Logic
    public function store(Request $request)
    {

        $request->validate([
            'purchase_no' => 'required',
            'company_id' => 'required',
            'purchase_date' => 'required',
            'product_id.*' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'unit_price.*' => 'required|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $purchase = purchases::create([
                    'purchase_no'   => $request->purchase_no,
                    'purchase_date' => $request->purchase_date,
                    'company_id'    => $request->company_id,
                    'user_id'       => Auth::id(),
                    'total_amount'  => $request->total_amount,
                    'discount'  => 0,
                    'paid_amount'  => 0,
                    'due_amount'  => $request->total_amount,
                    'status'        => 1, // Default active
                    'valid'         => 1, // Default valid
                ]);

                foreach ($request->product_id as $key => $product_id) {
                    $qty = $request->quantity[$key];
                    $price = $request->unit_price[$key];

                    purchase_items::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product_id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $qty * $price,
                    ]);

                    // Use Service to Update Product Table + StockMovement Table
                    StockService::updateStock($product_id, $qty, 'purchase', $purchase->purchase_no);
                }
            });

            return redirect()->route('purchases.index')->with('success', 'Purchase recorded and stock updated!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $purchase = purchases::with(['company', 'items.product'])->findOrFail($id);
        return response()->json($purchase);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $purchase = purchases::with('items')->findOrFail($id);
        $companies = Company::all();
        $products = Product::all();
        return view('purchases.edit', compact('purchase', 'companies', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $purchase = purchases::findOrFail($id);

                // 1. REVERSE STOCK & LOG MOVEMENT (Type: adjustment or return)
                foreach ($purchase->items as $oldItem) {
                    StockService::updateStock($oldItem->product_id, $oldItem->quantity, 'adjustment', $purchase->purchase_no, 'Stock reversal for edit');
                }

                $purchase->items()->delete();

                // 2. UPDATE HEADER
                $purchase->update([
                    'company_id'    => $request->company_id,
                    'purchase_date' => $request->purchase_date,
                    'total_amount'  => $request->total_amount,
                    'discount'  => 0,
                    'paid_amount'  => 0,
                    'due_amount'  => $request->total_amount,
                ]);

                // 3. INSERT NEW ITEMS & UPDATE STOCK
                foreach ($request->product_id as $key => $product_id) {
                    $qty = $request->quantity[$key];
                    $price = $request->unit_price[$key];

                    purchase_items::create([
                        'purchase_id' => $purchase->id,
                        'product_id'  => $product_id,
                        'quantity'    => $qty,
                        'unit_price'  => $price,
                        'subtotal'    => $qty * $price,
                    ]);

                    StockService::updateStock($product_id, $qty, 'purchase', $purchase->purchase_no);
                }
            });

            return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Update Failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // Load items with lock to prevent stock drifting during deletion
                $purchase = purchases::with('items')->findOrFail($id);

                foreach ($purchase->items as $item) {
                    // We use 'purchase' as the 3rd param so the service knows to SUBTRACT
                    StockService::reverseStock(
                        $item->product_id,
                        $item->quantity,
                        'purchase',
                        $purchase->purchase_no
                    );
                }

                // Delete child records first, then the parent
                $purchase->items()->delete();
                $purchase->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase deleted and stock inventory corrected.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
