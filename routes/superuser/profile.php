<?php

Route::group(['as' => 'profile.', 'prefix' => '/profile'], function () {
    Route::get('/', 'ProfileController@index')->name('index');
    Route::post('/update', 'ProfileController@update')->name('update');
    Route::post('/unlink-google', 'ProfileController@unlinkGoogle')->name('unlink-google');
});