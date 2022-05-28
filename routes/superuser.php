<?php

Route::group([
    'middleware' => ['auth:superuser'],
], function () {
    Route::get('/', 'DashboardController@index')->name('index');
});

Route::get('/getToken', 'AuthenticationController@getToken')->name('getToken');

Route::get('/logout', 'AuthenticationController@logout')->name('logout');