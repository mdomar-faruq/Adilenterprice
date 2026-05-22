<?php

namespace App\Http\Controllers;

use App\Models\AccountSetting;
use App\Models\Sale;
use App\Models\purchases;
use App\Models\Expense;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('dashboard.index');
        // return view('home');
    }

    public function dashboardOne()
    {
        return view('dashboard.index');
    }

    public function dashboardOneData()
    {
        $currentAccountAmount = 0;
        $currentStockValue = 0;
        $totalDsrDue = 0;
        $currentCompanyDue = 0;
        $overallSales = 0;
        $overallPurchase = 0;
        $overallExpense = 0;
        $salesDamageAmount = 0;

        // =========================================================================
        // STEP-BY-STEP CALCULATION: Current Account Balance (Liquid Cash)
        // =========================================================================

        // 1. Get Opening Balance (Fallback safely if no record exists)
        $settings = AccountSetting::first() ?? new AccountSetting(['opening_balance' => 0]);
        $opening = (float) $settings->opening_balance;

        // 2. Outgoing Money (-)
        $expenses = (float) Expense::sum('amount');
        $paymentVouchers = (float) PurchasePayment::sum('amount'); // Cash paid out to suppliers
        $totalOutgoing = $expenses + $paymentVouchers;

        // 3. Incoming Money (+)
        $sales = (float) Sale::sum('paid_amount'); // Actual cash collected from sales
        $dsr_opening = (float) Employee::sum('opening_paid'); // Actual cash collected from DSR staff
        $totalIncoming = $sales + $dsr_opening;

        // 4. Calculate Final Liquid Cash Balance
        // Formula: Opening + (Incoming) - (Outgoing)
        $currentAccountAmount = $opening + $totalIncoming - $totalOutgoing;
        // =========================================================================
        // STEP 2: Current Product Stock Value (Asset Value Logic)
        // =========================================================================
        // This aggregates matching values directly at the DB level for blazing-fast speed
        $currentStockValue = (float) Product::where('valid', 1)
            ->sum(\DB::raw('stock * purchase_price'));

        // =========================================================================
        // STEP 3: Total DSR Due (Receivables Logic)
        // =========================================================================
        // Instead of looping, we run high-speed global sum queries on index columns
        $totalDsrDue = (float) \DB::table('employees')->sum('opening_balance')
            + (float) \DB::table('sales_due_customers')->sum('due_amount')
            - (float) \DB::table('payments')->sum('amount');


        // =========================================================================
        // STEP 4: Current Company Due (Payables Logic)
        // =========================================================================
        $lifetimePurchases = (float) \DB::table('purchases')->sum('total_amount');

        $currentCompanyDue = (float) \DB::table('companies')->sum('opening_balance')
            + $lifetimePurchases
            - $paymentVouchers; // Reusing $paymentVouchers sum from Step 1

        // =========================================================================
        // REMAINING TRADE STATEMENT FIELDS
        // =========================================================================
        $overallSales         = (float) Sale::sum('total_amount');
        $overallPurchase         = $lifetimePurchases;
        $overallExpense         = $expenses;

        $salesDamageAmount = (float) \DB::table('sales_damage_items')
            ->join('products', 'sales_damage_items.product_id', '=', 'products.id')
            ->sum(\DB::raw('sales_damage_items.quantity * products.purchase_price'));


        return response()->json([
            'currentAccountAmount' => (float) $currentAccountAmount,
            'currentStockValue'    => (float) $currentStockValue,
            'totalDsrDue'          => (float) $totalDsrDue,
            'currentCompanyDue'    => (float) $currentCompanyDue,
            'overallSales'         => (float) $overallSales,
            'overallPurchase'      => (float) $overallPurchase,
            'overallExpense'       => (float) $overallExpense,
            'salesDamageAmount'    => (float) $salesDamageAmount,
            'timestamp'            => now()->format('d M, Y h:i A')
        ]);
    }


    //this part test
    public function getDashboardData($period)
    {
        $now = Carbon::now();
        $startDate = match ($period) {
            'daily'   => $now->copy()->startOfDay(),
            'weekly'  => $now->copy()->startOfWeek(),
            'monthly' => $now->copy()->startOfMonth(),
            'yearly'  => $now->copy()->startOfYear(),
            default   => $now->copy()->startOfWeek(),
        };

        // Summary Totals
        $cards = [
            'sales' => number_format(Sale::where('created_at', '>=', $startDate)->sum('total_amount'), 2),
            'purchases' => number_format(purchases::where('created_at', '>=', $startDate)->sum('total_amount'), 2),
            'expenses' => number_format(Expense::where('created_at', '>=', $startDate)->sum('amount'), 2),
            'customers' => Customer::where('created_at', '>=', $startDate)->count(),
        ];

        // Chart Logic
        $labels = [];
        $salesData = [];
        $expenseData = [];
        if ($period == 'daily') {
            for ($i = 0; $i < 24; $i += 4) {
                $labels[] = $i . ":00";
                $salesData[] = Sale::whereDate('created_at', Carbon::today())->whereRaw('HOUR(created_at) >= ? AND HOUR(created_at) < ?', [$i, $i + 4])->sum('total_amount') ?: 0;
                $expenseData[] = Expense::whereDate('created_at', Carbon::today())->whereRaw('HOUR(created_at) >= ? AND HOUR(created_at) < ?', [$i, $i + 4])->sum('amount') ?: 0;
            }
        } elseif ($period == 'weekly') {
            $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            foreach ($labels as $key => $day) {
                $date = Carbon::now()->startOfWeek()->addDays($key);
                $salesData[] = Sale::whereDate('created_at', $date)->sum('total_amount') ?: 0;
                $expenseData[] = Expense::whereDate('created_at', $date)->sum('amount') ?: 0;
            }
        } elseif ($period == 'monthly') {
            $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            for ($i = 1; $i <= 4; $i++) {
                $start = Carbon::now()->startOfMonth()->addWeeks($i - 1);
                $end = ($i == 4) ? Carbon::now()->endOfMonth() : Carbon::now()->startOfMonth()->addWeeks($i);
                $salesData[] = Sale::whereBetween('created_at', [$start, $end])->sum('total_amount') ?: 0;
                $expenseData[] = Expense::whereBetween('created_at', [$start, $end])->sum('amount') ?: 0;
            }
        } else {
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for ($m = 1; $m <= 12; $m++) {
                $salesData[] = Sale::whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', $m)->sum('total_amount') ?: 0;
                $expenseData[] = Expense::whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', $m)->sum('amount') ?: 0;
            }
        }

        return response()->json(['cards' => $cards, 'chart' => ['labels' => $labels, 'sales' => $salesData, 'expenses' => $expenseData]]);
    }
}
