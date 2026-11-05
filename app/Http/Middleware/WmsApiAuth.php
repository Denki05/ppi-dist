<?php

namespace App\Http\Middleware;

use App\Services\WmsTokenService;
use Closure;

class WmsApiAuth
{
    public function handle($request, Closure $next)
    {
        $payload = WmsTokenService::verify($request->bearerToken(), 'WMS');

        if (!$payload) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token WMS tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        if (!in_array((string) $payload['sub'], config('wms.user_ids', []), true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun tidak terdaftar pada project WMS.',
            ], 403);
        }

        $request->attributes->set('wms_user', $payload);

        return $next($request);
    }
}
