<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ApiCustomerController;
use App\Http\Controllers\ApiInvoiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Ambil Semua Data Customer
Route::get('customers', [ApiCustomerController::class, 'getApiDataCustomer']);
Route::get('products', [ApiCustomerController::class, 'getApiDataProduct']);
Route::get('brands', [ApiCustomerController::class, 'getApiDataBrand']);
Route::get('member', [ApiCustomerController::class, 'getApiMember']);
Route::get('invoices', [ApiInvoiceController::class, 'getApiData']);

// 🔹 Generate Report (POST karena ada parameter filter)
Route::post('generate-report', [ApiCustomerController::class, 'generateReportApi']);

// generate api file_doctor
Route::get('/customers/search', [ApiCustomerController::class, 'getApiFileDoctor']);