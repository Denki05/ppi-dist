<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ApiCustomerController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Ambil Semua Data Customer
Route::get('customers', [ApiCustomerController::class, 'getApiData']);