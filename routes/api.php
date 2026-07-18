<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ApiCustomerController;
use App\Http\Controllers\ApiInvoiceController;
use App\Http\Controllers\ReportRequestController;
use App\Http\Controllers\ProductAssetsController;
use App\Http\Controllers\Api\PickerApiController;

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

// Request Report
Route::post('/request/report', [ReportRequestController::class, 'handle']);

Route::get('/product-pack', [ReportRequestController::class, 'getProductPack']);

// PRODUCT & ASSETS
Route::get('product-assets', [ProductAssetsController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Picker App API Routes
|--------------------------------------------------------------------------
*/
// Rute publik untuk login
Route::post('picker/login', [PickerApiController::class, 'login']);

// Rute yang dilindungi Token (menggunakan custom middleware 'picker.auth' kita)
Route::group(['middleware' => 'picker.auth', 'prefix' => 'picker'], function () {
    Route::get('tasks/ready', [PickerApiController::class, 'getReadyTasks']);
    // Nanti rute update status akan diletakkan di sini:
    // Route::post('tasks/{id}/pack', [PickerApiController::class, 'submitPack']);
});