<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * CORS khusus API halal untuk origin landing halal.lsfragrance.id
 * (hosting terpisah dari aplikasi transaksi).
 *
 * Daftar origin lewat env agar beda per environment:
 *   HALAL_ALLOWED_ORIGINS=https://halal.lsfragrance.id
 * Untuk test lokal bisa tambah: ,http://127.0.0.1:8099,http://localhost:8099
 */
class CorsHalal
{
    public function handle(Request $request, Closure $next)
    {
        $allowed = array_filter(array_map('trim', explode(
            ',',
            env('HALAL_ALLOWED_ORIGINS', 'https://halal.lsfragrance.id')
        )));

        $origin = $request->header('Origin');

        // Preflight: jawab langsung tanpa masuk controller.
        if ($request->isMethod('options')) {
            $response = response('', 204);
        } else {
            $response = $next($request);
        }

        if ($origin && in_array($origin, $allowed, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Vary', 'Origin');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
            $response->headers->set('Access-Control-Max-Age', '86400');
        }

        return $response;
    }
}
