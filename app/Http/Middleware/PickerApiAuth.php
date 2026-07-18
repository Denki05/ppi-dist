<?php

namespace App\Http\Middleware;

use Closure;
// Path ini sudah diperbaiki sesuai dengan struktur project Anda
use App\Entities\Account\Superuser; 

class PickerApiAuth
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken(); 

        $user = Superuser::where('api_token', $token)->first();

        if (!$token || !$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized. Token tidak valid atau sesi telah habis.'
            ], 401);
        }

        return $next($request);
    }
}