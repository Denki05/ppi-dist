<?php

Route::group([
    'middleware' => ['auth:superuser'],
    'as' => 'finance.',
    'prefix' => '/finance',
    'namespace' => 'Finance'
], function () {

    Route::group(['as' => 'invoicing.', 'prefix' => '/invoicing'], function () {
        Route::get('/', 'InvoicingController@index')->name('index');
        Route::get('/create', 'InvoicingController@create')->name('create');
        Route::get('/{code_do}/detail', 'InvoicingController@detail')->name('detail');
        Route::post('/update_other_cost', 'InvoicingController@update_other_cost')->name('update_other_cost');
        Route::post('/update_pemesan', 'InvoicingController@update_pemesan')->name('update_pemesan');
        Route::post('/update_cost', 'InvoicingController@update_cost')->name('update_cost');
        Route::post('/store_invoicing', 'InvoicingController@store_invoicing')->name('store_invoicing');
        Route::get('/{id}/print', 'InvoicingController@print')->name('print');
        Route::get('/{id}/print_portait', 'InvoicingController@print_portait')->name('print_portait');
        Route::get('/{id}/print_paid', 'InvoicingController@print_paid')->name('print_paid');
        Route::get('/{id}/print_proforma', 'InvoicingController@print_proforma')->name('print_proforma');
        Route::get('/{id}/history_payable', 'InvoicingController@history_payable')->name('history_payable');
        Route::get('/json', 'InvoicingController@json')->name('json');
    });

    Route::group(['as' => 'proforma.', 'prefix' => '/proforma'], function () {
        Route::get('/', 'ProformaController@index')->name('index');
        Route::post('/cancel', 'ProformaController@cancel')->name('cancel');
        Route::get('/{id}/print_proforma', 'ProformaController@print_proforma')->name('print_proforma');
        
    });

    Route::group(['as' => 'payable.', 'prefix' => '/payable'], function () {
        Route::get('/', 'PayableController@index')->name('index');
        Route::get('/create', 'PayableController@create')->name('create');
        Route::get('/{id}/detail', 'PayableController@detail')->name('detail');
        Route::post('/store', 'PayableController@store')->name('store');
        Route::get('/{id}/print', 'PayableController@print')->name('print');
    });
    Route::resource('payable', 'PayableController');
});