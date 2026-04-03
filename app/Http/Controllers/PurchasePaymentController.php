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
            $data = PurchasePayment::with('company')->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', fn($row) => $row->company->name)
                ->editColumn('amount', fn($row) => number_format($row->amount, 2))
                ->editColumn('payment_date', fn($row) => date('d M, Y', strtotime($row->payment_date)))
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-light text-danger delete-btn" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                })
                ->make(true);
        }
        return view('purchase_payments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
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

                // 1. Record the Payment in the payments table
                $payment = PurchasePayment::create([
                    'company_id'     => $request->company_id, // Foreign key to companies table
                    'type'           => 'purchase_payment',
                    'amount'         => $paymentAmount,
                    'payment_date'   => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'note'           => $request->note,
                    'user_id'        => Auth::id(),
                ]);

                // 2. Waterfall: Pay off oldest purchases for this company
                $purchases = purchases::where('company_id', $request->company_id)
                    ->where('due_amount', '>', 0)
                    ->orderBy('purchase_date', 'asc')
                    ->get();

                foreach ($purchases as $purchase) {
                    if ($remaining <= 0) break;

                    $due = $purchase->due_amount;
                    $paymentToApply = min($remaining, $due);

                    $purchase->paid_amount += $paymentToApply;
                    $purchase->due_amount  -= $paymentToApply;

                    // Set status: pending, partial, or paid
                    $purchase->payment_status = ($purchase->due_amount <= 0) ? 'paid' : 'partial';
                    $purchase->save();

                    $remaining -= $paymentToApply;
                }

                return "Payment of TK" . number_format($paymentAmount, 2) . " successfully recorded for Company.";
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
    public function destroy(PurchasePayment $purchasePayment)
    {
        //
    }
}
