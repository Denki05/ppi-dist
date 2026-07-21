<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth; // <-- PENTING: Tambahkan ini
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

        // PENTING: Daftarkan user ke sistem Auth agar bisa dibaca oleh Model/Controller!
        Auth::guard('superuser')->setUser($user);

        return $next($request);
    }
}