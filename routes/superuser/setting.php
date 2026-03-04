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

    Route::group(['as' => '.', 'prefix' => '/api_keys'], function () {
        Route::get('/', 'ApiKeyController@index')->name('index');
        Route::get('/create', 'ApiKeyController@create')->name('create');
        Route::post('/store', 'ApiKeyController@store')->name('store');
        Route::get('/edit', 'ApiKeyController@edit')->name('edit');
        Route::post('/update', 'ApiKeyController@update')->name('update');
        Route::delete('/destroy/{id}', 'ApiKeyController@destroy')->name('destroy');

    });
});