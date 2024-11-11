<?php

Route::group([
    'middleware' => ['auth:superuser'],
    'as' => 'accounting.',
    'prefix' => '/accounting',
    'namespace' => 'Accounting'
], function () {

    Route::group(['as' => 'product_finance.', 'prefix' => '/product_finance'], function () {
        Route::get('/import_template', 'ProductFinanceController@import_template')->name('import_template');
        Route::post('/import', 'ProductFinanceController@import')->name('import');
        Route::post('/export', 'ProductFinanceController@export')->name('export');
        Route::post('/update_cost/{product_finance}', 'ProductFinanceController@update_cost')->name('update_cost');
        Route::get('/search_mitra', 'ProductFinanceController@search_mitra')->name('search_mitra');
        Route::get('/show/{mitra_id}', 'ProductFinanceController@show')->name('show');
        Route::get('get_product', 'ProductFinanceController@get_product')->name('get_product');
        
    });
    Route::resource('product_finance', 'ProductFinanceController');

    Route::group(['as' => 'invoice_tax.', 'prefix' => '/invoice_tax'], function () {
        Route::get('/index_jual', 'InvoiceTaxController@index_jual')->name('index_jual');
        Route::get('/index_beli', 'InvoiceTaxController@index_beli')->name('index_beli');
        Route::get('create', 'InvoiceTaxController@create')->name('create');
        Route::get('/search_invreal_jual', 'InvoiceTaxController@search_invreal_jual')->name('search_invreal_jual');
        Route::get('/search_invreal_beli', 'InvoiceTaxController@search_invreal_beli')->name('search_invreal_beli');
        Route::post('/get_product', 'InvoiceTaxController@get_product')->name('get_product');
        Route::post('/store', 'InvoiceTaxController@store')->name('store');
        Route::get('/print_invoice/{id}', 'InvoiceTaxController@print_invoice')->name('print_invoice');
        Route::post('/check_type', 'InvoiceTaxController@check_type')->name('check_type');
    });
    Route::resource('invoice_tax', 'InvoiceTaxController');
});