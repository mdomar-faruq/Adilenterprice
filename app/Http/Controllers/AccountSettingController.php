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

    public function profitLossReport(Request $request)
    {
        $start = $request->get('start_date', date('Y-m-01'));
        $end = $request->get('end_date', date('Y-m-d'));

        // 1. Get Sales and calculate COGS (Cost of Goods Sold)
        $salesData = \App\Models\SaleItem::with('product')
            ->whereHas('sale', function ($q) use ($start, $end) {
                $q->whereBetween('sale_date', [$start, $end]);
            })->get();

        $totalSales = $salesData->sum('subtotal');

        // Total Cost = Quantity Sold * Product Purchase Price
        $totalCost = $salesData->sum(function ($item) {
            return $item->quantity * ($item->product->purchase_price ?? 0);
        });

        $grossProfit = $totalSales - $totalCost;

        // 2. Get Expenses
        $totalExpenses = \App\Models\Expense::whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $netProfit = $grossProfit - $totalExpenses;

        return view('accounts.profit_loss', compact(
            'totalSales',
            'totalCost',
            'grossProfit',
            'totalExpenses',
            'netProfit',
            'start',
            'end'
        ));
    }
}
