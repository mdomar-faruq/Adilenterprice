<?php

namespace App\Http\Controllers;

use App\Models\{SalesReturn, Sale, Product, Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SalesReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Use with('customer') to prevent N+1 query issues
            $data = SalesReturn::with('customer')->select('sales_returns.*');

            return DataTables::of($data)
                ->addIndexColumn()
                // 1. Added Optional() for null safety (prevents crash if customer is missing)
                ->addColumn('customer_name', function ($row) {
                    return $row->customer ? $row->customer->name : 'Walk-in Customer';
                })
                // 2. Consistent date formatting
                ->editColumn('return_date', function ($row) {
                    return date('d M, Y', strtotime($row->return_date));
                })
                // 3. Currency formatting
                ->editColumn('total_amount', function ($row) {
                    return 'TK' . number_format($row->total_amount, 2);
                })
                ->addColumn('action', function ($row) {
                    return '
                    <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                        <a href="' . route('sales_returns.show', $row->id) . '" class="btn btn-sm btn-white text-info" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button class="btn btn-sm btn-white text-danger delete-return" data-id="' . $row->id . '" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>';
                })
                // Important: You MUST include customer_name in filterColumn 
                // if you want the DataTable search box to search by name
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('sales_returns.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('stock', '>', -1)->get();
        return view('sales_returns.create', compact('customers'));
    }

    // SalesReturnController.php
    public function getPurchasedProducts($id)
    {
        $products = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'sale_items.unit_price as sales_price',
                DB::raw('SUM(sale_items.quantity) as total_bought') // Add this line
            )
            ->groupBy('products.id', 'products.name', 'sale_items.unit_price')
            ->get();

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'return_date' => 'required|date',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $result = DB::transaction(function () use ($request) {

                // 1. Lock Customer
                $customer = \App\Models\Customer::lockForUpdate()->findOrFail($request->customer_id);

                // 2. Calculate Total Return Value
                $totalReturnAmount = 0;
                foreach ($request->items as $item) {
                    $totalReturnAmount += round((float)$item['quantity'] * (float)$item['unit_price'], 2);
                }

                // 3. Create Sales Return Header
                $salesReturn = \App\Models\SalesReturn::create([
                    'return_no'    => 'RET-' . time(),
                    'customer_id'  => $customer->id,
                    'return_date'  => $request->return_date,
                    'total_amount' => $totalReturnAmount,
                    'remarks'      => $request->remarks,
                    'user_id'      => Auth::id(),
                ]);

                // 4. Update Stock for all returned items
                foreach ($request->items as $item) {
                    $salesReturn->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal'   => round($item['quantity'] * $item['unit_price'], 2),
                    ]);

                    \App\Services\StockService::updateStock(
                        $item['product_id'],
                        $item['quantity'],
                        'return',
                        $salesReturn->return_no,
                        "Multi-sale return stock update"
                    );
                }
                return "Return processed. Stock updated. ";
            });

            return response()->json(['success' => true, 'message' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Eager load customer, items, and the products within those items
        $return = SalesReturn::with(['customer', 'items.product'])->findOrFail($id);

        return view('sales_returns.show', compact('return'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesReturn $salesReturn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesReturn $salesReturn)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $result = DB::transaction(function () use ($id) {
                $return = SalesReturn::with('items')->findOrFail($id);
                // 1. REVERSE STOCK (Decrease inventory because return is being cancelled)
                foreach ($return->items as $item) {
                    \App\Services\StockService::reverseStock(
                        $item->product_id,
                        $item->quantity,
                        'return', // Original was 'return' (+stock), so reversal is (-stock)
                        $return->return_no
                    );
                }

                // 2. Delete the Return and its items (Cascade)
                $return->delete();

                return "Sales Return deleted. Stock decreased.";
            });

            return response()->json(['success' => true, 'message' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
