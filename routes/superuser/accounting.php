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
        Route::post('/update_cost/{product_finance}', 'ProductFinanceController@update_cost')->name('update_cost');
        Route::get('/search_mitra', 'ProductFinanceController@search_mitra')->name('search_mitra');
        Route::get('/show/{mitra_id}', 'ProductFinanceController@show')->name('show');
        Route::get('get_product', 'ProductFinanceController@get_product')->name('get_product');
        
    });
    Route::resource('product_finance', 'ProductFinanceController');

    Route::group(['as' => 'invoice_tax.', 'prefix' => '/invoice_tax'], function () {
        Route::get('/search_invreal', 'InvoiceTaxController@search_invreal')->name('search_invreal');
        Route::post('/get_product', 'InvoiceTaxController@get_product')->name('get_product');
    });
    Route::resource('invoice_tax', 'InvoiceTaxController');
});