<?php

Route::group(['as' => 'utility.', 'prefix' => '/utility'], function () {
    Route::group(['middleware' => ['role:Developer|SuperAdmin', 'auth:superuser'], 'as' => 'settings.', 'prefix' => '/settings'], function () {
        Route::get('/', 'Utility\SettingController@index')->name('index');
        Route::post('/website', 'Utility\SettingController@website')->name('website');
        Route::get('/enableMaintenanceMode', 'Utility\SettingController@enableMaintenanceMode')->name('enableMaintenanceMode');
        Route::get('/disableMaintenanceMode', 'Utility\SettingController@disableMaintenanceMode')->name('disableMaintenanceMode');
        Route::get('/backupDatabase', 'Utility\SettingController@backupDatabase')->name('backupDatabase');
    });

    Route::get('/indonesian_teritory', function () {
        return view('superuser.utility.indonesian_teritory');
    })->middleware(['role:Developer|SuperAdmin', 'auth:superuser'])->name('indonesian_teritory');
});    
