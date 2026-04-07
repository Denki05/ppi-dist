<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next)
    {
        // ambil dari header atau query
        $key = $request->header('X-API-KEY') ?? $request->get('api_key');

        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'API Key is missing'
            ], 401);
        }

        // cek ke database TANPA MODEL
        $exists = DB::table('api_keys')
            ->where('key', $key)
            ->where('is_active', 1)
            ->exists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API Key'
            ], 401);
        }

        return $next($request);
    }
}