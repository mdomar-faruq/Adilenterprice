<?php

namespace App\Http\Controllers;

use App\Models\AccountSetting;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PurchasePayment;
use App\Models\purchases;
use App\Models\Sale;
use Illuminate\Http\Request;

class AccountSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Get Opening Balance (assuming one record exists)
        $settings = AccountSetting::first() ?? new AccountSetting(['opening_balance' => 0]);
        $opening = $settings->opening_balance;

        // 2. Outgoing Money (-)
        // $purchases = purchases::sum('total_amount');
        $expenses = Expense::sum('amount');
        $paymentVouchers = PurchasePayment::sum('amount'); // Payments you made to suppliers

        // 3. Incoming Money (+)
        $sales = Sale::sum('paid_amount'); // Only use paid amount for cash flow
        // $dueSales = Sale::sum('due_amount'); // Only use due amount for cash flow
        // $customerPayments = Payment::sum('amount'); // Collections from previous dues

        // 4. Calculate Final Balance
        // Formula: Opening + (Incoming) - (Outgoing)
        $currentBalance = $opening + $sales - ($expenses + $paymentVouchers);

        return view('accounts.index', compact(
            'opening',
            'expenses',
            'paymentVouchers',
            'sales',
            'currentBalance'
        ));
    }

    // Update Opening Balance Dynamically
    public function updateOpening(Request $request)
    {
        $settings = AccountSetting::first() ?? new AccountSetting();
        $settings->opening_balance = $request->opening_balance;
        $settings->save();
        return back()->with('success', 'Opening balance updated!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountSetting $accountSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountSetting $accountSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountSetting $accountSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountSetting $accountSetting)
    {
        //
    }
}
