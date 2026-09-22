<?php

Route::group(['as' => 'utility.', 'prefix' => '/utility'], function () {
    // Endpoint polling maintenance: bisa diakses SEMUA user login
    // (tanpa batasan role) agar user online langsung diberi tahu.
    // Termasuk dalam $except CheckForMaintenanceMode.
    Route::get('/settings/maintenance-status', 'Utility\SettingController@maintenanceStatus')
        ->middleware(['auth:superuser'])
        ->name('settings.maintenanceStatus');

    Route::group(['middleware' => ['role:Developer|SuperAdmin', 'auth:superuser'], 'as' => 'settings.', 'prefix' => '/settings'], function () {
        Route::get('/', 'Utility\SettingController@index')->name('index');
        Route::post('/website', 'Utility\SettingController@website')->name('website');
        Route::get('/toggleMaintenanceMode', 'Utility\SettingController@toggleMaintenanceMode')
            ->name('toggleMaintenanceMode');
        Route::get('/backupDatabase', 'Utility\SettingController@backupDatabase')->name('backupDatabase');

        // ✅ Email Routes
        Route::group(['prefix' => 'emails', 'as' => 'emails.'], function () {
            Route::get('/index', 'Utility\MailController@index')->name('index');
            Route::get('/create', 'Utility\MailController@create')->name('create');
            Route::post('/sendEmail', 'Utility\MailController@sendEmail')->name('sendEmail');
        });
    });

    Route::get('/indonesian_teritory', function () {
        return view('superuser.utility.indonesian_teritory');
    })->middleware(['role:Developer|SuperAdmin', 'auth:superuser'])->name('indonesian_teritory');
});