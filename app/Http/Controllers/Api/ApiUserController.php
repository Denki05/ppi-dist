<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Direktori user untuk login modul AO (taskManagement).
 * GET /api/superusers
 * GET /api/superusers/{id}
 *
 * Response {success, data} — data memuat hash password karena
 * verifikasi password dilakukan di sisi AO (password_verify).
 * Lindungi dengan X-API-KEY (middleware user.apikey).
 */
class ApiUserController extends Controller
{
    public function index(Request $request)
    {
        $users = DB::table('superusers')
            ->select('id', 'username', 'name', 'password', 'division', 'is_active', 'is_superuser')
            ->where('is_active', 1)
            ->orderBy('username')
            ->get();

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function show(Request $request, $id)
    {
        $user = DB::table('superusers')
            ->select('id', 'username', 'name', 'password', 'division', 'is_active', 'is_superuser')
            ->where('id', $id)
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $user]);
    }
}
