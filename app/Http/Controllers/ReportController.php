<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payment;
use App\Models\SalesDueCustomer;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function productStock(Request $request)
    {
        $start_date = $request->start_date ?? date('Y-m-01');
        $end_date = $request->end_date ?? date('Y-m-d');

        // --- SUBQUERY 1: CALCULATE OPENING STOCK PRIOR TO START DATE ---
        // Formula: System Opening Balance + Historical Purchases - Historical Sales - Historical Damages
        $prevPurchases = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->where('purchases.purchase_date', '<', $start_date)
            ->groupBy('product_id');

        $prevSales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->where('sales.sale_date', '<', $start_date)
            ->groupBy('product_id');

        $prevDamages = DB::table('sales_damage_items')
            ->join('sales', 'sales_damage_items.sale_id', '=', 'sales.id')
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->where('sales.sale_date', '<', $start_date)
            ->groupBy('product_id');

        // --- SUBQUERY 2: METRICS WITHIN THE SELECTED DATE RANGE ---
        $currentPurchases = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->whereBetween('purchases.purchase_date', [$start_date, $end_date])
            ->groupBy('product_id');

        $currentSales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->whereBetween('sales.sale_date', [$start_date, $end_date])
            ->groupBy('product_id');

        $currentDamages = DB::table('sales_damage_items')
            ->join('sales', 'sales_damage_items.sale_id', '=', 'sales.id')
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->whereBetween('sales.sale_date', [$start_date, $end_date])
            ->groupBy('product_id');

        // --- MASTER QUERY: JOINING EVERYTHING TOGETHER ---
        $report = DB::table('products')
            ->leftJoinSub($prevPurchases, 'prev_purch', 'products.id', '=', 'prev_purch.product_id')
            ->leftJoinSub($prevSales, 'prev_sales', 'products.id', '=', 'prev_sales.product_id')
            ->leftJoinSub($prevDamages, 'prev_dmg', 'products.id', '=', 'prev_dmg.product_id')
            ->leftJoinSub($currentPurchases, 'curr_purch', 'products.id', '=', 'curr_purch.product_id')
            ->leftJoinSub($currentSales, 'curr_sales', 'products.id', '=', 'curr_sales.product_id')
            ->leftJoinSub($currentDamages, 'curr_dmg', 'products.id', '=', 'curr_dmg.product_id')
            ->select(
                'products.id',
                'products.name',
                // Opening Stock Calculation
                DB::raw('(
                COALESCE(products.opening_stock, 0) + 
                COALESCE(prev_purch.qty, 0) +
                COALESCE(prev_dmg.qty, 0) -
                COALESCE(prev_sales.qty, 0)
            ) as opening_balance'),
                // Current Period Activities
                DB::raw('COALESCE(curr_purch.qty, 0) as purchased_qty'),
                DB::raw('COALESCE(curr_sales.qty, 0) as sold_qty'),
                DB::raw('COALESCE(curr_dmg.qty, 0) as damaged_qty'),
                // Final Stock Calculation
                DB::raw('(
                (COALESCE(products.opening_stock, 0) + COALESCE(prev_purch.qty, 0) - COALESCE(prev_sales.qty, 0) + COALESCE(prev_dmg.qty, 0)) +
                COALESCE(curr_purch.qty, 0) +
                COALESCE(curr_dmg.qty, 0) -
                COALESCE(curr_sales.qty, 0)
            ) as final_stock')
            )
            ->orderBy('products.name', 'asc')
            ->get();

        return view('reports.product_stock', compact('report', 'start_date', 'end_date'));
    }

    public function allDsrDue(Request $request)
    {
        // Default to the first of the month until today if parameters are missing
        $start_date = $request->input('start_date') ?? date('Y-m-01');
        $end_date = $request->input('end_date') ?? date('Y-m-d');

        // Keep these for payments if payments table uses a full timestamp field
        $start_datetime = $start_date . ' 00:00:00';
        $end_datetime   = $end_date . ' 23:59:59';

        // --- SUBQUERY 1: Invoices (Debits) joined with Sales Date ---
        $debits = DB::table('sales_due_customers')
            ->join('sales', 'sales_due_customers.sale_id', '=', 'sales.id') // Joining to get sales date
            ->select('sales_due_customers.customer_id')
            ->selectRaw("SUM(CASE WHEN sales.sale_date < ? THEN sales_due_customers.due_amount ELSE 0 END) as historical_debit", [$start_date])
            ->selectRaw("SUM(CASE WHEN sales.sale_date BETWEEN ? AND ? THEN sales_due_customers.due_amount ELSE 0 END) as current_debit", [$start_date, $end_date])
            ->groupBy('sales_due_customers.customer_id');

        // --- SUBQUERY 2: Payments (Credits) ---
        // If your payments table uses a Y-M-D date column, use $start_date / $end_date here too.
        // If it uses a timestamp created_at/payment_date, keep $start_datetime / $end_datetime.
        $credits = DB::table('payments')
            ->select('customer_id')
            ->selectRaw("SUM(CASE WHEN payment_date < ? THEN amount ELSE 0 END) as historical_credit", [$start_date])
            ->selectRaw("SUM(CASE WHEN payment_date BETWEEN ? AND ? THEN amount ELSE 0 END) as current_credit", [$start_date, $end_date])
            ->groupBy('customer_id');

        // --- MASTER QUERY: Merging Metrics for the Ledger Report ---
        $employees = DB::table('employees')
            ->leftJoinSub($debits, 'deb', 'employees.id', '=', 'deb.customer_id')
            ->leftJoinSub($credits, 'crd', 'employees.id', '=', 'crd.customer_id')
            ->select(
                'employees.id',
                'employees.name',
                'employees.phone',
                'employees.opening_balance as base_opening',

                // B: Calculated Dynamic Opening Balance
                DB::raw('(
                COALESCE(employees.opening_balance, 0) + 
                COALESCE(deb.historical_debit, 0) - 
                COALESCE(crd.historical_credit, 0)
            ) as dynamic_opening_balance'),

                // C: Current Window Activity Columns
                DB::raw('COALESCE(deb.current_debit, 0) as current_invoiced'),
                DB::raw('COALESCE(crd.current_credit, 0) as current_paid'),

                // D: Final Net Outstanding Due
                DB::raw('(
                COALESCE(employees.opening_balance, 0) + 
                COALESCE(deb.historical_debit, 0) - 
                COALESCE(crd.historical_credit, 0) +
                COALESCE(deb.current_debit, 0) - 
                COALESCE(crd.current_credit, 0)
            ) as net_current_due')
            )
            ->orderBy('net_current_due', 'desc')
            ->get();

        return view('reports.all_dsr_due', compact('employees', 'start_date', 'end_date'));
    }


    public function expense(Request $request)
    {
        // 1. Set default date ranges (First of current month through today)
        $start_date = $request->input('start_date') ?? date('Y-m-01');
        $end_date = $request->input('end_date') ?? date('Y-m-d');
        $category_search = $request->input('category');

        $start_datetime = $start_date . ' 00:00:00';
        $end_datetime   = $end_date . ' 23:59:59';

        // 2. Build Query Engine with a Join to pull category labels
        // Assumes your expenses table has an 'expense_category_id' foreign key linked to expense_categories.id
        $query = DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select(
                'expenses.id',
                'expenses.amount',
                'expenses.expense_date',
                'expenses.payment_method',
                'expenses.note',
                'expenses.expense_category_id',
                'expense_categories.name as category_name' // Mapping fixed field context label here
            )
            ->whereBetween('expenses.expense_date', [$start_datetime, $end_datetime]);

        // 3. Conditional Category Search Filter (Looks up string matches on the linked text field name)
        if (!empty($category_search)) {
            $query->where('expense_categories.name', 'LIKE', '%' . $category_search . '%');
        }

        // Fetch records sorted newest to oldest
        $expenses = $query->orderBy('expenses.expense_date', 'desc')->get();

        // 4. Extract existing operational categories cleanly for the dropdown datalist element helper
        $available_categories = DB::table('expense_categories')
            ->distinct()
            ->pluck('name')
            ->filter();

        return view('reports.expense', compact(
            'expenses',
            'start_date',
            'end_date',
            'category_search',
            'available_categories'
        ));
    }

    public function dsrSales(Request $request)
    {
        // 1. Set default date ranges (First of current month through today)
        $start_date = $request->input('start_date') ?? date('Y-m-01');
        $end_date   = $request->input('end_date') ?? date('Y-m-d');
        $dsr_id     = $request->input('dsr_id');

        $start_datetime = $start_date . ' 00:00:00';
        $end_datetime   = $end_date . ' 23:59:59';

        // 2. Fetch active DSR workforce profiles (Removed 'id = 1' constraint to show all DSRs)
        $dsr_list = DB::table('employees')
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        // Subquery A: Aggregate total sold quantities per individual invoice item row
        $soldItemsSub = DB::table('sale_items')
            ->select('sale_id', DB::raw('SUM(quantity) as total_sold_qty')) // Adjust column 'qty' to match your schema
            ->groupBy('sale_id');

        // Subquery B: Aggregate total damage quantities per individual invoice row
        $damageItemsSub = DB::table('sales_damage_items')
            ->select('sale_id', DB::raw('SUM(quantity) as total_damage_qty')) // Adjust column 'qty' to match your schema
            ->groupBy('sale_id');

        // 3. Build Performance Report Query connecting everything safely
        $query = DB::table('sales')
            ->join('employees', 'sales.delivery_id', '=', 'employees.id')
            ->leftJoinSub($soldItemsSub, 'si', 'sales.id', '=', 'si.sale_id')
            ->leftJoinSub($damageItemsSub, 'sdi', 'sales.id', '=', 'sdi.sale_id')
            ->select(
                'sales.delivery_id',
                'employees.name as dsr_name',
                DB::raw('COUNT(sales.id) as total_orders'),
                DB::raw('SUM(COALESCE(si.total_sold_qty, 0)) as total_sold'),
                DB::raw('SUM(COALESCE(sdi.total_damage_qty, 0)) as total_damage'),
                DB::raw('SUM(sales.discount) as total_discount'),
                DB::raw('SUM(sales.extra_amount) as extra_dsr'),
                DB::raw('SUM(sales.total_amount) as net_revenue') // Assumes total_amount represents final collection
            )
            ->whereBetween('sales.created_at', [$start_datetime, $end_datetime])
            ->groupBy('sales.delivery_id', 'employees.name'); // Fixed: Grouping by employees.name instead of users.name

        // Apply conditional DSR filter
        if (!empty($dsr_id)) {
            $query->where('sales.delivery_id', $dsr_id);
        }

        $report = $query->orderBy('net_revenue', 'desc')->get();

        return view('reports.dsr_sales', compact(
            'report',
            'dsr_list',
            'start_date',
            'end_date',
            'dsr_id'
        ));
    }

    public function getDsrSalesDetails(Request $request)
    {
        $dsr_id     = $request->get('dsr_id');
        $start_date = $request->get('start_date') . ' 00:00:00';
        $end_date   = $request->get('end_date') . ' 23:59:59';

        // Query to pull quantities from sales_items, grouping them by company/brand name
        // NOTE: Replace 'company_name' and 'brand' columns if named differently in your database.
        $details = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('companies', 'products.company_id', '=', 'companies.id')

            // Left join a subquery that calculates total damage per sale + product combination
            ->leftJoin(DB::raw('(
        SELECT sale_id, product_id, SUM(quantity) as dmg_qty 
        FROM sales_damage_items 
        GROUP BY sale_id, product_id
    ) as damage_summary'), function ($join) {
                $join->on('sale_items.sale_id', '=', 'damage_summary.sale_id')
                    ->on('sale_items.product_id', '=', 'damage_summary.product_id');
            })

            ->select(
                'companies.name as company',
                'products.name as product_name',
                // Subtract aggregated damage from gross quantity (COALESCE handles cases with 0 damages)
                DB::raw('SUM(sale_items.quantity - COALESCE(damage_summary.dmg_qty, 0)) as units_sold'),
                DB::raw('SUM(sale_items.subtotal) as line_revenue')
            )
            ->where('sales.delivery_id', $dsr_id)
            ->whereBetween('sales.created_at', [$start_date, $end_date])
            ->groupBy('companies.id', 'companies.name', 'products.id', 'products.name')
            ->orderBy('companies.name', 'asc')
            ->orderBy('units_sold', 'desc')
            ->get();

        return response()->json($details);
    }

    //


    public function companyLedger(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        // 1. Establish Default Date Ranges if not explicitly provided
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        // 2. Fetch ALL purchases related to this company context profile
        $purchases = DB::table('purchases')
            ->where('company_id', $id)
            ->select(
                'id',
                'purchase_date as date',
                'purchase_no as reference',
                DB::raw('0 as debit'),
                'total_amount as credit',
                DB::raw('"Purchase" as type')
            )->get();

        // 3. Fetch ALL structural payments related to this company context profile
        $payments = DB::table('purchase_payments')
            ->where('company_id', $id)
            ->select(
                'id',
                'payment_date as date',
                'note as reference',
                'amount as debit',
                DB::raw('0 as credit'),
                DB::raw('"Payment" as type')
            )->get();

        // 4. Concat all logs and sort them in chronological order
        $allTransactions = $purchases->concat($payments)->sortBy('date');

        // 5. Separate Historical logs vs Current window records
        $historicalOpeningBalance = (float) $company->opening_balance;
        $filteredLedger = collect([]);

        foreach ($allTransactions as $item) {
            $itemDate = Carbon::parse($item->date)->toDateString();

            if ($itemDate < $startDate) {
                // Transaction happened BEFORE the target window -> accumulates into custom dynamic opening balance row
                $historicalOpeningBalance += ($item->credit - $item->debit);
            } elseif ($itemDate >= $startDate && $itemDate <= $endDate) {
                // Transaction falls directly inside selected filter dates -> keep for standard viewing rows
                $filteredLedger->push($item);
            }
        }

        // 6. Calculate running balance sequentially over the remaining active filtered subset items
        $runningBalance = $historicalOpeningBalance;
        $filteredLedger->transform(function ($item) use (&$runningBalance) {
            $runningBalance += ($item->credit - $item->debit);
            $item->balance = $runningBalance;
            return $item;
        });

        return view('reports.company_ledger', compact('company', 'filteredLedger', 'historicalOpeningBalance', 'startDate', 'endDate'));
    }
    //

    //
    public function companySummary(Request $request)
    {
        // 1. Set default date limits (Current Month)
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        // 2. Fetch all companies
        $companies = Company::all();

        $reportData = collect([]);

        foreach ($companies as $company) {
            // --- A. HISTORICAL TRANSACTIONS (Before Start Date) ---
            $pastPurchases = DB::table('purchases')
                ->where('company_id', $company->id)
                ->where('purchase_date', '<', $startDate)
                ->sum('total_amount');

            $pastPayments = DB::table('purchase_payments')
                ->where('company_id', $company->id)
                ->where('payment_date', '<', $startDate)
                ->sum('amount');

            // Opening Balance at Start Date = Base Opening + Past Purchases - Past Payments
            $calculatedOpening = (float)$company->opening_balance + ($pastPurchases - $pastPayments);

            // --- B. CURRENT TRANSACTIONS (Within Date Range) ---
            $currentPurchases = DB::table('purchases')
                ->where('company_id', $company->id)
                ->whereBetween('purchase_date', [$startDate, $endDate])
                ->sum('total_amount');

            $currentPayments = DB::table('purchase_payments')
                ->where('company_id', $company->id)
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->sum('amount');

            // --- C. CLOSING BALANCE ---
            $closingBalance = $calculatedOpening + ($currentPurchases - $currentPayments);

            // Package data for this row
            $reportData->push((object)[
                'id' => $company->id,
                'name' => $company->name,
                'opening' => $calculatedOpening,
                'purchases' => $currentPurchases,
                'payments' => $currentPayments,
                'balance' => $closingBalance
            ]);
        }

        return view('reports.companies_summary', compact('reportData', 'startDate', 'endDate'));
    }

    //
    public function dsrLedger(Request $request)
    {

        // Catch optional date filter inputs from request query strings
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $dsr_id = $request->get('employee');

        $customer = Employee::findOrFail($dsr_id);
        $ledgerItems = collect();

        // 1. Calculate Carry-Forward Balance up to the selected window start date
        // Start with the absolute baseline opening balance defined on the employee profile
        $baseOpeningBalance = (float) ($customer->opening_balance ?? 0);

        // Sum up all historic invoices before our selected window start date
        $priorInvoiced = SalesDueCustomer::where('customer_id', $dsr_id)
            ->when($start_date, fn($q) => $q->where('created_at', '<', Carbon::parse($start_date)->startOfDay()))
            ->sum('due_amount');

        // Sum up all historic payments before our selected window start date
        $priorPaid = Payment::where('customer_id', $dsr_id)
            ->when($start_date, fn($q) => $q->where('payment_date', '<', Carbon::parse($start_date)->format('Y-m-d')))
            ->sum('amount');

        // Calculate dynamic carry-forward opening balance
        $runningBalance = $baseOpeningBalance + $priorInvoiced - $priorPaid;

        // Push the opening balance as the very first entry in our ledger items collection
        $ledgerItems->push((object)[
            'date'            => $start_date ? Carbon::parse($start_date)->format('Y-m-d') : $customer->created_at->format('Y-m-d'),
            'sort_timestamp'  => $start_date ? Carbon::parse($start_date)->startOfDay()->timestamp : $customer->created_at->timestamp,
            'type'            => 'Opening Balance',
            'reference'       => 'Carry-Forward Balance',
            'debit'           => 0,
            'credit'          => 0,
            'balance'         => $runningBalance,
        ]);

        // 2. Get Period Invoices (Debits) within the filtered window
        $invoices = SalesDueCustomer::where('customer_id', $dsr_id)
            ->with('sale')
            ->when($start_date, fn($q) => $q->where('created_at', '>=', Carbon::parse($start_date)->startOfDay()))
            ->when($end_date, fn($q) => $q->where('created_at', '<=', Carbon::parse($end_date)->endOfDay()))
            ->get()
            ->map(function ($item) {
                return (object)[
                    'date'            => $item->created_at->format('Y-m-d'),
                    'sort_timestamp'  => $item->created_at->timestamp,
                    'type'            => 'Invoice',
                    'reference'       => '#' . ($item->sale->invoice_no ?? $item->id),
                    'debit'           => (float) $item->due_amount,
                    'credit'          => 0,
                ];
            });

        // 3. Get Period Payments (Credits) within the filtered window
        $payments = Payment::where('customer_id', $dsr_id)
            ->when($start_date, fn($q) => $q->where('payment_date', '>=', Carbon::parse($start_date)->format('Y-m-d')))
            ->when($end_date, fn($q) => $q->where('payment_date', '<=', Carbon::parse($end_date)->format('Y-m-d')))
            ->get()
            ->map(function ($item) {
                // Parse payment date to establish safe sorting times
                $paymentDate = Carbon::parse($item->payment_date);
                return (object)[
                    'date'            => $paymentDate->format('Y-m-d'),
                    'sort_timestamp'  => $paymentDate->startOfDay()->timestamp,
                    'type'            => 'Payment',
                    'reference'       => $item->payment_method . ($item->transaction_no ? ' - ' . $item->transaction_no : ''),
                    'debit'           => 0,
                    'credit'          => (float) $item->amount,
                ];
            });

        // 4. Merge periodic transactions and sort them chronologically
        $periodTransactions = $invoices->concat($payments)->sortBy('sort_timestamp');

        // 5. Build full running balance sheet ledger profile loop
        foreach ($periodTransactions as $row) {
            $runningBalance += ($row->debit - $row->credit);

            $ledgerItems->push((object)[
                'date'      => $row->date,
                'type'      => $row->type,
                'reference' => $row->reference,
                'debit'     => $row->debit,
                'credit'    => $row->credit,
                'balance'   => $runningBalance,
            ]);
        }

        return view('reports.dsr_ledger', [
            'customer'   => $customer,
            'ledger'     => $ledgerItems,
            'start_date' => $start_date,
            'end_date'   => $end_date
        ]);
    }
}
