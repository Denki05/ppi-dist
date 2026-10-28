<?php

use Illuminate\Support\Facades\Auth;
use App\Models\SuperuserLoginToken;

Route::group(['as' => 'website.'], function () {
    Route::get('/', function () {
        $guard = Auth::guard('superuser');

        if (!$guard->check()) {
            return redirect()->route('auth.superuser.index');
        }

        $user = $guard->user();

        // Sesi bisa tertinggal setelah akun dinonaktifkan; bersihkan dulu.
        if (!$user || (int) $user->is_active !== 1) {
            $guard->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('auth.superuser.index');
        }

        return redirect()->route('portal.modules');
    });
});

Route::group(['as' => 'auth.', 'prefix' => '/auth', 'middleware' => 'authenctication'], function () {
    Route::group(['as' => 'superuser.', 'prefix' => '/superuser'], function () {
        Route::get('/', 'Superuser\AuthenticationController@index')->name('index');
        Route::post('/login', 'Superuser\AuthenticationController@login')->name('login')->middleware('throttle:10,1');
        // Halaman SSO kandidat (signup + login Google, 1 tombol).
        Route::get('/sso', 'Superuser\SsoGoogleController@index')->name('sso');
        Route::get('/sso/google', 'Superuser\SsoGoogleController@redirect')->name('sso.redirect')->middleware('throttle:20,1');
        Route::get('/sso/google/callback', 'Superuser\SsoGoogleController@callback')->name('sso.callback')->middleware('throttle:30,1');
    });
});

// Portal pilih modul setelah login. Halaman ini tidak memakai guard modul TRS
// supaya user yang hanya punya MIS/AO tetap bisa memilih modulnya.
Route::group(['middleware' => ['superuser']], function () {
    Route::get('/portal/modules', [\App\Http\Controllers\Superuser\ModuleController::class, 'index'])->name('portal.modules');
    Route::get('/portal/modules/{module}', [\App\Http\Controllers\Superuser\ModuleController::class, 'go'])->name('portal.modules.go');

    // Profil mandiri: tidak butuh akses TRS, dipakai user yang hanya punya
    // modul lain (mis. DSG) untuk memperbaiki email / menautkan Google.
    Route::get('/portal/profile', [\App\Http\Controllers\Superuser\ProfileController::class, 'portal'])->name('portal.profile');
    Route::post('/portal/profile/update', [\App\Http\Controllers\Superuser\ProfileController::class, 'update'])->name('portal.profile.update');
    Route::post('/portal/profile/google-link', [\App\Http\Controllers\Superuser\ProfileController::class, 'startGoogleLink'])->name('portal.profile.google-link');
    Route::post('/portal/profile/unlink-google', [\App\Http\Controllers\Superuser\ProfileController::class, 'unlinkGoogle'])->name('portal.profile.unlink-google');
    Route::post('/portal/profile/logout-all-devices', [\App\Http\Controllers\Superuser\ProfileController::class, 'logoutAllDevices'])->name('portal.profile.logout-all-devices');
});

Route::group(['as' => 'utility.'], function () {
    Route::post('/token', function () {
        return 'Token valid.';
    })->name('token');
});

Route::get('/auth/superuser/magic-login/{token}', [\App\Http\Controllers\Superuser\AuthenticationController::class, 'magicLogin'])
    ->name('superuser.magic-login')
    ->middleware('throttle:10,1');
    
Route::get('/auth/superuser/direct-login/{id}', [\App\Http\Controllers\Superuser\AuthenticationController::class, 'directLogin'])
    ->name('superuser.direct-login');

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