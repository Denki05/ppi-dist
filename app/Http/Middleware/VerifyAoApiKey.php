<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyAoApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) config('services.ao_api.key', '');
        $provided = (string) $request->header('X-API-KEY', '');

        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: API key tidak valid',
            ], 401);
        }

        return $next($request);
    }
}