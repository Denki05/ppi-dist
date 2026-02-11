<?php

Route::group([
    'middleware' => ['auth:superuser'],
    'as' => 'gudang.',
    'prefix' => '/gudang',
    'namespace' => 'Gudang'
], function () {
    Route::group(['as' => 'stock.', 'prefix' => '/stock'], function () {
        Route::get('/json', 'StockController@json')->name('json');
        Route::get('{warehouse_id}/detail/{product_id}', 'StockController@detail')->name('detail');
        Route::get('{warehouse}/{startDate}/{endDate}/exportTransactions', 'StockController@exportTransactions')->name('exportTransactions');
        Route::get('/backfillMonthEndBalances', 'StockController@backfillMonthEndBalances')->name('backfillMonthEndBalances');
    });
    Route::resource('stock', 'StockController');

    Route::group(['as' => 'stock_adjustment.', 'prefix' => '/stock_adjustment'], function () {
        Route::get('/', 'StockAdjustmentController@index')->name('index');
        Route::get('/json', 'StockAdjustmentController@json')->name('json');
        Route::get('/create', 'StockAdjustmentController@create')->name('create');
        Route::post('/check_product_warehouse', 'StockAdjustmentController@check_product_warehouse')->name('check_product_warehouse');
        Route::post('/store', 'StockAdjustmentController@store')->name('store');
        
    });

    Route::group(['as' => 'purchase_order.', 'prefix' => '/purchase_order'], function () {
        Route::get('/json', 'PurchaseOrderController@json')->name('json');
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
        Route::get('/export', 'PurchaseOrderController@export')->name('export');
        Route::get('/{id}/send', 'PurchaseOrderController@send')->name('send');
        Route::get('/summary', 'PurchaseOrderController@summary')->name('summary');
        Route::get('/{id}/cancel_send', 'PurchaseOrderController@cancel_send')->name('cancel_send');
        Route::get('/{id}/send_spk', 'PurchaseOrderController@send_spk')->name('send_spk');

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
        Route::get('/json', 'PurchaseOrderSPKController@json')->name('json');
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

    Route::group(['as' => 'receiving.', 'perfix' => '/receiving'], function (){
        Route::get('/json', 'ReceivingController@json')->name('json');
        Route::get('/step/{id}', 'ReceivingController@step')->name('step');
        Route::get('{id}/publish', 'ReceivingController@publish')->name('publish');
        Route::get('{id}/acc', 'ReceivingController@acc')->name('acc');
        Route::get('/cancel_approve/{id}', 'ReceivingController@cancel_approve')->name('cancel_approve');
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
});