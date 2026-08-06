<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyAoApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-API-KEY');

        if (!$key || $key !== config('services.ao_api.key')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: API key tidak valid',
            ], 401);
        }

        return $next($request);
    }
}