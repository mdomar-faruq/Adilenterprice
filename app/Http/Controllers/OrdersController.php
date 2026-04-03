<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Orders;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = Orders::with('customer')
                ->withCount('items as items_count')
                ->withSum('items as total_qty', 'qty')
                ->latest();

            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('customer_name', fn($row) => $row->customer->name ?? 'N/A')
                ->addColumn('grand_total', fn($row) => number_format($row->total_qty) . ' Qty')
                ->addColumn('status_badge', function ($row) {
                    $class = $row->status == 'pending' ? 'warning' : 'success';
                    return '<span class="badge bg-' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-sm btn-light border view-details" data-id="' . $row->id . '"><i class="bi bi-eye text-primary"></i></button>
                        <a href="' . route('orders.edit', $row->id) . '" class="btn btn-sm btn-light border"><i class="bi bi-pencil text-info"></i></a>
                        <button class="btn btn-sm btn-light border delete-orders" data-id="' . $row->id . '"><i class="bi bi-trash text-danger"></i></button>
                    </div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('orders.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        return view('orders.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'order_no'    => 'required', // Added unique check
            'customer_id' => 'required|exists:customers,id',
            'order_date'  => 'required|date',
            'product_id'  => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity'    => 'required|array|min:1',
            'quantity.*'  => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // 2. Save Order Master 
            // Note: Changed "Orders" to "Order" to match standard model naming
            $order = Orders::create([
                'order_no'    => $request->order_no,
                'customer_id' => $request->customer_id,
                'order_date'  => $request->order_date,
                'status'      => 'pending',
            ]);

            // 3. Save Order Items
            foreach ($request->product_id as $key => $product_id) {
                // Safety check: ensure product_id is not null
                if ($product_id) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $product_id,
                        'qty'        => $request->quantity[$key],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('orders.index')->with('success', 'Order created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            // It's helpful to log the error for debugging
            \Log::error('Order Store Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Eager load relationships to avoid multiple database hits
        $order = Orders::with(['customer', 'items.product'])->findOrFail($id);

        // We return a specific partial view that doesn't include 
        // the layout (no navbar, no sidebar)
        return view('orders.view', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orders $order)
    {
        $customers = Customer::all();
        $products = Product::all();
        $order->load('items'); // Load existing items to populate the table
        return view('orders.edit', compact('order', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Orders $order)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date'  => 'required|date',
            'product_id'  => 'required|array',
            'quantity'    => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $order->update([
                'customer_id' => $request->customer_id,
                'order_date'  => $request->order_date,
            ]);

            // Sync Items: Delete old ones and insert new ones
            $order->items()->delete();

            foreach ($request->product_id as $key => $p_id) {
                $order->items()->create([
                    'product_id' => $p_id,
                    'qty'        => $request->quantity[$key],
                ]);
            }

            DB::commit();
            return redirect()->route('orders.index')->with('success', 'Order updated!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = Orders::findOrFail($id);
        $order->items()->delete(); // Clear detail items
        $order->delete();          // Delete master
        return response()->json(['success' => 'Order and its items deleted successfully.']);
    }
}
