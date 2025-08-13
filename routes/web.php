<?php

use Illuminate\Support\Facades\Auth;
use App\Models\SuperuserLoginToken;

Route::group(['as' => 'website.'], function () {
    Route::get('/', function () {
        return redirect('auth/superuser');
    });
});

Route::group(['as' => 'auth.', 'prefix' => '/auth', 'middleware' => 'authenctication'], function () {
    Route::group(['as' => 'superuser.', 'prefix' => '/superuser'], function () {
        Route::get('/', 'Superuser\AuthenticationController@index')->name('index');
        Route::post('/login', 'Superuser\AuthenticationController@login')->name('login')->middleware('throttle:10,1');
    });
});

Route::group(['as' => 'utility.'], function () {
    Route::post('/token', function () {
        return 'Token valid.';
    })->name('token');
});

Route::get('/auth/superuser/magic-login/{token}', [\App\Http\Controllers\Superuser\AuthenticationController::class, 'magicLogin'])
    ->name('superuser.magic-login');
