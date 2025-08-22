<?php

use App\Htpp\Controllers\Superuser\Finance\PayableController;

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
        Route::get('/{id}/print2', 'InvoicingController@print2')->name('print2');
        Route::get('/{id}/print_portait', 'InvoicingController@print_portait')->name('print_portait');
        Route::get('/{id}/print_paid', 'InvoicingController@print_paid')->name('print_paid');
        Route::get('/{id}/print_proforma', 'InvoicingController@print_proforma')->name('print_proforma');
        Route::get('/{id}/history_payable', 'InvoicingController@history_payable')->name('history_payable');
        Route::get('/json', 'InvoicingController@json')->name('json');
        Route::get('/json2', 'InvoicingController@json2')->name('json2');
        Route::get('/updateInvoice', 'InvoicingController@updateInvoice')->name('updateInvoice');
        Route::get('/pageReport', 'InvoicingController@pageReport')->name('pageReport');
        Route::post('/printReportPage', 'InvoicingController@printReportPage')->name('printReportPage');
        Route::get('/download_invoice_full/{id}', 'InvoicingController@download_invoice_full')->name('download_invoice_full');
        Route::get('/download_invoice_proforma/{id}', 'InvoicingController@download_invoice_proforma')->name('download_invoice_proforma');
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
        // Route::get('/{id}/edit', 'PayableController@edit')->name('edit');
        // Route::put('/{id}/update', 'PayableController@update')->name('update');

        Route::put('/finance/payable/{id}', [PayableController::class, 'update'])->name('superuser.finance.payable.update');
        Route::get('/finance/payable/{id}/edit', [PayableController::class, 'edit'])->name('superuser.finance.payable.edit');
        Route::post('/{id}/store', 'PayableController@store')->name('store');
        Route::get('/approve/{id}', 'PayableController@approve')->name('approve');
        Route::get('/{id}/print', 'PayableController@print')->name('print');
        Route::get('/json', 'PayableController@json')->name('json');
        Route::get('/json2', 'PayableController@json2')->name('json2');
        Route::get('/pageReport', 'PayableController@pageReport')->name('pageReport');
        Route::get('/{id}/cancel_approve', 'PayableController@cancel_approve')->name('cancel_approve');
        Route::get('/{id}/cancel_edit', 'PayableController@cancel_edit')->name('cancel_edit');
        Route::post('/{id}/update_cancel', 'PayableController@update_cancel')->name('update_cancel');
        Route::get('/unpaidInvoices', 'PayableController@unpaidInvoices')->name('unpaidInvoices');
        Route::get('/customerSearch', 'PayableController@customerSearch')->name('customerSearch');
        Route::get('/json_done', 'PayableController@json_done')->name('json_done');
    });
    Route::resource('payable', 'PayableController');

    Route::group(['as' => 'cashback.', 'prefix' => '/cashback'], function () {
        Route::get('/get_invoice', 'CashbackController@get_invoice')->name('get_invoice');
        Route::get('/create/{id}', 'CashbackController@create')->name('create');
        Route::delete('/destroy/{id}', 'CashbackController@destroy')->name('destroy');
        Route::get('/print_invoice_beli/{id}', 'CashbackController@print_invoice_beli')->name('print_invoice_beli');
        Route::get('/print_invoice_jual/{id}', 'CashbackController@print_invoice_jual')->name('print_invoice_jual');
        Route::get('/pageReport', 'CashbackController@pageReport')->name('pageReport');
        Route::get('/pageReportBeli', 'CashbackController@pageReportBeli')->name('pageReportBeli');
        Route::get('/pageReportJual', 'CashbackController@pageReportJual')->name('pageReportJual');
        Route::get('/json2', 'CashbackController@json2')->name('json2');

    });
    Route::resource('cashback', 'CashbackController')->except(['create']);
});