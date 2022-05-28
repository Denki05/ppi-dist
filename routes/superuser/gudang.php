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

    Route::group(['as' => 'stock_sales_order.', 'prefix' => '/stock_sales_order'], function () {
        Route::get('/import_template', 'StockSalesOrderController@import_template')->name('import_template');
        Route::post('/import', 'StockSalesOrderController@import')->name('import');
        Route::get('/export', 'StockSalesOrderController@export')->name('export');
    });
    Route::resource('stock_sales_order', 'StockSalesOrderController');
});