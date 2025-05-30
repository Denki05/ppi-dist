<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ApiCustomerController;
use App\Http\Controllers\ApiInvoiceController;

Route::middleware('verify.apikey')->group(function () {
    Route::get('customers', [ApiCustomerController::class, 'getApiDataCustomer']);
    Route::get('products', [ApiCustomerController::class, 'getApiDataProduct']);
    Route::get('brands', [ApiCustomerController::class, 'getApiDataBrand']);
    Route::get('invoices', [ApiInvoiceController::class, 'getApiData']);
    Route::get('categories', [ApiCustomerController::class, 'getApiDataCategoryProduct']);
});