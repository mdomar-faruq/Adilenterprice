<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\purchases;
use App\Models\Expense;
use App\Models\Customer;
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
        return view('home');
    }

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
