<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'account.',
    'prefix' => '/account'
], function () {
    
    Route::group(['as' => 'superuser.', 'prefix' => '/superuser'], function () {
        Route::get('/restore/{id}', 'Account\SuperuserController@restore')->name('restore');

        Route::post('/role/assign/{id}', 'Account\SuperuserController@assignRole')->name('role.assign');
        Route::post('/role/remove/{id?}', 'Account\SuperuserController@removeRole')->name('role.remove');

        Route::post('/permission/sync/{id}', 'Account\SuperuserController@syncPermission')->name('permission.sync');
    });
    Route::resource('superuser', 'Account\SuperuserController');
    
    Route::group(['as' => 'user.', 'prefix' => '/user'], function () {
        Route::get('/','Account\UserController@index')->name('index');
        Route::get('/create','Account\UserController@create')->name('create');
        Route::get('/{id}/edit','Account\UserController@edit')->name('edit');
        Route::post('/store','Account\UserController@store')->name('store');
        Route::post('/update','Account\UserController@update')->name('update');
        Route::post('/destroy','Account\UserController@destroy')->name('destroy');
        Route::post('/restore','Account\UserController@restore')->name('restore');
    });

    Route::group(['as' => 'sales_person.', 'prefix' => '/sales_person'], function () {
        Route::get('/restore/{id}', 'Account\SalesPersonController@restore')->name('restore');

        Route::group(['as' => 'zone.'], function () {
            Route::get('/{id}/zone', 'Account\SalesPersonZoneController@manage')->name('manage');
            Route::post('/{id}/zone', 'Account\SalesPersonZoneController@add')->name('add');
            Route::get('/{id}/zone/{zone_id}/remove', 'Account\SalesPersonZoneController@remove')->name('remove');
        });

        Route::group(['as' => 'warehouse.'], function () {
            Route::get('/{id}/warehouse', 'Account\SalesPersonWarehouseController@manage')->name('manage');
            Route::post('/{id}/warehouse', 'Account\SalesPersonWarehouseController@add')->name('add');
            Route::get('/{id}/warehouse/{warehouse_id}/remove', 'Account\SalesPersonWarehouseController@remove')->name('remove');
        });

        Route::group(['as' => 'branch_office.'], function () {
            Route::get('/{id}/branch_office', 'Account\SalesPersonBranchOfficeController@manage')->name('manage');
            Route::post('/{id}/branch_office', 'Account\SalesPersonBranchOfficeController@add')->name('add');
            Route::get('/{id}/branch_office/{branch_office_id}/remove', 'Account\SalesPersonBranchOfficeController@remove')->name('remove');
        });
    });
    Route::resource('sales_person', 'Account\SalesPersonController');

    
});