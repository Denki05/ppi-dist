<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Memvalidasi request sinkronisasi user yang masuk ke TRIAL
 * (dikirim oleh trans). Header: X-API-KEY harus sama dengan
 * config('services.trial.key').
 */
class VerifyTrialSyncKey
{
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) config('services.trial.key', '');

        if ($expected === '' || $request->header('X-API-KEY') !== $expected) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: API key sinkronisasi tidak valid',
            ], 401);
        }

        return $next($request);
    }
}
