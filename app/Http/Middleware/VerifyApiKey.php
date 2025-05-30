<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-API-KEY');

        if (!$key || !ApiKey::where('key', $key)->where('is_active', true)->exists()) {
            return response()->json(['message' => 'Unauthorized or invalid API Key'], 401);
        }

        return $next($request);
    }
}