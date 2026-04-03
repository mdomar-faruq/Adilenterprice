<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\SalesReturnController;

Auth::routes();

Route::middleware(['auth'])->group(function () {
 Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

 //--------------------------Inventory------------------------------------------------

 //=========== Start Product
 //Custom route FIRST
 Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
 Route::resource('products', ProductController::class);
 //===========End Product


 //--------------------------Purchase------------------------------------------------
 Route::get('/companies/{id}/ledger', [CompanyController::class, 'ledger'])->name('companies.ledger');
 Route::resource('companies', CompanyController::class);
 Route::resource('purchases', PurchasesController::class);



 //--------------------------Sales------------------------------------------------
 Route::get('/customers/{id}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
 Route::resource('customers', CustomerController::class);
 Route::resource('orders', OrdersController::class);
 Route::get('/orders/{id}', [OrdersController::class, 'show'])->name('orders.show');
 Route::resource('sales', SaleController::class);
 Route::get('/customer/{customerId}/purchased-products', [SalesReturnController::class, 'getPurchasedProducts']);
 Route::resource('sales_returns', SalesReturnController::class);

 //--------------------------Finance------------------------------------------------
 Route::resource('purchase_payments', PurchasePaymentController::class);
 Route::resource('payments', PaymentController::class);
 Route::resource('expenses', ExpenseController::class);
});
