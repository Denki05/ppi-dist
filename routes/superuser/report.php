<?php

Route::group([
    'middleware' => ['auth:superuser'],
    'as' => 'report.',
    'prefix' => '/report',
    'namespace' => 'Report'
], function () {

    Route::group(['as' => 'sales.', 'prefix' => '/sales'], function () {
        Route::get('/', 'ReportSalesController@index')->name('index');
        Route::post('/export', 'ReportSalesController@export')->name('export');
        Route::post('/print_report', 'ReportSalesController@print_report')->name('print_report');
    });
    Route::resource('sales', 'ReportSalesController');

    Route::group(['as' => 'revenue.', 'prefix' => '/revenue'], function () {
        Route::get('/', 'ReportRevenueController@index')->name('index');
        Route::get('/json', 'ReportRevenueController@json')->name('json');
        Route::get('/print', 'ReportRevenueController@print')->name('print');
    });

    Route::group(['as' => 'product_performance.', 'prefix' => '/product_performance'], function () {
        Route::get('/', 'ReportProductPerformanceController@index')->name('index');
        Route::get('/json', 'ReportProductPerformanceController@json')->name('json');
        Route::get('/getProductsByBrand', 'ReportProductPerformanceController@getProductsByBrand')->name('getProductsByBrand');
        Route::post('/print_report', 'ReportProductPerformanceController@print_report')->name('print_report');
    });

    Route::group(['as' => 'customer_type_brand.', 'prefix' => '/customer_type_brand'], function () {
        Route::get('/', 'ReportCustomerTypeBrandController@index')->name('index');
        Route::get('/postData', 'ReportCustomerTypeBrandController@postData')->name('postData');
        Route::get('/removeDt', 'ReportCustomerTypeBrandController@removeDt')->name('removeDt');
        Route::post('/print_report', 'ReportCustomerTypeBrandController@print_report')->name('print_report');
    });
    Route::resource('customer_type_brand', 'ReportCustomerTypeBrandController');

    Route::group(['as' => 'customer_type_zone.', 'prefix' => '/customer_type_zone'], function () {
        Route::get('/', 'ReportCustomerTypeZoneController@index')->name('index');
        Route::get('/postData', 'ReportCustomerTypeZoneController@postData')->name('postData');
        Route::get('/removeDt', 'ReportCustomerTypeZoneController@removeDt')->name('removeDt');
        Route::post('/print_report', 'ReportCustomerTypeZoneController@print_report')->name('print_report');
    });
    Route::resource('customer_type_zone', 'ReportCustomerTypeZoneController');

    Route::group(['as' => 'report_delivery_cost.', 'prefix' => '/report_delivery_cost'], function () {
        Route::get('/', 'ReportDeliveryCostController@index')->name('index');
        Route::get('/json', 'ReportDeliveryCostController@json')->name('json');
    });
    Route::resource('report_delivery_cost', 'ReportDeliveryCostController');

    Route::group(['as' => 'salesman.', 'prefix' => '/salesman'], function () {
        Route::get('/', 'ReportSalesmanController@index')->name('index');
        Route::post('/export', 'ReportSalesmanController@export')->name('export');
    });
    Route::resource('salesman', 'ReportSalesmanController');

    Route::group(['as' => 'forecast_supplier.', 'prefix' => '/forecast_supplier'], function () {
        Route::get('/', 'ReportForecastSupplierController@index')->name('index');
        Route::post('/printReport', 'ReportForecastSupplierController@printReport')->name('printReport');
    });
    Route::resource('forecast_supplier', 'ReportForecastSupplierController');

    Route::group(['as' => 'customer_order_variant.', 'prefix' => '/customer_order_variant'], function () {
        Route::get('/', 'ReportCustmerOrderVariantController@index')->name('index');
        Route::post('/print_report', 'ReportCustmerOrderVariantController@print_report')->name('print_report');
        Route::get('/getProductsByBrand', 'ReportCustmerOrderVariantController@getProductsByBrand')->name('getProductsByBrand');
        Route::get('/get_brand', 'ReportCustmerOrderVariantController@get_brand')->name('get_brand');
        Route::post('/get_product', 'ReportCustmerOrderVariantController@get_product')->name('get_product');
    });
    Route::resource('customer_order_variant', 'ReportCustmerOrderVariantController');

    Route::group(['as' => 'customer_order_variant_v2.', 'prefix' => '/customer_order_variant_v2'], function () {
        Route::get('/', 'ReportCustomerOrderVariantV2Controller@index')->name('index');
        Route::post('/print_report', 'ReportCustomerOrderVariantV2Controller@print_report')->name('print_report');
        Route::get('/getProductsByBrand', 'ReportCustomerOrderVariantV2Controller@getProductsByBrand')->name('getProductsByBrand');
        // Route::post('/get_product', 'ReportCustomerOrderVariantV2Controller@get_product')->name('get_product');
        // Route::post('/get_product', 'ReportCustomerOrderVariantV2Controller@get_product')->name('get_product');
    });
    Route::resource('customer_order_variant_v2', 'ReportCustomerOrderVariantV2Controller');

    Route::group(['as' => 'product_high_sell.', 'prefix' => '/product_high_sell'], function () {
        Route::get('/', 'ReportProductHighSellController@index')->name('index');
        Route::post('/print_report', 'ReportProductHighSellController@print_report')->name('print_report');
    });
    Route::resource('product_high_sell', 'ReportProductHighSellController');

    Route::group(['as' => 'employee_performance.', 'prefix' => '/employee_performance'], function () {
        Route::get('/', 'ReportEmployeePerformanceController@index')->name('index');
        Route::post('/print_report', 'ReportEmployeePerformanceController@print_report')->name('print_report');
    });
    Route::resource('employee_performance', 'ReportEmployeePerformanceController');

    Route::group(['as' => 'summary_customer_product.', 'prefix' => '/summary_customer_product'], function () {
        Route::get('/', 'ReportSummaryCustomerProductController@index')->name('index');
        Route::get('/getProductsByBrand', 'ReportSummaryCustomerProductController@getProductsByBrand')->name('getProductsByBrand');
        Route::post('/print_report', 'ReportSummaryCustomerProductController@print_report')->name('print_report');
    });
    Route::resource('summary_customer_product', 'ReportSummaryCustomerProductController');
});