<?php

namespace App\Http\Controllers\Api;

use App\Entities\Account\Superuser;
use App\Entities\Master\Vendor;
use App\Entities\Master\Warehouse;
use App\Http\Controllers\Controller;
use App\Services\WmsTokenService;
use Illuminate\Http\Request;

class WmsAuthController extends Controller
{
    /**
     * Tukar handoff token SSO modul menjadi API token WMS.
     * Dipanggil sekali saat login; sekaligus mengembalikan data bootstrap.
     */
    public function exchange(Request $request)
    {
        $request->validate([
            'sso_token' => 'required|string',
        ]);

        $payload = WmsTokenService::verify($request->input('sso_token'), 'WMS');

        if (!$payload) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token SSO tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $user = Superuser::find($payload['sub']);

        if (!$user || (int) $user->is_active !== 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun tidak aktif.',
            ], 403);
        }

        if (!in_array((string) $user->id, config('wms.user_ids', []), true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun tidak terdaftar pada project WMS.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'token'     => WmsTokenService::issue($user),
                'bootstrap' => $this->bootstrapPayload($user),
            ],
        ]);
    }

    /**
     * Cek sesi WMS. Tanpa query database: cukup klaim token.
     */
    public function bootstrap(Request $request)
    {
        $payload = $request->attributes->get('wms_user');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user'          => $this->userPayload($payload),
                'server_time'   => date('c'),
            ],
        ]);
    }

    protected function bootstrapPayload($user)
    {
        return [
            'user'        => $this->userPayload($user),
            'warehouses'  => Warehouse::where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name']),
            'ekspedisi'   => Vendor::where('type', 1)
                ->orderBy('name')
                ->get(['id', 'name']),
            'server_time' => date('c'),
        ];
    }

    protected function userPayload($user)
    {
        $id = (string) (is_array($user) ? ($user['sub'] ?? '') : ($user->id ?? ''));

        return [
            'id'          => $id,
            'username'    => (string) (is_array($user) ? ($user['username'] ?? '') : ($user->username ?? '')),
            'name'        => (string) (is_array($user) ? ($user['name'] ?? '') : ($user->name ?? '')),
            'email'       => (string) (is_array($user) ? ($user['email'] ?? '') : ($user->email ?? '')),
            'division'    => (string) (is_array($user) ? ($user['division'] ?? '') : ($user->division ?? '')),
            'jabatan'     => (string) (is_array($user) ? ($user['jabatan'] ?? '') : ($user->jabatan ?? '')),
            'is_overtake' => in_array($id, config('wms.overtake_user_ids', []), true),
        ];
    }
}
