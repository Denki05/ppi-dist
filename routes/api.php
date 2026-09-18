<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ApiCustomerController;
use App\Http\Controllers\ApiInvoiceController;
use App\Http\Controllers\ProductAssetsController;
use App\Http\Controllers\Api\PickerApiController;
use App\Http\Controllers\Api\AoSalesOrderApiController;

Route::get('customers', [ApiCustomerController::class, 'getApiDataCustomer']);
Route::get('products', [ApiCustomerController::class, 'getApiDataProduct']);
Route::get('brands', [ApiCustomerController::class, 'getApiDataBrand']);
Route::get('invoices', [ApiInvoiceController::class, 'getApiData']);
Route::get('categories', [ApiCustomerController::class, 'getApiDataCategoryProduct']);
Route::get('product-assets', [ProductAssetsController::class, 'index']);

// === API khusus modul AO (sys-af) untuk fitur Add SO Awal ===
Route::group(['prefix' => 'ao/so-awal', 'middleware' => 'ao.apikey'], function () {
    Route::get('/brands', [AoSalesOrderApiController::class, 'brands']);
    Route::get('/products', [AoSalesOrderApiController::class, 'products']);
    Route::get('/kemasan', [AoSalesOrderApiController::class, 'kemasan']);
    Route::get('/next-code', [AoSalesOrderApiController::class, 'nextCode']);
    // Sinkron dua arah: import transaksi->AO (pull) + delete sync AO->transaksi
    Route::get('/list', [AoSalesOrderApiController::class, 'list']);
    Route::get('/detail/{so_code}', [AoSalesOrderApiController::class, 'detail']);
    Route::delete('/{so_code}', [AoSalesOrderApiController::class, 'destroyApi']);
    Route::post('/store', [AoSalesOrderApiController::class, 'store']);
    // Revisi AO: cek status + kirim revisi (hanya jika status=3 di transaksi)
    Route::get('/status/{so_code}', [AoSalesOrderApiController::class, 'status']);
    Route::post('/update', [AoSalesOrderApiController::class, 'updateFromAo']);
});

/*
|--------------------------------------------------------------------------
| Picker App API Routes
|--------------------------------------------------------------------------
*/

// Rute publik untuk login
Route::post('picker/login', [PickerApiController::class, 'login']);
 
// Rute yang dilindungi Token
Route::group(['middleware' => 'picker.auth', 'prefix' => 'picker'], function () {
    Route::get('tasks/ready', [PickerApiController::class, 'getReadyTasks']);
    Route::get('tasks/{id}', [PickerApiController::class, 'getTaskDetail']);
    Route::post('tasks/{id}/pack', [PickerApiController::class, 'packTask']);
    Route::get('tasks/{id}/label', [PickerApiController::class, 'printLabel']);
});