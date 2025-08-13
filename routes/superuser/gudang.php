<?php

Route::group([
    'middleware' => ['auth:superuser'],
    'as' => 'gudang.',
    'prefix' => '/gudang',
    'namespace' => 'Gudang'
], function () {
    Route::group(['as' => 'stock.', 'prefix' => '/stock'], function () {
        Route::get('/', 'StockController@index')->name('index');
        Route::get('/json', 'StockController@json')->name('json');
        Route::get('{warehouse_id}/detail/{product_id}', 'StockController@detail')->name('detail');
        Route::get('{warehouse}/{startDate}/{endDate}/exportTransactions', 'StockController@exportTransactions')->name('exportTransactions');
        Route::get('/backfillMonthEndBalances', 'StockController@backfillMonthEndBalances')->name('backfillMonthEndBalances');
        Route::get('/export_stock_db', 'StockController@export_stock_db')->name('export_stock_db');
        Route::get('/import_template', 'StockController@import_template')->name('import_template');
        Route::post('/import', 'StockController@import')->name('import');
    });

    Route::group(['as' => 'stock_adjustment.', 'prefix' => '/stock_adjustment'], function () {
        Route::get('/', 'StockAdjustmentController@index')->name('index');
        Route::get('/create', 'StockAdjustmentController@create')->name('create');
        Route::post('/check_product_warehouse', 'StockAdjustmentController@check_product_warehouse')->name('check_product_warehouse');
        Route::post('/store', 'StockAdjustmentController@store')->name('store');
    });

    // SIRIE
    Route::group(['as' => 'purchase_order.', 'prefix' => '/purchase_order'], function () {
        Route::get('/step/{id}', 'PurchaseOrderController@step')->name('step');
        Route::get('{id}/publish', 'PurchaseOrderController@publish')->name('publish');
        Route::get('{id}/unpublish', 'PurchaseOrderController@unpublish')->name('unpublish');
        Route::get('{id}/save_modify/{save_type}', 'PurchaseOrderController@save_modify')->name('save_modify');
        Route::get('{id}/acc', 'PurchaseOrderController@acc')->name('acc');
        Route::get('{id}/print_pdf', 'PurchaseOrderController@print_pdf')->name('print_pdf');
        Route::get('/import_template', 'PurchaseOrderController@import_template')->name('import_template');
        Route::post('/import/{id}', 'PurchaseOrderController@import')->name('import');
        Route::get('/search_sku', 'PurchaseOrderController@search_sku')->name('search_sku');
        Route::get('/search_kemasan', 'PurchaseOrderController@search_kemasan')->name('search_kemasan');
        Route::get('/{id}/cancel_acc', 'PurchaseOrderController@cancel_acc')->name('cancel_acc');
        Route::get('/{id}/send', 'PurchaseOrderController@send')->name('send');
        Route::get('/summary', 'PurchaseOrderController@summary')->name('summary');
        Route::get('/{id}/cancel_send', 'PurchaseOrderController@cancel_send')->name('cancel_send');

        Route::group(['as' => 'detail.'], function () {
            Route::get('{purchase_id}/detail/create', 'PurchaseOrderDetailController@create')->name('create');
            Route::post('{purchase_id}/detail', 'PurchaseOrderDetailController@store')->name('store');
            Route::get('{id}/detail/{detail_id}/edit', 'PurchaseOrderDetailController@edit')->name('edit');
            Route::put('{id}/detail/{detail_id}', 'PurchaseOrderDetailController@update')->name('update');
            Route::delete('{id}/detail/{detail_id}', 'PurchaseOrderDetailController@destroy')->name('destroy');
            Route::get('/get_product', 'PurchaseOrderDetailController@get_product')->name('get_product');
            Route::get('/get_packaging', 'PurchaseOrderDetailController@get_packaging')->name('get_packaging');
        });
    });
    Route::resource('purchase_order', 'PurchaseOrderController');

    // SPK
    Route::group(['as' => 'purchase_order_spk.', 'prefix' => '/purchase_order_spk'], function () {
        Route::get('/step/{id}', 'PurchaseOrderSPKController@step')->name('step');
        Route::get('{id}/publish', 'PurchaseOrderSPKController@publish')->name('publish');
        Route::get('{id}/unpublish', 'PurchaseOrderSPKController@unpublish')->name('unpublish');
        Route::get('{id}/save_modify/{save_type}', 'PurchaseOrderSPKController@save_modify')->name('save_modify');
        Route::get('{id}/acc', 'PurchaseOrderSPKController@acc')->name('acc');
        Route::get('{id}/print_pdf', 'PurchaseOrderSPKController@print_pdf')->name('print_pdf');
        Route::get('/import_template', 'PurchaseOrderSPKController@import_template')->name('import_template');
        Route::post('/import/{id}', 'PurchaseOrderSPKController@import')->name('import');
        Route::get('/search_sku', 'PurchaseOrderSPKController@search_sku')->name('search_sku');
        Route::get('/search_kemasan', 'PurchaseOrderSPKController@search_kemasan')->name('search_kemasan');
        Route::get('/{id}/cancel_acc', 'PurchaseOrderSPKController@cancel_acc')->name('cancel_acc');
        Route::get('/{id}/send', 'PurchaseOrderSPKController@send')->name('send');
        Route::get('/summary', 'PurchaseOrderSPKController@summary')->name('summary');
        Route::get('/{id}/cancel_send', 'PurchaseOrderSPKController@cancel_send')->name('cancel_send');

        Route::group(['as' => 'detail.'], function () {
            Route::get('{purchase_id}/detail/create', 'PurchaseOrderDetailSPKController@create')->name('create');
            Route::post('{purchase_id}/detail', 'PurchaseOrderDetailSPKController@store')->name('store');
            Route::get('{id}/detail/{detail_id}/edit', 'PurchaseOrderDetailSPKController@edit')->name('edit');
            Route::put('{id}/detail/{detail_id}', 'PurchaseOrderDetailSPKController@update')->name('update');
            Route::delete('{id}/detail/{detail_id}', 'PurchaseOrderDetailSPKController@destroy')->name('destroy');
            Route::get('/get_product', 'PurchaseOrderDetailSPKController@get_product')->name('get_product');
            Route::get('/get_packaging', 'PurchaseOrderDetailSPKController@get_packaging')->name('get_packaging');
        });
    });
    Route::resource('purchase_order_spk', 'PurchaseOrderSPKController');

    // RECEIVING IN SIRIE
    Route::group(['as' => 'receiving.', 'perfix' => '/receiving'], function (){
        Route::get('/step/{id}', 'ReceivingController@step')->name('step');
        Route::get('{id}/publish', 'ReceivingController@publish')->name('publish');
        Route::get('{id}/acc_ri', 'ReceivingController@acc_ri')->name('acc_ri');
        Route::get('/cancel/{id}', 'ReceivingController@cancel')->name('cancel');
        Route::get('/import_template', 'ReceivingController@import_template')->name('import_template');
        Route::post('/import/{id}', 'ReceivingController@import')->name('import');

        Route::group(['as' => 'detail.'], function () {
            Route::get('{id}/detail/{detail_id}/colly', 'ReceivingDetailController@show')->name('show');
            Route::get('{id}/detail/create', 'ReceivingDetailController@create')->name('create');
            Route::post('{id}/detail', 'ReceivingDetailController@store')->name('store');
            Route::delete('{id}/detail/{detail_id}/delete', 'ReceivingDetailController@destroy')->name('destroy');

            Route::get('{id}/detail/{detail_id}/edit', 'ReceivingDetailController@edit')->name('edit');
            Route::put('{id}/detail/{detail_id}', 'ReceivingDetailController@update')->name('update');

            Route::post('detail/get_sku_json', 'ReceivingDetailController@get_sku_json')->name('get_sku_json');

            Route::post('detail/{detail}/qc', 'ReceivingDetailController@storeQc')->name('qty_qc');
            Route::get('detail/qc/{id}/approve', 'ReceivingDetailController@approveQc')->name('approveQc');
            Route::get('detail/qc/{id}/destroy', 'ReceivingDetailController@destroyQc')->name('destroyQc');

            Route::group(['as' => 'colly.'], function () {
                Route::get('{id}/colly/{detail_id}/create', 'ReceivingDetailCollyController@create')->name('create');
                Route::post('{id}/{detail_id}/colly', 'ReceivingDetailCollyController@store')->name('store');
                Route::get('{id}/detail/{detail_id}/colly/{colly_id}/edit', 'ReceivingDetailCollyController@edit')->name('edit');
                Route::put('{id}/detail/{detail_id}/colly/{colly_id}', 'ReceivingDetailCollyController@update')->name('update');
                Route::delete('{id}/detail/{detail_id}/colly/{colly_id}/delete', 'ReceivingDetailCollyController@destroy')->name('destroy');
            });

        });
    });
    Route::resource('receiving', 'ReceivingController');

    // Quality Control
    Route::group(['as' => 'quality_control.', 'perfix' => '/quality_control'], function (){
        Route::get('/step/{id}', 'QualityControlController@step')->name('step');
        Route::get('{id}/publish', 'QualityControlController@publish')->name('publish');
        Route::get('{id}/acc_ri', 'QualityControlController@acc_ri')->name('acc_ri');
        Route::get('/cancel/{id}', 'QualityControlController@cancel')->name('cancel');
        Route::get('/import_template', 'QualityControlController@import_template')->name('import_template');
        Route::post('/import/{id}', 'QualityControlController@import')->name('import');

        Route::group(['as' => 'detail.'], function () {
            Route::get('{id}/detail/{detail_id}/colly', 'QualityControlDetailController@show')->name('show');
            Route::get('{id}/detail/create', 'QualityControlDetailController @create')->name('create');
            Route::post('{id}/detail', 'QualityControlDetailController @store')->name('store');
            Route::delete('{id}/detail/{detail_id}/delete', 'QualityControlDetailController@destroy')->name('destroy');

            Route::get('{id}/detail/{detail_id}/edit', 'QualityControlDetailController@edit')->name('edit');
            Route::put('{id}/detail/{detail_id}', 'QualityControlDetailController @update')->name('update');

            Route::post('detail/get_sku_json', 'QualityControlDetailController@get_sku_json')->name('get_sku_json');

            Route::post('detail/{detail}/qc', 'QualityControlDetailController@storeQc')->name('qty_qc');
            Route::get('detail/qc/{id}/approve', 'QualityControlDetailController @approveQc')->name('approveQc');
            Route::get('detail/qc/{id}/destroy', 'QualityControlDetailController@destroyQc')->name('destroyQc');

            Route::group(['as' => 'colly.'], function () {
                Route::get('{id}/colly/{detail_id}/create', 'ReceivingDetailCollyController@create')->name('create');
                Route::post('{id}/{detail_id}/colly', 'ReceivingDetailCollyController@store')->name('store');
                Route::get('{id}/detail/{detail_id}/colly/{colly_id}/edit', 'ReceivingDetailCollyController@edit')->name('edit');
                Route::put('{id}/detail/{detail_id}/colly/{colly_id}', 'ReceivingDetailCollyController@update')->name('update');
                Route::delete('{id}/detail/{detail_id}/colly/{colly_id}/delete', 'ReceivingDetailCollyController@destroy')->name('destroy');
            });

        });
    });
    Route::resource('quality_control', 'QualityControlController');

    Route::group(['as' => 'mutasi_out.', 'perfix' => '/mutasi_out'], function (){
        Route::get('/search_sku', 'MutasiOutController@search_sku')->name('search_sku');
        Route::get('{id}/acc', 'MutasiOutController@acc')->name('acc');
    });
    Route::resource('mutasi_out', 'MutasiOutController');
});