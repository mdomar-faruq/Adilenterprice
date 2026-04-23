<?php

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

Auth::routes();

Route::middleware(['auth'])->group(function () {
 Route::get('/', [HomeController::class, 'index'])->name('home');
 Route::get('/dashboard/data/{period}', [HomeController::class, 'getDashboardData']);

 //--------------------------Inventory------------------------------------------------

 //=========== Start Product
 //Custom route FIRST
 Route::get('products/stock_value_report', [ProductController::class, 'stockValueReport'])->name('products.stockValueReport');
 Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
 Route::resource('products', ProductController::class);
 //===========End Product


 //--------------------------Purchase------------------------------------------------
 Route::get('/companies/{id}/ledger', [CompanyController::class, 'ledger'])->name('companies.ledger');
 Route::resource('companies', CompanyController::class);
 Route::resource('purchases', PurchasesController::class);



 //--------------------------Sales------------------------------------------------
 //DSR Ledger Report
 Route::get('/dsr/ledger', [SaleController::class, 'dsrLedger'])->name('dsr.ledger');
 Route::post('/dsr_opening/store', [SaleController::class, 'dsrOpeningStore'])->name('dsr_opening.store');
 Route::get('/dsr/{id}/ledger', [SaleController::class, 'DsrDetailsledger'])->name('dsr_details.ledger');

 //customer not use 
 Route::get('/customers/{id}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
 Route::resource('customers', CustomerController::class);
 //
 Route::resource('orders', OrdersController::class);
 Route::get('/orders/{id}', [OrdersController::class, 'show'])->name('orders.show');
 Route::post('/sales-due-store', [SaleController::class, 'storeDueCustomer'])->name('sales.due.store');
 Route::resource('sales', SaleController::class);
 Route::get('/customer/{customerId}/purchased-products', [SalesReturnController::class, 'getPurchasedProducts']);
 Route::resource('sales_returns', SalesReturnController::class);

 //--------------------------Finance------------------------------------------------
 Route::resource('purchase_payments', PurchasePaymentController::class);
 Route::get('payments/pending-dues/{customerId}', [PaymentController::class, 'getPendingDues'])->name('payments.pending-dues');
 Route::resource('payments', PaymentController::class);
 Route::resource('expenses', ExpenseController::class);
 Route::get('/accounts', [AccountSettingController::class, 'index'])->name('accounts.index');
 Route::post('/accounts/update-opening', [AccountSettingController::class, 'updateOpening'])->name('accounts.updateOpening');

 //--------------------------Setting------------------------------------------------
 Route::resource('employees', EmployeeController::class);
});
