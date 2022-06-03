<?php

Route::group(['as' => 'website.'], function () {
    Route::get('/', function () {
        return redirect('auth/superuser');
    });
});

Route::group(['as' => 'auth.', 'prefix' => '/auth', 'middleware' => 'authenctication'], function () {
    Route::group(['as' => 'superuser.', 'prefix' => '/superuser'], function () {
        Route::get('/', 'Superuser\AuthenticationController@index')->name('index');
        Route::post('/login', 'Superuser\AuthenticationController@login')->name('login')->middleware('throttle:10,1');
    });
});

Route::group(['as' => 'utility.'], function () {
    Route::post('/token', function () {
        return 'Token valid.';
    })->name('token');
});


Route::get('compailePdf', 'JasperReportPdfController@compailePdf')->name('compailePdf');
Route::get('getReportPdf', 'JasperReportPdfController@getReportPdf')->name('getReportPdf');