<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login'])->name('login');

/*
|--------------------------------------------------------------------------
| Protected routes (require a valid Sanctum token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Invoices belonging to a specific customer.
    Route::get('/customers/{customer}/invoices', [InvoiceController::class, 'forCustomer'])
        ->whereNumber('customer');

    Route::apiResource('customers', CustomerController::class)
        ->whereNumber('customer');
    Route::apiResource('invoices', InvoiceController::class)
        ->whereNumber('invoice');
});
