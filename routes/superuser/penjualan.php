<?php

Route::group([
    'middleware' => ['auth:superuser'],
    'as' => 'penjualan.',
    'prefix' => '/penjualan',
    'namespace' => 'Penjualan'
], function () {

    Route::group(['as' => 'setting_price.', 'prefix' => '/setting_price'], function () {
        Route::get('/', 'SettingPriceController@index')->name('index');
        Route::get('/{id}/edit', 'SettingPriceController@edit')->name('edit');
        Route::post('/update', 'SettingPriceController@update')->name('update');
        Route::post('/print/product', 'SettingPriceController@print_product')->name('print_product');
        Route::post('/print/product_price', 'SettingPriceController@print_product_price')->name('print_product_price');
        Route::get('/{id}/history', 'SettingPriceController@history')->name('history');
    });

    Route::group(['as' => 'sales_order.', 'prefix' => '/sales_order'], function () {
        Route::get('/', 'SalesOrderController@index')->name('index');
        Route::get('/so_awal', 'SalesOrderController@index_awal')->name('index_awal');
        Route::get('/so_lanjutan', 'SalesOrderController@index_lanjutan')->name('index_lanjutan');
        Route::get('/so_mutasi', 'SalesOrderController@index_mutasi')->name('index_mutasi');
        Route::get('/create', 'SalesOrderController@create')->name('create');
        Route::get('/create/{step}', 'SalesOrderController@create')->name('create');
        Route::get('/{id}/edit/{step}', 'SalesOrderController@edit')->name('edit');
        Route::get('/{id}/detail', 'SalesOrderController@detail')->name('detail');
        Route::post('/store', 'SalesOrderController@store')->name('store');
        Route::post('/update', 'SalesOrderController@update')->name('update');
        Route::post('/lanjutkan', 'SalesOrderController@lanjutkan')->name('lanjutkan');
        Route::post('/kembali', 'SalesOrderController@kembali')->name('kembali');
        Route::post('/tidak_lanjut_so', 'SalesOrderController@tidak_lanjut_so')->name('tidak_lanjut_so');
        Route::post('/tutup_so', 'SalesOrderController@tutup_so')->name('tutup_so');
        Route::post('/destroy', 'SalesOrderController@destroy')->name('destroy');

        Route::get('/{id}/edit_item', 'SalesOrderController@edit_item')->name('edit_item');
        Route::post('/store_item', 'SalesOrderController@store_item')->name('store_item');
        Route::post('/update_item', 'SalesOrderController@update_item')->name('update_item');
        Route::post('/destroy_item', 'SalesOrderController@destroy_item')->name('destroy_item');

        Route::get('/get_product', 'SalesOrderController@get_product')->name('get_product');
        Route::get('/get_category', 'SalesOrderController@get_category')->name('get_category');
        Route::post('/getmember', 'SalesOrderController@getmember')->name('getmember');

        Route::post('/ajax_customer_detail', 'SalesOrderController@ajax_customer_detail')->name('ajax_customer_detail');
        Route::post('/ajax_warehouse_detail', 'SalesOrderController@ajax_warehouse_detail')->name('ajax_warehouse_detail');
        
        Route::get('/{id}/print_rejected_so', 'SalesOrderController@print_rejected_so')->name('print_rejected_so');
    });

    Route::group(['as' => 'packing_order.', 'prefix' => '/packing_order'], function () {
        Route::get('/', 'PackingOrderController@index')->name('index');
        Route::get('/create', 'PackingOrderController@create')->name('create');
        Route::get('/{id}/edit', 'PackingOrderController@edit')->name('edit');
        Route::get('/{id}/detail', 'PackingOrderController@detail')->name('detail');
        Route::post('/store', 'PackingOrderController@store')->name('store');
        Route::post('/update', 'PackingOrderController@update')->name('update');
        Route::post('/update_new', 'PackingOrderController@update_new')->name('update_new');
        Route::post('/destroy', 'PackingOrderController@destroy')->name('destroy');
        Route::post('/prepare', 'PackingOrderController@prepare')->name('prepare');
        Route::post('/order', 'PackingOrderController@order')->name('order');
        Route::post('/ready', 'PackingOrderController@ready')->name('ready');
        Route::post('/packed', 'PackingOrderController@packed')->name('packed');

        Route::get('/{id}/select_so', 'PackingOrderController@select_so')->name('select_so');
        Route::post('/store_so', 'PackingOrderController@store_so')->name('store_so');

        Route::post('/destroy_item', 'PackingOrderController@destroy_item')->name('destroy_item');


        Route::post('/update_cost', 'PackingOrderController@update_cost')->name('update_cost');

        Route::post('/ajax_customer_detail', 'PackingOrderController@ajax_customer_detail')->name('ajax_customer_detail');
        Route::post('/ajax_customer_other_address', 'PackingOrderController@ajax_customer_other_address')->name('ajax_customer_other_address');
        Route::post('/ajax_customer_other_address_detail', 'PackingOrderController@ajax_customer_other_address_detail')->name('ajax_customer_other_address_detail');

        Route::get('/{id}/print_proforma', 'PackingOrderController@print_proforma')->name('print_proforma');
    });

    Route::group(['as' => 'delivery_order.', 'prefix' => '/delivery_order'], function () {
        Route::get('/', 'DeliveryOrderController@index')->name('index');
        Route::get('/{id}/print', 'DeliveryOrderController@print')->name('print');
        Route::get('/{id}/detail', 'DeliveryOrderController@detail')->name('detail');

        Route::post('/get_cost', 'DeliveryOrderController@get_cost')->name('get_cost');
        Route::post('/packed', 'DeliveryOrderController@packed')->name('packed');
        Route::post('/sending', 'DeliveryOrderController@sending')->name('sending');
        Route::post('/sent', 'DeliveryOrderController@sent')->name('sent');
        Route::post('/upload_image', 'DeliveryOrderController@upload_image')->name('upload_image');

        Route::get('/{id}/print_proforma', 'DeliveryOrderController@print_proforma')->name('print_proforma');
        Route::get('/{id}/print_manifest', 'DeliveryOrderController@print_manifest')->name('print_manifest');
    });

    Route::group(['as' => 'delivery_order_mutation.', 'prefix' => '/delivery_order_mutation'], function () {
        Route::get('/', 'DeliveryOrderMutationController@index')->name('index');
        Route::get('/create', 'DeliveryOrderMutationController@create')->name('create');
        Route::get('/{id}/detail', 'DeliveryOrderMutationController@detail')->name('detail');
        Route::get('/{id}/edit', 'DeliveryOrderMutationController@edit')->name('edit');
        Route::get('/{id}/print', 'DeliveryOrderMutationController@print')->name('print');

        Route::post('/store', 'DeliveryOrderMutationController@store')->name('store');

        Route::get('/{id}/select_so', 'DeliveryOrderMutationController@select_so')->name('select_so');
        Route::post('/store_so', 'DeliveryOrderMutationController@store_so')->name('store_so');

        Route::post('/destroy', 'DeliveryOrderMutationController@destroy')->name('destroy');
        Route::post('/destroy_item', 'DeliveryOrderMutationController@destroy_item')->name('destroy_item');
        Route::post('/sent', 'DeliveryOrderMutationController@sent')->name('sent');
    });

    Route::group(['as' => 'canvasing.', 'prefix' => '/canvasing'], function () {

        Route::get('/', 'CanvasingController@index')->name('index');
        Route::get('/create', 'CanvasingController@create')->name('create');
        Route::get('/{id}/edit', 'CanvasingController@edit')->name('edit');
        Route::get('/{id}/detail', 'CanvasingController@detail')->name('detail');
        Route::get('/{id}/print', 'CanvasingController@print')->name('print');
        Route::post('/store', 'CanvasingController@store')->name('store');
        Route::post('/destroy', 'CanvasingController@destroy')->name('destroy');
        Route::post('/sent', 'CanvasingController@sent')->name('sent');

        Route::get('/{id}/edit_item', 'CanvasingController@edit_item')->name('edit_item');
        Route::post('/store_item', 'CanvasingController@store_item')->name('store_item');
        Route::post('/update_item', 'CanvasingController@update_item')->name('update_item');
        Route::post('/destroy_item', 'CanvasingController@destroy_item')->name('destroy_item');
    });
});