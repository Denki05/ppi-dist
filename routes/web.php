<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use App\Http\Controllers\MailController;
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
        // Halaman SSO kandidat (signup + login Google, 1 tombol).
        Route::get('/sso', 'Superuser\SsoGoogleController@index')->name('sso');
        Route::get('/sso/google', 'Superuser\SsoGoogleController@redirect')->name('sso.redirect');
        Route::get('/sso/google/callback', 'Superuser\SsoGoogleController@callback')->name('sso.callback');
    });
});

Route::group(['as' => 'utility.'], function () {
    Route::post('/token', function () {
        return 'Token valid.';
    })->name('token');
});

Route::get('/auth/superuser/magic-login/{token}', [\App\Http\Controllers\Superuser\AuthenticationController::class, 'magicLogin'])
    ->name('superuser.magic-login');

// Landing halal — tabel baru halal_helpers (bukan cms_*).
// Subdomain production: halal.lsfragrance.id
Route::domain('halal.lsfragrance.id')->group(function () {
    Route::get('/', 'HalalLandingController@index')->name('halal.index');
    Route::get('/produk', 'HalalLandingController@listProduk')->name('halal.produk');
    Route::get('/search', 'HalalLandingController@search')->name('halal.search');
    Route::get('/produk/{id}', 'HalalLandingController@show')->name('halal.show');
    Route::get('/produk/{id}/preview', 'HalalLandingController@preview')->name('halal.preview');
});

// Fallback lokal (/halal...) — untuk dev tanpa DNS subdomain.
Route::group(['prefix' => 'halal', 'as' => 'halal.local.'], function () {
    Route::get('/', 'HalalLandingController@index')->name('index');
    Route::get('/produk', 'HalalLandingController@listProduk')->name('produk');
    Route::get('/search', 'HalalLandingController@search')->name('search');
    Route::get('/produk/{id}', 'HalalLandingController@show')->name('show');
    Route::get('/produk/{id}/preview', 'HalalLandingController@preview')->name('preview');
});