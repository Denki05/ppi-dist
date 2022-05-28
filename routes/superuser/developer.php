<?php

Route::group(['middleware' => ['role:Developer', 'auth:superuser']], function () {
    Route::group(['middleware' => ['permission:boilerplate-manage']], function () {
        Route::get('/boilerplate/import_template', 'BoilerplateController@import_template')->name('boilerplate.import_template');
        Route::post('/boilerplate/import', 'BoilerplateController@import')->name('boilerplate.import');
        Route::get('/boilerplate/export', 'BoilerplateController@export')->name('boilerplate.export');
        Route::get('/boilerplate/restore/{id}', 'BoilerplateController@restore')->name('boilerplate.restore');
        Route::delete('/boilerplate/destroy_permanent/{id}', 'BoilerplateController@destroy_permanent')->name('boilerplate.destroy_permanent');
        Route::resource('boilerplate', 'BoilerplateController');
    
        Route::group(['as' => 'boilerplate_img.', 'prefix' => '/boilerplate_img'], function () {
            Route::post('/delete/{id}', 'BoilerplateImageController@delete');
            Route::get('/restore/{parent_id}/{id}', 'BoilerplateImageController@restore')->name('restore');
            Route::get('/restore_all/{parent_id}', 'BoilerplateImageController@restore_all')->name('restore_all');
            Route::get('/destroy/{parent_id}/{id}', 'BoilerplateImageController@destroy')->name('destroy');
            Route::get('/destroy_all/{parent_id}', 'BoilerplateImageController@destroy_all')->name('destroy_all');
        });
    });

    Route::group(['middleware' => ['permission:terminal-manage']], function () {
        Route::get('/terminal', 'Utility\TerminalController')->name('terminal');
    });
    
    Route::group(['middleware' => ['permission:gate-manage'], 'as' => 'gate.', 'prefix' => '/gate'], function () {
        Route::get('/', 'Utility\GateController@index')->name('index');

        Route::get('/show/role/{id?}', 'Utility\GateController@show_role')->name('show.role');

        Route::post('/save/guard', 'Utility\GateController@save_guard')->name('save.guard');
        Route::post('/save/role', 'Utility\GateController@save_role')->name('save.role');

        Route::post('/save/permission', 'Utility\GateController@save_permission')->name('save.permission');

        Route::get('/delete/role/{id?}', 'Utility\GateController@delete_role')->name('delete.role');
    });
});