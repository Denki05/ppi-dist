<?php

namespace App\Http\Controllers\Superuser;

use App\Entities\Account\Superuser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\SuperuserLoginToken;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    public function index()
    {
        $down = app()->isDownForMaintenance();

        return view('superuser.auth', [
            'maintenanceDown' => $down,
            'maintenanceMessage' => $down ? $this->maintenanceMessage() : null,
        ]);
    }

    public function login(Request $request)
    {
        if ($request->ajax()) {
            $rules = [
                'account_name' => 'required|string',
                'password' => 'required|string',
                'remember' => 'nullable',
            ];

            $validator = validator($request->all(), $rules);

            $superuser = Superuser::where('username', $request->account_name)->orWhere('email', $request->account_name)->first();

            // Gate maintenance: user biasa (semua role selain
            // Developer/SuperAdmin) ditolak login sejak awal dengan
            // pesan yang jelas, agar tidak "login sukses lalu mental".
            // Admin tetap boleh login untuk mematikan maintenance.
            if (app()->isDownForMaintenance() && !$this->isMaintenanceAdmin($superuser)) {
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'danger',
                    'content' => $this->maintenanceMessage(),
                ];

                return $this->response(503, $response);
            }

            if (filter_var($request->account_name, FILTER_VALIDATE_EMAIL)) {
                $field = 'email';
            } else {
                $field = 'username';
            }

            $credentials = [
                $field => $request->account_name,
                'password' => $request->password,
                'is_active' => true
            ];

            $remember = $request->remember ?? false;

            if ($validator->fails() OR Auth::guard('superuser')->attempt($credentials) == false OR !$superuser) {
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'danger',
                    'content' => 'Login Failed',
                ];

                return $this->response(400, $response);
            } else if ($validator->passes() AND Auth::guard('superuser')->attempt($credentials, $remember) == true) {
                $request->session()->regenerate();

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];
                $response['redirect_to'] = route('superuser.index');
                return $this->response(200, $response);
            }
        } else {
            return redirect()->route('auth.superuser.index');
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('superuser')->logout();
        $request->session()->flush();

        return redirect()->route('auth.superuser.index');
    }

    public function getToken(Request $request) {
        if ($request->ajax()) {
            $superuser = Auth::guard('superuser')->user();
            $passcode = $request->link . '###' . $superuser->username;
            $encrypted = Crypt::encryptString($passcode);

            return response()->json($encrypted);
        } else {
            return 'hello';
        }
    }

    public function generateMagicLink($superuserId)
    {
        $user = Superuser::findOrFail($superuserId);

        $plainToken = Str::random(64);

        SuperuserLoginToken::create([
            'superuser_id' => $user->id,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addHours(12),
            'used' => false,
        ]);

        // Gunakan route agar lebih fleksibel
        return route('superuser.magic-login', ['token' => $plainToken]);
    }

    public function magicLogin($plainToken)
    {
        // Cari token di database
        $tokenData = SuperuserLoginToken::where('token', hash('sha256', $plainToken))
            ->where('used', false)
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$tokenData) {
            return redirect()->route('auth.superuser.index')
                ->withErrors(['Token tidak valid atau sudah kadaluarsa.']);
        }

        // Gate maintenance: user biasa ditolak dengan pesan jelas.
        // Token sengaja TIDAK ditandai used agar bisa dipakai lagi
        // setelah maintenance selesai.
        if (app()->isDownForMaintenance() && !$this->isMaintenanceAdmin($tokenData->superuser)) {
            return redirect()->route('auth.superuser.index')
                ->withErrors([$this->maintenanceMessage()]);
        }

        // Login otomatis
        Auth::guard('superuser')->login($tokenData->superuser);

        // Tandai token sudah digunakan
        $tokenData->update(['used' => true]);

        return redirect()->route('superuser.index'); // ubah sesuai dashboard Anda
    }

    /**
     * Admin (Developer/SuperAdmin) boleh login saat maintenance.
     * $user null (username tidak dikenal) dianggap bukan admin.
     */
    protected function isMaintenanceAdmin($user)
    {
        try {
            if ($user && method_exists($user, 'hasAnyRole')) {
                return $user->hasAnyRole(['Developer', 'SuperAdmin']);
            }

            if ($user && isset($user->is_superuser) && $user->is_superuser == 1) {
                return true;
            }
        } catch (\Throwable $e) {
            // abaikan, anggap bukan admin
        }

        return false;
    }

    /**
     * Ambil pesan maintenance dari file storage/framework/down,
     * fallback ke pesan default bila file tidak ada.
     */
    protected function maintenanceMessage()
    {
        $default = 'Sistem sedang dalam pemeliharaan (update fitur). Mohon simpan pekerjaan Anda dan coba login kembali nanti.';

        try {
            $raw = @file_get_contents(storage_path('framework/down'));
            $data = json_decode($raw, true);
            if (!empty($data['message'])) {
                return $data['message'];
            }
        } catch (\Throwable $e) {
            // pakai pesan default
        }

        return $default;
    }
}
