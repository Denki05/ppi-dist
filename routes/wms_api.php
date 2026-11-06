<?php

use App\Http\Controllers\Api\WmsAuthController;

/*
|--------------------------------------------------------------------------
| WMS (SPV Gudang) API Routes
|--------------------------------------------------------------------------
| Dipisah dari routes/api.php karena grup "api" memakai throttle:60,1 yang
| dihitung PER IP. WMS memanggil server-to-server, jadi seluruh user WMS
| berbagi satu jatah 60/menit. Di sini jatahnya dipisah (tanpa throttle grup
| "api"), dengan batas 300/menit agar beberapa user bisa bekerja bersamaan.
|
| Login WMS memakai handoff SSO modul; token ditukar sekali di exchange.
| Setelah itu semua request memakai bearer token (tanpa query DB per request).
*/

Route::post('wms/auth/exchange', [WmsAuthController::class, 'exchange'])
    ->middleware('throttle:20,1');

Route::group(['prefix' => 'wms', 'middleware' => ['wms.auth', 'throttle:300,1']], function () {
    Route::get('bootstrap', [WmsAuthController::class, 'bootstrap']);
    Route::get('dashboard', [\App\Http\Controllers\Api\WmsDashboardController::class, 'index']);
    Route::get('stock', [\App\Http\Controllers\Api\WmsStockController::class, 'index']);
    Route::get('stock/{warehouseId}/detail/{encoded}', [\App\Http\Controllers\Api\WmsStockController::class, 'detail'])->where('warehouseId', '[0-9]+');
    Route::get('stock/{warehouseId}/detail/{encoded}/pdf', [\App\Http\Controllers\Api\WmsStockController::class, 'pdf'])->where('warehouseId', '[0-9]+');
    Route::get('receiving', [\App\Http\Controllers\Api\WmsReceivingController::class, 'index']);
    Route::get('receiving/{id}', [\App\Http\Controllers\Api\WmsReceivingController::class, 'show'])->where('id', '[0-9]+');
    Route::post('receiving/{id}/acc', [\App\Http\Controllers\Api\WmsReceivingController::class, 'acc'])->where('id', '[0-9]+');
    Route::post('receiving/{id}/publish', [\App\Http\Controllers\Api\WmsReceivingController::class, 'publish'])->where('id', '[0-9]+');
    Route::post('receiving/{id}/cancel', [\App\Http\Controllers\Api\WmsReceivingController::class, 'cancel'])->where('id', '[0-9]+');
    Route::post('receiving/{id}/rollback', [\App\Http\Controllers\Api\WmsReceivingController::class, 'rollback'])->where('id', '[0-9]+');
    Route::post('receiving/{id}/details/{detailId}/qc', [\App\Http\Controllers\Api\WmsReceivingController::class, 'storeQc'])->where('id', '[0-9]+')->where('detailId', '[0-9]+');
    Route::post('receiving/{id}/details/{detailId}/qc/{logId}/approve', [\App\Http\Controllers\Api\WmsReceivingController::class, 'approveQc'])->where('id', '[0-9]+')->where('detailId', '[0-9]+')->where('logId', '[0-9]+');
    Route::post('receiving/{id}/details/{detailId}/qc/{logId}/delete', [\App\Http\Controllers\Api\WmsReceivingController::class, 'deleteQc'])->where('id', '[0-9]+')->where('detailId', '[0-9]+')->where('logId', '[0-9]+');
    Route::get('receiving-transaksi', [\App\Http\Controllers\Api\WmsReceivingController::class, 'transaksi']);
    Route::get('receiving-transaksi/{id}', [\App\Http\Controllers\Api\WmsReceivingController::class, 'transaksiShow'])->where('id', '[0-9]+');
    Route::get('receiving-transaksi/{id}/pdf', [\App\Http\Controllers\Api\WmsReceivingController::class, 'transaksiPdf'])->where('id', '[0-9]+');
    Route::post('receiving-transaksi/{id}/publish', [\App\Http\Controllers\Api\WmsReceivingController::class, 'transaksiPublish'])->where('id', '[0-9]+');
    Route::post('receiving-transaksi/{id}/acc', [\App\Http\Controllers\Api\WmsReceivingController::class, 'transaksiAcc'])->where('id', '[0-9]+');
    Route::post('receiving-transaksi/{id}/details', [\App\Http\Controllers\Api\WmsReceivingController::class, 'transaksiAddDetail'])->where('id', '[0-9]+');
    Route::post('receiving-transaksi/{id}/details/{detailId}/delete', [\App\Http\Controllers\Api\WmsReceivingController::class, 'transaksiDeleteDetail'])->where('id', '[0-9]+')->where('detailId', '[0-9]+');
    Route::post('receiving-transaksi/{id}/details/{detailId}/qc', [\App\Http\Controllers\Api\WmsReceivingController::class, 'transaksiStoreQc'])->where('id', '[0-9]+')->where('detailId', '[0-9]+');
    Route::post('receiving-transaksi/{id}/details/{detailId}/qc/{logId}/delete', [\App\Http\Controllers\Api\WmsReceivingController::class, 'transaksiDeleteQc'])->where('id', '[0-9]+')->where('detailId', '[0-9]+')->where('logId', '[0-9]+');
    Route::get('komplain', [\App\Http\Controllers\Api\WmsKomplainController::class, 'index']);
    Route::get('komplain/{id}', [\App\Http\Controllers\Api\WmsKomplainController::class, 'show'])->where('id', '[0-9]+');
    Route::post('komplain/{id}/acc', [\App\Http\Controllers\Api\WmsKomplainController::class, 'acc'])->where('id', '[0-9]+');
    Route::get('komplain/{id}/pdf', [\App\Http\Controllers\Api\WmsKomplainController::class, 'pdf'])->where('id', '[0-9]+');
    Route::get('mutasi', [\App\Http\Controllers\Api\WmsMutasiController::class, 'index']);
    Route::get('mutasi/{type}/{id}', [\App\Http\Controllers\Api\WmsMutasiController::class, 'show'])->where('id', '[0-9]+');
    Route::get('mutasi/{type}/{id}/pdf', [\App\Http\Controllers\Api\WmsMutasiController::class, 'pdf'])->where('id', '[0-9]+');
    Route::get('mutasi/{type}/{id}/image', [\App\Http\Controllers\Api\WmsMutasiController::class, 'image'])->where('id', '[0-9]+');
    Route::post('mutasi/{type}/{id}/checklist', [\App\Http\Controllers\Api\WmsMutasiController::class, 'checklist'])->where('id', '[0-9]+');
    Route::post('mutasi/{type}/{id}/cancel', [\App\Http\Controllers\Api\WmsMutasiController::class, 'cancel'])->where('id', '[0-9]+');
    Route::post('mutasi/{type}/{id}/next', [\App\Http\Controllers\Api\WmsMutasiController::class, 'next'])->where('id', '[0-9]+');
    Route::post('mutasi/{type}/{id}/barang', [\App\Http\Controllers\Api\WmsMutasiController::class, 'updateBarang'])->where('id', '[0-9]+');
    Route::get('notifications', [\App\Http\Controllers\Api\WmsNotificationController::class, 'index']);
    Route::post('notifications/read-all', [\App\Http\Controllers\Api\WmsNotificationController::class, 'markAllRead']);
    Route::post('notifications/{id}/read', [\App\Http\Controllers\Api\WmsNotificationController::class, 'markRead'])->where('id', '[0-9a-fA-F-]{36}');
    Route::get('delivery-orders', [\App\Http\Controllers\Api\WmsDeliveryOrderController::class, 'index']);
    Route::get('delivery-orders/{id}', [\App\Http\Controllers\Api\WmsDeliveryOrderController::class, 'show'])->where('id', '[0-9]+');
    Route::get('delivery-orders/{id}/print-spk', [\App\Http\Controllers\Api\WmsDeliveryOrderController::class, 'printManifest'])->where('id', '[0-9]+');
    Route::get('delivery-orders/{id}/print-sj', [\App\Http\Controllers\Api\WmsDeliveryOrderController::class, 'printSuratJalan'])->where('id', '[0-9]+');
    Route::post('delivery-orders/{id}/packed', [\App\Http\Controllers\Api\WmsDeliveryOrderController::class, 'packed'])->where('id', '[0-9]+');
    Route::post('delivery-orders/{id}/sending', [\App\Http\Controllers\Api\WmsDeliveryOrderController::class, 'sending'])->where('id', '[0-9]+');
    Route::post('delivery-orders/{id}/sent', [\App\Http\Controllers\Api\WmsDeliveryOrderController::class, 'sent'])->where('id', '[0-9]+');
    Route::get('performance', [\App\Http\Controllers\Api\WmsDeliveryOrderController::class, 'performance']);
});
