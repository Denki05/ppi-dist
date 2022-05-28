<?php

Route::group([
    // 'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'repo.',
    'prefix' => '/repo',
    ], function () {

    Route::group([
        'as' => 'master.',
        'prefix' => '/master'
    ],function () {
        Route::get('/product_type', 'Repo\MasterController@product_type')->name('product_type');
    });
});