<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\AccountSettingController;
use App\Http\Controllers\ProductReturnController;
use App\Http\Controllers\ReportController;

Auth::routes();

Route::middleware(['auth'])->group(function () {
 Route::get('/', [HomeController::class, 'index'])->name('home');
 Route::get('/v1/dashboard', [HomeController::class, 'dashboardOne']);
 Route::get('/v1/dashboard/data', [HomeController::class, 'dashboardOneData'])->name('dashboardOne.data');
 Route::get('/dashboard/data/{period}', [HomeController::class, 'getDashboardData']);
 Route::get('/export/database', [HomeController::class, 'exportDatabase'])->name('export.database');

 //--------------------------Inventory------------------------------------------------

 //=========== Start Product
 //Custom route FIRST
 Route::get('products/stock_value_report', [ProductController::class, 'stockValueReport'])->name('products.stockValueReport');
 Route::get('products/damage_report', [ProductController::class, 'damageReport'])->name('products.damage');
 Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
 Route::resource('products', ProductController::class);
 //===========End Product


 //--------------------------Purchase------------------------------------------------
 Route::get('/companies/{id}/ledger', [CompanyController::class, 'ledger'])->name('companies.ledger');
 Route::resource('companies', CompanyController::class);
 Route::resource('purchases', PurchasesController::class);



 //--------------------------Sales------------------------------------------------
 //DSR Ledger Report
 Route::get('/dsr_opening', [SaleController::class, 'dsrOpening'])->name('dsr.dsr_opening');
 Route::post('/dsr_opening', [SaleController::class, 'dsrOpeningStore'])->name('dsr.opening_store');

 //customer not use 
 Route::get('/customers/{id}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
 Route::resource('customers', CustomerController::class);
 //customer not use 
 Route::resource('orders', OrdersController::class);
 Route::get('/orders/{id}', [OrdersController::class, 'show'])->name('orders.show');
 Route::post('/sales-due-store', [SaleController::class, 'storeDueCustomer'])->name('sales.due.store');
 Route::get('/sales/company-sales', [SaleController::class, 'companySalesReport'])->name('sales.company-sales');
 Route::get('/sales/sr-sales', [SaleController::class, 'srSalesReport'])->name('sales.sr-sales');
 Route::get('/sales/product-sales', [SaleController::class, 'productSalesReport'])->name('sales.product-sales');
 Route::resource('sales', SaleController::class);
 Route::get('/customer/{customerId}/purchased-products', [SalesReturnController::class, 'getPurchasedProducts']);
 Route::resource('sales_returns', SalesReturnController::class);
 Route::resource('returns', ProductReturnController::class);

 //--------------------------Finance------------------------------------------------
 Route::resource('purchase_payments', PurchasePaymentController::class);
 Route::get('payments/pending-dues/{customerId}', [PaymentController::class, 'getPendingDues'])->name('payments.pending-dues');
 Route::resource('payments', PaymentController::class);
 Route::resource('expenses', ExpenseController::class);
 Route::get('/accounts/profit-loss-report', [AccountSettingController::class, 'profitLossReport'])->name('accounts.profit-loss');
 Route::get('/accounts', [AccountSettingController::class, 'index'])->name('accounts.index');
 Route::post('/accounts/update-opening', [AccountSettingController::class, 'updateOpening'])->name('accounts.updateOpening');

 //--------------------------Report------------------------------------------------
 Route::prefix('report')->group(function () {
  Route::get('/product_stock', [ReportController::class, 'productStock'])->name('reports.product_stock');
  Route::get('/all_dsr_due', [ReportController::class, 'allDsrDue'])->name('reports.all_dsr_due');
  Route::get('/dsr_ledger', [ReportController::class, 'dsrLedger'])->name('reports.dsr_ledger');
  Route::get('/expense', [ReportController::class, 'expense'])->name('reports.expense');
  Route::get('/dsr_sales', [ReportController::class, 'dsrSales'])->name('reports.dsr_sales');
  Route::get('/get_dsr_sales_details', [ReportController::class, 'getDsrSalesDetails'])->name('reports.get_dsr_sales_details');
  Route::get('/company_ledger/{id}', [ReportController::class, 'companyLedger'])->name('reports.company_ledger');
  Route::get('/company_summary', [ReportController::class, 'companySummary'])->name('reports.company_summary');
 });


 //--------------------------Setting------------------------------------------------
 Route::resource('employees', EmployeeController::class);
});



// use Illuminate\Support\Facades\DB;

// Route::get('/abc', function () {
//  $m_product = DB::table('products676767')->get();
//  foreach ($m_product as $row) {

//   DB::table('products')->where('id', $row->id)->update(['opening_stock' => $row->stock]);
//  }


//  return "success";
// });
