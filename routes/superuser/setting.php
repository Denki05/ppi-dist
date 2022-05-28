<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'setting.',
    'prefix' => '/setting',
    'namespace' => 'Setting'
], function () {

    Route::group(['as' => 'menu.', 'prefix' => '/menu'], function () {
        Route::get('/', 'MenuController@index')->name('index');
        Route::post('/store', 'MenuController@store')->name('store');
        Route::post('/update', 'MenuController@update')->name('update');
        Route::post('/destroy', 'MenuController@destroy')->name('destroy');
    });
   
});