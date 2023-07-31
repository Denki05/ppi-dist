<?php

Route::group([
    'middleware' => ['auth:superuser'],
    'as' => 'gudang.',
    'prefix' => '/gudang',
    'namespace' => 'Gudang'
], function () {

    Route::group(['as' => 'stock.', 'prefix' => '/stock'], function () {
        Route::get('/', 'StockController@index')->name('index');
        Route::get('/detail', 'StockController@detail')->name('detail');
    });

    Route::group(['as' => 'stock_adjustment.', 'prefix' => '/stock_adjustment'], function () {
        Route::get('/', 'StockAdjustmentController@index')->name('index');
        Route::get('/create', 'StockAdjustmentController@create')->name('create');
        Route::post('/check_product_warehouse', 'StockAdjustmentController@check_product_warehouse')->name('check_product_warehouse');
        Route::post('/store', 'StockAdjustmentController@store')->name('store');
        
    });

    Route::group(['as' => 'purchase_order.', 'prefix' => '/purchase_order'], function () {
        Route::get('/step/{id}', 'PurchaseOrderController@step')->name('step');
        Route::get('{id}/publish', 'PurchaseOrderController@publish')->name('publish');
        Route::get('{id}/save_modify/{save_type}', 'PurchaseOrderController@save_modify')->name('save_modify');
        Route::get('{id}/acc', 'PurchaseOrderController@acc')->name('acc');
        Route::post('/store_item/{id}', 'PurchaseOrderController@store_item')->name('store_item');
        Route::get('/search_sku', 'PurchaseOrderController@search_sku')->name('search_sku');
        Route::get('/get_product', 'PurchaseOrderController@get_product')->name('get_product');
    });
    Route::resource('purchase_order', 'PurchaseOrderController');
});