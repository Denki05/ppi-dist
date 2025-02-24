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
        Route::get('/export', 'ProductFinanceController@export')->name('export');
        Route::get('/get_product', 'ProductFinanceController@get_product')->name('get_product');
        Route::post('/updatePrice', 'ProductFinanceController@updatePrice')->name('updatePrice');
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
        Route::get('/get-last-code', 'InvoiceTaxController@getLastCode')->name('getLastCode');
        Route::get('/pageReportBeli', 'InvoiceTaxController@pageReportBeli')->name('pageReportBeli');
        Route::get('/pageReportJual', 'InvoiceTaxController@pageReportJual')->name('pageReportJual');
        Route::get('/json', 'InvoiceTaxController@json')->name('json');
        Route::get('/json2', 'InvoiceTaxController@json2')->name('json2');
        Route::get('/json3', 'InvoiceTaxController@json3')->name('json3');
        Route::get('/json4', 'InvoiceTaxController@json4')->name('json4');
        Route::delete('/destroy_beli/{id}', 'InvoiceTaxController@destroy_beli')->name('destroy_beli');
        Route::delete('/destroy_jual/{id}', 'InvoiceTaxController@destroy_jual')->name('destroy_jual');
    });
    Route::resource('invoice_tax', 'InvoiceTaxController');

    Route::group(['as' => 'finance_simulation.', 'prefix' => '/finance_simulation'], function () {
        Route::get('/index', 'FinanceSimulationPriceController@index')->name('index');
        Route::get('/getInvoice', 'FinanceSimulationPriceController@getInvoice')->name('getInvoice');
        Route::get('/create', 'FinanceSimulationPriceController@create')->name('create');
        Route::post('/store', 'FinanceSimulationPriceController@store')->name('store');
        Route::get('/removeData', 'FinanceSimulationPriceController@removeData')->name('removeData');
        Route::get('/page_report', 'FinanceSimulationPriceController@page_report')->name('page_report');
    });
    Route::resource('finance_simulation', 'FinanceSimulationPriceController'); 
});