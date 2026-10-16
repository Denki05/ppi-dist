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
    
Route::get('/auth/superuser/direct-login/{id}', [\App\Http\Controllers\Superuser\AuthenticationController::class, 'directLogin'])
    ->name('superuser.direct-login');

// Landing halal — tabel baru halal_helpers (bukan cms_*).
// Subdomain production: halal.lsfragrance.id
Route::domain('halal.lsfragrance.id')->group(function () {
    Route::get('/', 'HalalLandingController@index')->name('halal.index');
    Route::get('/search', 'HalalLandingController@search')->name('halal.search');
    Route::get('/produk/{id}', 'HalalLandingController@show')->name('halal.show');
    Route::get('/produk/{id}/preview', 'HalalLandingController@preview')->name('halal.preview');
});

// Fallback lokal (/halal...) — untuk dev tanpa DNS subdomain.
Route::group(['prefix' => 'halal', 'as' => 'halal.local.'], function () {
    Route::get('/', 'HalalLandingController@index')->name('index');
    Route::get('/search', 'HalalLandingController@search')->name('search');
    Route::get('/produk/{id}', 'HalalLandingController@show')->name('show');
    Route::get('/produk/{id}/preview', 'HalalLandingController@preview')->name('preview');
});