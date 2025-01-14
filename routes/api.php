<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ApiCustomerController;
use App\Http\Controllers\ApiInvoiceController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Ambil Semua Data Customer
Route::get('customers', [ApiCustomerController::class, 'getApiData']);

// Ambil Data invoice
Route::get('invoices', [ApiInvoiceController::class, 'getApiData']); 