<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
}
