<?php

Route::group([
    'middleware' => ['auth:superuser'],
    'as' => 'report.',
    'prefix' => '/report',
    'namespace' => 'Report'
], function () {

    Route::group(['as' => 'sales.', 'prefix' => '/sales'], function () {
        Route::get('/', 'ReportSalesController@index')->name('index');
        Route::get('/print', 'ReportSalesController@print')->name('print');
    });

    Route::group(['as' => 'revenue.', 'prefix' => '/revenue'], function () {
        Route::get('/', 'ReportRevenueController@index')->name('index');
        Route::get('/print', 'ReportRevenueController@print')->name('print');
    });

    Route::group(['as' => 'product_performance.', 'prefix' => '/product_performance'], function () {
        Route::get('/', 'ReportProductPerformanceController@index')->name('index');
        Route::get('/print', 'ReportProductPerformanceController@print')->name('print');
    });

});