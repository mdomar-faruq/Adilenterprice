<?php

namespace App\Http\Controllers;

use App\Models\ProductReturn;
use App\Models\ProductReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProductReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ProductReturn::with(['dsr'])->select('product_returns.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('type', function ($row) {
                    $class = $row->type == 'sales_return' ? 'bg-success' : 'bg-danger';
                    return '<span class="badge ' . $class . '">' . strtoupper(str_replace('_', ' ', $row->type)) . '</span>';
                })
                ->editColumn('dsr_id', fn($row) => $row->dsr->name ?? 'N/A')
                ->editColumn('total_amount', fn($row) => number_format($row->total_amount, 2))
                ->addColumn('action', function ($row) {
                    return '
                        <div class="btn-group">
                            <a href="' . route('returns.show', $row->id) . '" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteReturn(' . $row->id . ')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <form id="delete-form-' . $row->id . '" action="' . route('returns.destroy', $row->id) . '" method="POST" style="display:none;">
                            ' . csrf_field() . ' ' . method_field('DELETE') . '
                        </form>';
                })
                ->rawColumns(['type', 'action'])
                ->make(true);
        }
        return view('returns.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = \App\Models\Employee::orderBy('name')->get();
        $products = \App\Models\Product::orderBy('name')->get();
        return view('returns.create', compact('employees', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sales_return,purchase_return',
            'return_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
        ]);

        try {
            // ALWAYS use the transaction to prevent stock mismatch
            return DB::transaction(function () use ($request) {

                // 1. Create Return Header
                $return = ProductReturn::create([
                    'type' => $request->type,
                    'dsr_id' => $request->dsr_id,
                    'return_date' => $request->return_date,
                    'total_amount' => $request->total_amount,
                    'user_id' => Auth::id(),
                ]);

                // 2. Filter valid items (remove empty rows from UI)
                $items = array_filter($request->items, function ($item) {
                    return !empty($item['product_id']);
                });

                $ref = 'RET-' . $return->id;

                foreach ($items as $item) {
                    $pId    = $item['product_id'];
                    $good   = (float)($item['good_qty'] ?? 0);
                    $dmg    = (float)($item['damage_qty'] ?? 0);
                    $uPrice = (float)($item['price'] ?? 0);

                    // 3. Create Return Item record
                    ProductReturnItem::create([
                        'product_return_id' => $return->id,
                        'product_id'        => $pId,
                        'good_qty'          => $good,
                        'damage_qty'        => $dmg,
                        'unit_price'        => $uPrice,
                        'subtotal'          => ($good + $dmg) * $uPrice,
                    ]);

                    // 4. Handle Stock Logic based on Return Type
                    if ($request->type === 'sales_return') {
                        // STOCK IN (DSR/Customer to Warehouse)
                        if ($good > 0) {
                            \App\Services\StockService::updateStock($pId, $good, 'return', $ref, "Sales Return (Good)");
                        }
                        if ($dmg > 0) {
                            // Increase the damage pile in the warehouse
                            \App\Services\StockService::updateDamageStock($pId, $dmg, true, $ref);
                        }
                    } else if ($request->type === 'purchase_return') {
                        // STOCK OUT (Warehouse back to Vendor)
                        if ($good > 0) {
                            // adjustment usually decrements in your service
                            \App\Services\StockService::updateStock($pId, $good, 'adjustment', $ref, "Purchase Return (Good)");
                        }
                        if ($dmg > 0) {
                            // false = decrease the damage pile (it's leaving the warehouse)
                            \App\Services\StockService::recordAccidentalDamage($pId, $dmg, $ref);
                        }
                    }
                }

                return redirect()->route('returns.index')->with('success', 'Return Saved & Stock Updated Successfully!');
            });
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error("Return Store Error: " . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specific return details.
     */
    public function show($id)
    {
        // Load with relationships to avoid N+1 queries in the view
        $return = ProductReturn::with(['dsr', 'items.product'])->findOrFail($id);

        return view('returns.show', compact('return'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductReturn $productReturn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductReturn $productReturn)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $return = ProductReturn::with('items')->findOrFail($id);
                $ref = 'REV-RET-' . $return->id;

                foreach ($return->items as $item) {
                    // 1. Reverse Main Stock (Good Qty)
                    if ($item->good_qty > 0) {
                        /** * If original was sales_return (stock went UP), 
                         * reverseStock will take it BACK DOWN.
                         */
                        $originalType = ($return->type === 'sales_return') ? 'return' : 'adjustment';
                        \App\Services\StockService::reverseStock($item->product_id, $item->good_qty, $originalType, $ref);
                    }

                    // 2. Reverse Damage Stock
                    if ($item->damage_qty > 0) {
                        /**
                         * If original was sales_return (damage stock went UP), 
                         * we need to DECREMENT (false) to remove it.
                         */
                        $isOriginalIncrement = ($return->type === 'sales_return');
                        \App\Services\StockService::updateDamageStock(
                            $item->product_id,
                            $item->damage_qty,
                            !$isOriginalIncrement, // Reverse the boolean
                            $ref
                        );

                        if($return->type === 'purchase_return'){
                            \App\Services\StockService::updateStock($item->product_id, $item->damage_qty, "return", $ref, "Accidental Damage Remove");
                        }

                    }
                }

                // 3. Delete records
                $return->items()->delete();
                $return->delete();

                return redirect()->route('returns.index')->with('success', 'Return deleted and stock reversed successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
