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
        Route::get('/import_template', 'SettingPriceController@import_template')->name('import_template');
        Route::post('/import', 'SettingPriceController@import')->name('import');
        Route::get('/sync_price', 'SettingPriceController@sync_price')->name('sync_price');
    });

    Route::group(['as' => 'sales_order.', 'prfix' => '/sales_order'], function () {
        // Route::get('/index', 'SalesOrderController@index')->name('index');
        Route::get('/so_awal', 'SalesOrderController@index_awal')->name('index_awal');
        Route::get('/so_lanjutan', 'SalesOrderController@index_lanjutan')->name('index_lanjutan');
        Route::get('/so_mutasi', 'SalesOrderController@index_mutasi')->name('index_mutasi');
        Route::get('/create/{step}/{member}/{brand}/{type}/{indent}/{approval}/{note}/{kurs}/{disc_percent}', 'SalesOrderController@create')->name('create');
        Route::get('/{id}/edit/{step}', 'SalesOrderController@edit')->name('edit');
        Route::get('/{id}/detail', 'SalesOrderController@detail')->name('detail');
        Route::post('/{member}/store', 'SalesOrderController@store')->name('store');
        Route::post('/update', 'SalesOrderController@update')->name('update');
        Route::get('/lanjutkan/{id}', 'SalesOrderController@lanjutkan')->name('lanjutkan');
        Route::get('/{id}/kembali', 'SalesOrderController@kembali')->name('kembali');
        Route::post('/tidak_lanjut_so', 'SalesOrderController@tidak_lanjut_so')->name('tidak_lanjut_so');
        Route::post('/tutup_so', 'SalesOrderController@tutup_so')->name('tutup_so');
        Route::get('/destroy/{id}', 'SalesOrderController@destroy')->name('destroy');
        Route::get('/{id}/print_proforma', 'SalesOrderController@print_proforma')->name('print_proforma');
        Route::get('/destroy_lanjutan/{id}', 'SalesOrderController@destroy_lanjutan')->name('destroy_lanjutan');
        Route::get('/indent/{id}', 'SalesorderController@indent')->name('indent');
        Route::post('/kembali_hold/{id}', 'SalesOrderController@kembali_hold')->name('kembali_hold');

        Route::get('/{id}/edit_item', 'SalesOrderController@edit_item')->name('edit_item');
        Route::post('/store_item', 'SalesOrderController@store_item')->name('store_item');
        Route::post('/update_item', 'SalesOrderController@update_item')->name('update_item');
        Route::post('/destroy_item', 'SalesOrderController@destroy_item')->name('destroy_item');
        Route::post('/ajax_customer_detail', 'SalesOrderController@ajax_customer_detail')->name('ajax_customer_detail');
        Route::post('/ajax_warehouse_detail', 'SalesOrderController@ajax_warehouse_detail')->name('ajax_warehouse_detail');
        Route::post('/ajax_product_detail', 'SalesOrderController@ajax_product_detail')->name('ajax_product_detail');
        Route::get('/{id}/print_rejected_so', 'SalesOrderController@print_rejected_so')->name('print_rejected_so');
        Route::get('/{so_id}/print_so', 'SalesOrderController@print_so')->name('print_so');

        Route::get('/get_category', 'SalesOrderController@get_category')->name('get_category');
        Route::get('/get_product', 'SalesOrderController@get_product')->name('get_product');
        Route::get('/get_packaging', 'SalesOrderController@get_packaging')->name('get_packaging');
        Route::get('/get_brand', 'SalesOrderController@get_brand')->name('get_brand');
        Route::post('/get_product_pack', 'SalesOrderController@get_product_pack')->name('get_product_pack');
        Route::get('/updateBrandName', 'SalesOrderController@updateBrandName')->name('updateBrandName');
        Route::get('/export', 'SalesOrderController@export')->name('export');
        Route::get('/search_kontrak/{id}/{merek}', 'SalesOrderController@search_kontrak')->name('search_kontrak');
        Route::post('/get_product_kontrak', 'SalesOrderController@get_product_kontrak')->name('get_product_kontrak');
        Route::get('/json_awal', 'SalesOrderController@json_awal')->name('json_awal');
        Route::get('/json_lanjutan', 'SalesOrderController@json_lanjutan')->name('json_lanjutan');
        Route::get('/data_so/{id}', 'SalesOrderController@data_so')->name('data_so');
        Route::post('/approvalMouSo/{id}', 'SalesOrderController@approvalMouSo')->name('approvalMouSo');
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
        Route::get('/ready/{id}', 'PackingOrderController@ready')->name('ready');
        Route::post('/packed', 'PackingOrderController@packed')->name('packed');
        Route::get('/revisi/{id}', 'PackingOrderController@revisi')->name('revisi');

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
        Route::get('/{id}/print_label', 'DeliveryOrderController@print_label')->name('print_label');
        Route::get('/print_label_pengirim', 'DeliveryOrderController@print_label_pengirim')->name('print_label_pengirim');
        Route::post('/cancel_proses', 'DeliveryOrderController@cancel_proses')->name('cancel_proses');
        Route::post('/do_edit', 'DeliveryOrderController@do_edit')->name('do_edit');
        Route::post('/do_update', 'DeliveryOrderController@do_update')->name('do_update');
        Route::get('/json', 'DeliveryOrderController@json')->name('json');
        Route::post('/unread_notif/{id}/{do}', 'DeliveryOrderController@unread_notif')->name('unread_notif');
        Route::get('/getNotifData', 'DeliveryOrderController@getNotifData')->name('getNotifData');
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

    Route::group(['as' => 'sales_order_ppn.', 'prefix' => '/sales_order_ppn'], function () {
        Route::get('/index_ppn_awal', 'SalesOrderPpnController@index_ppn_awal')->name('index_ppn_awal');
        Route::get('/index_ppn_lanjutan', 'SalesOrderPpnController@index_ppn_lanjutan')->name('index_ppn_lanjutan');
        Route::get('/create', 'SalesOrderPpnController@create')->name('create');
        Route::post('/store', 'SalesOrderPpnController@store')->name('store');
        Route::get('/{id}/detail', 'SalesOrderPpnController@detail')->name('detail');
        Route::post('/{id}/update_awal_ppn', 'SalesOrderPpnController@update_awal_ppn')->name('update_awal_ppn');
        Route::get('/{id}/lanjutkan', 'SalesOrderPpnController@lanjutkan')->name('lanjutkan');
        Route::get('/{id}/{step}/edit_ppn', 'SalesOrderPpnController@edit_ppn')->name('edit_ppn');
        Route::post('/tutup_so_ppn', 'SalesOrderPpnController@tutup_so_ppn')->name('tutup_so_ppn');
        Route::post('/destroy_ppn', 'SalesOrderPpnController@destroy_ppn')->name('destroy_ppn');
        Route::get('/get_brand', 'SalesOrderPpnController@get_brand')->name('get_brand');
        Route::post('/get_product_pack', 'SalesOrderPpnController@get_product_pack')->name('get_product_pack');
        Route::post('/ajax_customer_detail', 'SalesOrderPpnController@ajax_customer_detail')->name('ajax_customer_detail');
        Route::get('/destroy_lanjutan/{id}', 'SalesOrderPpnController@destroy_lanjutan')->name('destroy_lanjutan');
        Route::get('/cancel_approve/{id}', 'SalesOrderPpnController@cancel_approve')->name('cancel_approve');
    });
    Route::resource('sales_order_ppn', 'SalesOrderPpnController');

    Route::group(['as' => 'sales_order_indent.', 'prefix' => '/sales_order_indent'], function () {
        Route::get('/', 'SalesOrderIndentController@index')->name('index');
        Route::get('/export', 'SalesOrderIndentController@export')->name('export');
        Route::get('/destroy/{id}', 'SalesOrderIndentController@destroy')->name('destroy');
        Route::post('/proses_ready', 'SalesOrderIndentController@proses_ready')->name('proses_ready');
        Route::post('/deleteItems', 'SalesOrderIndentController@deleteItems')->name('deleteItems');
        Route::get('/print_out_indent/{so_id}', 'SalesOrderIndentController@print_out_indent')->name('print_out_indent');
    });
    Route::resource('sales_order_indent', 'SalesOrderIndentController');

    Route::group(['as' => 'sales_order_kontrak.', 'prefix' => '/sales_order_kontrak'], function () {
        Route::get('/index', 'SalesOrderKontrakController@index')->name('index');
        Route::get('/get_brand', 'SalesOrderKontrakController@get_brand')->name('get_brand');
        Route::post('/get_product', 'SalesOrderKontrakController@get_product')->name('get_product');
        Route::post('/get_packaging', 'SalesOrderKontrakController@get_packaging')->name('get_packaging');
        Route::post('/get_packaging_edit', 'SalesOrderKontrakController@get_packaging_edit')->name('get_packaging_edit');
        Route::post('/store', 'SalesOrderKontrakController@store')->name('store');
        Route::patch('/update/{id}', 'SalesOrderKontrakController@update')->name('update');
        Route::get('/acc/{id}', 'SalesOrderKontrakController@acc')->name('acc');
        Route::get('/complete/{id}', 'SalesOrderKontrakController@complete')->name('complete');
        Route::get('/destroy/{id}', 'SalesOrderKontrakController@destroy')->name('destroy');
        Route::get('/revisi/{id}', 'SalesOrderKontrakController@revisi')->name('revisi');
        Route::post('/update_revisi/{id}', 'SalesOrderKontrakController@update_revisi')->name('update_revisi');
        Route::get('/cancel_aprove/{id}', 'SalesOrderKontrakController@cancel_aprove')->name('cancel_aprove');
        Route::get('/print_log', 'SalesOrderKontrakController@print_log')->name('print_log');
        Route::get('/update_pivot', 'SalesOrderKontrakController@update_pivot')->name('update_pivot');
        Route::get('/update_log', 'SalesOrderKontrakController@update_log')->name('update_log');
    });
    Route::resource('sales_order_kontrak', 'SalesOrderKontrakController');

    Route::group(['as' => 'notification.', 'prefix' => '/notification'], function () {
        Route::get('/getNotifData', 'NotificationController@getNotifData')->name('getNotifData');
        
        // Corrected routes to use POST method
        Route::post('/mark_as_read_do/{id}/{do}', 'NotificationController@unread_notif_do')->name('mark_as_read_do');
        Route::post('/mark_as_read_so/{id}/{do}', 'NotificationController@unread_notif_so')->name('mark_as_read_so');
        Route::post('/mark_as_read_payable/{id}', 'NotificationController@unread_notif_payable')->name('mark_as_read_payable');
        Route::post('/mark_as_read_only/{id}', 'NotificationController@mark_as_read_only')->name('mark_as_read_only');
        Route::post('/unread_all_notif', 'NotificationController@unread_all_notif')->name('unread_all_notif');
    });
    Route::resource('notification', 'NotificationController');

    Route::group(['as' => 'so_proforma.', 'prefix' => '/so_proforma'], function () {
        Route::post('/store', 'SalesOrderProformaController@store')->name('store');
        Route::get('/show/{id}', 'SalesOrderProformaController@show')->name('show');
        Route::get('/search_sku', 'SalesOrderProformaController@search_sku')->name('search_sku');
        Route::put('/update/{id}', 'SalesOrderProformaController@update')->name('update');
        Route::get('/print_so_proforma/{id}', 'SalesOrderProformaController@print_so_proforma')->name('print_so_proforma');
        Route::get('/acc/{id}', 'SalesOrderProformaController@acc')->name('acc');
        Route::get('/approval_so/{id}', 'SalesOrderProformaController@approval_so')->name('approval_so');
        Route::get('/destroy/{id}', 'SalesOrderProformaController@destroy')->name('destroy');
        Route::get('/getCustomer', 'SalesOrderProformaController@getCustomer')->name('getCustomer');
     });
     Route::resource('so_proforma', 'SalesOrderProformaController');
});