<?php

Route::group(['as' => 'profile.', 'prefix' => '/profile'], function () {
    Route::get('/', 'ProfileController@index')->name('index');
    Route::post('/update', 'ProfileController@update')->name('update');
    Route::post('/google-link', 'ProfileController@startGoogleLink')->name('google-link');
    Route::post('/unlink-google', 'ProfileController@unlinkGoogle')->name('unlink-google');
    Route::post('/logout-all-devices', 'ProfileController@logoutAllDevices')->name('logout-all-devices');
});