<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // We only want 'payment' types (not 'return' adjustments) 
            // and we eager load the customer to avoid N+1 queries.
            $data = Payment::with('customer')
                ->where('type', 'payment')
                ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                // Format the Customer Name from the relationship
                ->addColumn('customer_name', function ($row) {
                    return $row->customer ? $row->customer->name : 'N/A';
                })
                // Format the Date to look nice (e.g., 02 Apr, 2026)
                ->editColumn('payment_date', function ($row) {
                    return date('d M, Y', strtotime($row->payment_date));
                })
                // Format the Amount with a dollar sign
                ->editColumn('amount', function ($row) {
                    return number_format($row->amount, 2);
                })
                // Action Buttons
                ->addColumn('action', function ($row) {
                    return '
                    <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                        <a href="' . route('payments.show', $row->id) . '" class="btn btn-sm btn-white text-info">
                            <i class="bi bi-printer"></i>
                        </a>
                        <button class="btn btn-sm btn-white text-danger delete-payment" data-id="' . $row->id . '">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>';
                })
                // Enable searching by customer name specifically
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('payments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        return view('payments.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'amount'         => 'required|numeric|min:1',
            'payment_date'   => 'required|date',
            'payment_method' => 'required',
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $customer = Customer::lockForUpdate()->findOrFail($request->customer_id);
                $paymentAmount = (float) $request->amount;
                $remaining = $paymentAmount;

                // 1. Create the Main Payment Record (The Money Receipt)
                $payment = Payment::create([
                    'customer_id'    => $customer->id,
                    'type'           => 'payment',
                    'amount'         => $paymentAmount,
                    'payment_date'   => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'note'           => $request->note,
                    'user_id'        => Auth::id(),
                ]);

                // 2. Distribute money to unpaid sales (Oldest First)
                $unpaidSales = Sale::where('customer_id', $customer->id)
                    ->where('due_amount', '>', 0)
                    ->orderBy('sale_date', 'asc')
                    ->get();

                foreach ($unpaidSales as $sale) {
                    if ($remaining <= 0) break;

                    $due = $sale->due_amount;
                    $allocation = min($remaining, $due);

                    $sale->paid_amount += $allocation;
                    $sale->due_amount  -= $allocation;
                    $sale->payment_status = ($sale->due_amount <= 0) ? 'paid' : 'partial';
                    $sale->save();

                    $remaining -= $allocation;

                    // Link this specific allocation to the payment (Optional: requires a pivot table)
                }

                return "Money Receipt #MR-{$payment->id} generated for {$customer->name}";
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
        $payment = Payment::with(['customer', 'user'])->findOrFail($id);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
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
                $payment = Payment::findOrFail($id);
                $customerId = $payment->customer_id;
                $refundAmount = $payment->amount;

                // 1. Find the sales for this customer that were affected.
                // We look for sales where the paid_amount > 0, 
                // and we restore debt starting from the LATEST sale (Reverse Waterfall).
                $sales = Sale::where('customer_id', $customerId)
                    ->where('paid_amount', '>', 0)
                    ->orderBy('sale_date', 'desc')
                    ->lockForUpdate()
                    ->get();

                foreach ($sales as $sale) {
                    if ($refundAmount <= 0) break;

                    // How much can we actually restore to this sale?
                    // We shouldn't exceed the original total_amount.
                    $canRestore = $sale->paid_amount;
                    $restoreNow = min($refundAmount, $canRestore);

                    $sale->paid_amount -= $restoreNow;
                    $sale->due_amount  += $restoreNow;

                    // Update status
                    $sale->payment_status = ($sale->paid_amount <= 0) ? 'pending' : 'partial';
                    $sale->save();

                    $refundAmount -= $restoreNow;
                }

                // 2. Delete the actual Money Receipt record
                $payment->delete();

                return "Money Receipt deleted. $" . number_format($payment->amount, 2) . " has been added back to customer debt.";
            });

            return response()->json(['success' => true, 'message' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
