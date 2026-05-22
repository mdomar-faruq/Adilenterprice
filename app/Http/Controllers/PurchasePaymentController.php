<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PurchasePayment;
use App\Models\purchases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchasePaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // 1. Using a Join or specific Eager Loading avoids N+1 query loops
            // 2. We use withDefault() in the model or a null-coalescing operator to prevent crashes
            $data = PurchasePayment::with('company')->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                // Added safe fallback '??' in case a company is missing
                ->addColumn('company_name', fn($row) => $row->company->name ?? 'N/A')

                // Clean currency formatting
                ->editColumn('amount', fn($row) => number_format($row->amount, 2))

                // Structured date styling
                ->editColumn('payment_date', fn($row) => date('d M, Y', strtotime($row->payment_date)))

                // Action column mapping
                ->addColumn('action', function ($row) {
                    // Generate the exact URL using Laravel's route helper
                    $deleteUrl = route('purchase_payments.destroy', $row->id);

                    return '<button class="btn btn-sm btn-light text-danger delete-btn" 
                    data-route="' . $deleteUrl . '" 
                    title="Delete Payment">
                <i class="bi bi-trash"></i>
            </button>';
                })
                // Explicitly declare columns that render raw HTML strings to prevent XSS vulnerability bugs
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('purchase_payments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Calculate the absolute running current balance for every company profile
        $companies = Company::withSum('purchases', 'total_amount')
            ->withSum('purchases_payments', 'amount')
            ->get()
            ->map(function ($company) {
                $opening = (float)$company->opening_balance;
                $bills   = (float)($company->purchases_sum_total_amount ?? 0);
                $paid    = (float)($company->purchases_payments_sum_amount ?? 0);

                // Ultimate true balance layout formula: (What you owed initially + what you bought) - what you paid
                $company->calculated_due = ($opening + $bills) - $paid;

                return $company;
            });

        return view('purchase_payments.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // PurchasePaymentController.php

    public function store(Request $request)
    {
        $request->validate([
            'company_id'     => 'required|exists:companies,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string',
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $paymentAmount = (float) $request->amount;
                $remaining = $paymentAmount;

                // 1. Create the base Master Voucher Record
                $payment = PurchasePayment::create([
                    'company_id'     => $request->company_id,
                    'type'           => 'purchase_payment',
                    'amount'         => $paymentAmount,
                    'payment_date'   => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'note'           => $request->note,
                    'user_id'        => Auth::id(),
                    'log_details'    => null // We will populate this to record exactly where money went
                ]);

                $allocationLogs = [
                    'opening_balance_paid' => 0.00,
                    'purchases_affected'   => []
                ];

                // 2. STAGE 1 WATERFALL: Address Outstanding Corporate Opening Balance First
                $company = Company::lockForUpdate()->find($request->company_id);

                // Calculate how much opening balance is currently unpaid
                // We track this by checking what has already been historically deducted from it
                $totalPriorPayments = PurchasePayment::where('company_id', $company->id)
                    ->where('id', '!=', $payment->id)
                    ->sum('amount');
                $totalPriorPurchases = purchases::where('company_id', $company->id)->sum('total_amount');

                // If payments exceeded purchases in the past, the excess went to opening balance
                $pastPaymentAppliedToOpening = max(0, $totalPriorPayments - $totalPriorPurchases);
                $currentOpeningDue = max(0, (float)$company->opening_balance - $pastPaymentAppliedToOpening);

                if ($currentOpeningDue > 0 && $remaining > 0) {
                    $applyToOpening = min($remaining, $currentOpeningDue);
                    $remaining -= $applyToOpening;
                    $allocationLogs['opening_balance_paid'] = $applyToOpening;
                }

                // 3. STAGE 2 WATERFALL: Clear standard purchase ledger records
                if ($remaining > 0) {
                    $purchases = purchases::where('company_id', $request->company_id)
                        ->where('due_amount', '>', 0)
                        ->orderBy('purchase_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                    foreach ($purchases as $purchase) {
                        if ($remaining <= 0) break;

                        $due = (float)$purchase->due_amount;
                        $paymentToApply = min($remaining, $due);

                        $purchase->paid_amount += $paymentToApply;
                        $purchase->due_amount  -= $paymentToApply;
                        $purchase->payment_status = ($purchase->due_amount <= 0) ? 'paid' : 'partial';
                        $purchase->save();

                        // Keep historical logs map entries so deletion can execute code rollback cleanly
                        $allocationLogs['purchases_affected'][] = [
                            'purchase_id' => $purchase->id,
                            'amount_paid' => $paymentToApply
                        ];

                        $remaining -= $paymentToApply;
                    }
                }

                // 4. STAGE 3: Handle Overpayments / Advance Deposit Credits
                // If $remaining is still greater than 0, it becomes a loose credit balance on file.
                // It organically lowers the company's dynamic 'calculated_due' aggregate in subsequent calls.
                if ($remaining > 0) {
                    $allocationLogs['excess_advance_credit'] = $remaining;
                }

                // Save audit trail footprint inside document column instance tracking metrics
                $payment->log_details = json_encode($allocationLogs);
                $payment->save();

                return "Payment of TK " . number_format($paymentAmount, 2) . " successfully processed.";
            });

            return response()->json(['success' => true, 'message' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchasePayment $purchasePayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchasePayment $purchasePayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchasePayment $purchasePayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $payment = PurchasePayment::findOrFail($id);
                $logs = json_decode($payment->log_details, true);

                if (!empty($logs) && isset($logs['purchases_affected'])) {
                    // Loop backwards through exact invoices hit during storage allocation layout mapping step
                    foreach ($logs['purchases_affected'] as $alloc) {
                        $purchase = purchases::find($alloc['purchase_id']);
                        if ($purchase) {
                            $purchase->paid_amount -= $alloc['amount_paid'];
                            $purchase->due_amount  += $alloc['amount_paid'];

                            // Recalculate status parameters
                            if ($purchase->paid_amount <= 0) {
                                $purchase->payment_status = 'pending';
                            } else {
                                $purchase->payment_status = 'partial';
                            }
                            $purchase->save();
                        }
                    }
                }

                // Delete transaction document anchor securely
                $payment->delete();
            });

            return response()->json(['success' => true, 'message' => 'Payment voucher cancelled. restored successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Rollback execution dropped: ' . $e->getMessage()], 422);
        }
    }
}
