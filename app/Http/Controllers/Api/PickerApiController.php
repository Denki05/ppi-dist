<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Entities\Account\Superuser; 
use App\Entities\Penjualan\PackingOrder; 

class PickerApiController extends Controller
{
    public function login(Request $request)
    {
        // 1. Cari user di database
        // PENTING: Jika saat login Anda biasa menggunakan 'email' dan bukan 'username', 
        // silakan ganti kata 'username' di bawah ini menjadi 'email'
        $user = Superuser::where('username', $request->username)->first();

        // 2. Cek apakah user ditemukan dan password cocok menggunakan Hash
        if ($user && Hash::check($request->password, $user->password)) {
            
            // Generate Token Baru
            $token = Str::random(60);
            
            // Simpan token ke database superuser
            $user->api_token = $token; 
            $user->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Login berhasil',
                'data'    => [
                    'token' => $token,
                    'user'  => [
                        'id'       => $user->id,
                        'name'     => $user->name,
                        'username' => $user->username
                    ]
                ]
            ], 200);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Username atau Password salah'
        ], 401);
    }

    public function getReadyTasks()
    {
        $tasks = PackingOrder::select('id', 'do_code', 'code', 'created_at', 'status')
            ->where('status', 3)
            ->orderBy('created_at', 'ASC')
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data task berhasil diambil',
            'data'    => $tasks
        ], 200);
    }
}