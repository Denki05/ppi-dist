<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyUserApiKey
{
    /**
     * Kunci direktori user untuk modul AO.
     * Samakan USER_API_KEY di .env sini dengan AGENDA_TOKEN di .env AO.
     */
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) config('services.user_api.key', '');

        if ($expected === '' || $request->header('X-API-KEY') !== $expected) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: API key tidak valid',
            ], 401);
        }

        return $next($request);
    }
}
