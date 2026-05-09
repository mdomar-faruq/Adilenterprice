<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\SalesDueCustomer;
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
            $data = Payment::with(['customer', 'dueRecord.sale', 'user'])->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return '<strong>' . \Carbon\Carbon::parse($row->payment_date)->format('d M, Y') . '</strong><br>' .
                        '<small class="text-muted">' . $row->created_at->format('h:i A') . '</small>';
                })
                ->addColumn('customer', function ($row) {
                    return $row->customer->name;
                })
                ->addColumn('invoice_no', function ($row) {
                    if (!$row->dueRecord || !$row->dueRecord->sale) {
                        return '<span class="badge bg-light text-muted">N/A</span>';
                    }

                    $url = route('sales.show', $row->dueRecord->sale_id);
                    return '<a href="' . $url . '" class="text-decoration-none">
                <span class="badge bg-secondary-subtle text-secondary">#' .
                        $row->dueRecord->sale->invoice_no .
                        '</span>
            </a>';
                })
                ->addColumn('method', function ($row) {
                    $trx = $row->transaction_no ? '<br><small class="extra-small text-muted">' . $row->transaction_no . '</small>' : '';
                    return '<small class="badge bg-info-subtle text-info">' . $row->payment_method . '</small>' . $trx;
                })
                ->addColumn('amount', function ($row) {
                    return '<div class="fw-bold text-success">' . number_format($row->amount, 2) . '</div>';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-sm btn-outline-danger delete-payment" data-id="' . $row->id . '">
                            <i class="bi bi-trash"></i>
                        </button>';
                })
                ->rawColumns(['date', 'invoice_no', 'method', 'amount', 'action'])
                ->make(true);
        }

        // $customers = Customer::orderBy('name')->get();
        $customers = Employee::orderBy('name')->get();
        return view('payments.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //DSR is Customer
        $customers = Employee::orderBy('name')->get();
        // $customers = Customer::orderBy('name')->get();
        return view('payments.create', compact('customers'));
    }

    // AJAX: Get pending dues for a specific customer
    // public function getPendingDues($customerId)
    // {
    //     $dues = SalesDueCustomer::with('sale')
    //         ->where('customer_id', $customerId)
    //         ->whereIn('status', ['unpaid', 'partial'])
    //         ->orderBy('created_at', 'asc') // FIFO: Oldest first
    //         ->get();

    //     return response()->json($dues);
    // }

    public function getPendingDues($customerId)
    {
        $customer = Employee::findOrFail($customerId);
        $dues = SalesDueCustomer::with('sale')->where('customer_id', $customerId)
            ->where('due_amount', '>', 0)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('created_at', 'asc') // FIFO: Oldest first
            ->get();

        $data = [];

        // 1. Add Opening Balance as the first "Invoice"
        if ($customer->opening_balance > 0) {
            $data[] = [
                'id' => 'opening', // Special ID
                'invoice_no' => 'OPENING-BAL',
                'due_amount' => $customer->opening_balance,
                'paid_amount' => $customer->opening_paid,
                'created_at' => $customer->created_at,
                'note' => 'Initial outstanding balance'
            ];
        }

        // 2. Add Actual Invoices
        foreach ($dues as $due) {
            $data[] = [
                'id' => $due->id,
                'invoice_no' => $due->sale->invoice_no,
                'due_amount' => $due->due_amount,
                'paid_amount' => $due->paid_amount,
                'created_at' => $due->created_at,
                'note' => $due->note
            ];
        }

        return response()->json($data);
    }


    /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        // 1. Validate inputs (Updated to include advance and opening balance keys)
        $request->validate([
            'customer_id'           => 'required|exists:employees,id',
            'payment_date'          => 'required|date',
            'payment_method'        => 'required|string',
            'amounts'               => 'nullable|array', // Main invoices
            'opening_balance_pay'   => 'nullable|numeric|min:0',
            'advance_amount'        => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $customer = Employee::findOrFail($request->customer_id);

                // --- A. HANDLE OPENING BALANCE PAYMENT ---
                if ($request->filled('opening_balance_pay') && $request->opening_balance_pay > 0) {
                    $payAmount = $request->opening_balance_pay;

                    // // Create a generic payment record for the opening balance
                    Payment::create([
                        'customer_id'    => $customer->id,
                        'amount'         => $payAmount,
                        'payment_date'   => $request->payment_date,
                        'payment_method' => $request->payment_method,
                        'transaction_no' => $request->transaction_no,
                        'note'           => 'Paid towards Opening Balance: ' . $request->note,
                        'user_id'        => Auth::id(),
                    ]);

                    // Deduct from customer's specific opening balance field
                    $customer->increment('opening_paid', $payAmount);
                }

                // --- B. HANDLE SPECIFIC INVOICE PAYMENTS ---
                if ($request->amounts) {
                    foreach ($request->amounts as $dueId => $payAmount) {
                        if ($payAmount && $payAmount > 0) {
                            $dueRecord = SalesDueCustomer::with('sale')->findOrFail($dueId);

                            // 1. Create Payment Entry
                            Payment::create([
                                'customer_id'           => $customer->id,
                                'sales_due_customer_id' => $dueId,
                                'amount'                => $payAmount,
                                'payment_date'          => $request->payment_date,
                                'payment_method'        => $request->payment_method,
                                'transaction_no'        => $request->transaction_no,
                                'note'                  => $request->note,
                                'user_id'               => Auth::id(),
                            ]);

                            // 2. Update SalesDueCustomer (The sub-record)
                            $dueRecord->increment('paid_amount', $payAmount);

                            // Set status
                            $newBalance = $dueRecord->due_amount - $dueRecord->paid_amount;
                            $dueRecord->status = ($newBalance <= 0) ? 'paid' : 'partial';
                            $dueRecord->save();

                            // 3. Update the Parent Sale (The master invoice)
                            $sale = $dueRecord->sale;
                            $sale->increment('paid_amount', $payAmount);
                            $sale->decrement('due_amount', $payAmount);
                        }
                    }
                }

                // --- C. HANDLE ADVANCE PAYMENT (EXCESS) ---
                // if ($request->filled('advance_amount') && $request->advance_amount > 0) {
                //     $advance = $request->advance_amount;

                //     Payment::create([
                //         'customer_id'    => $customer->id,
                //         'amount'         => $advance,
                //         'payment_date'   => $request->payment_date,
                //         'payment_method' => $request->payment_method,
                //         'transaction_no' => $request->transaction_no,
                //         'note'           => 'Excess/Advance Payment: ' . $request->note,
                //         'user_id'        => Auth::id(),
                //     ]);
                // }


            });

            return redirect()->route('payments.create')
                ->with('success', 'Full payment distribution completed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Payment failed: ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Display the specified resource.
     */
    public function show($id) {}

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
        // Find the payment with related records
        $payment = Payment::with('dueRecord.sale', 'customer')->findOrFail($id);

        try {
            DB::transaction(function () use ($payment) {
                if ($payment->sales_due_customer_id == null) {
                    //opening payment
                    $payment->customer->decrement('opening_paid', $payment->amount);
                    $payment->delete();
                } else {


                    $dueRecord = $payment->dueRecord;

                    // 1. Reverse Math in the 'sales_due_customers' table
                    $dueRecord->paid_amount -= $payment->amount;

                    // Recalculate the status based on the new balance
                    if ($dueRecord->paid_amount <= 0) {
                        $dueRecord->status = 'unpaid';
                        $dueRecord->paid_amount = 0; // Prevent negative numbers
                    } else {
                        $dueRecord->status = 'partial';
                    }
                    $dueRecord->save();

                    // 2. Reverse Math in the 'sales' table (The Master Invoice)
                    if ($dueRecord->sale) {
                        $dueRecord->sale->decrement('paid_amount', $payment->amount);
                        $dueRecord->sale->increment('due_amount', $payment->amount);
                    }

                    // 3. Delete the payment record itself
                    $payment->delete();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment has been deleted and balances restored successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
}
