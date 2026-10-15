<?php

namespace App\Http\Controllers\Superuser;

use App\Entities\Account\Superuser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SsoGoogleController extends Controller
{
    /**
     * Halaman SSO khusus kandidat (signup + login Google).
     * Terpisah dari form password agar fokus 1 tombol.
     */
    public function index()
    {
        if (Auth::guard('superuser')->check()) {
            return redirect()->route('superuser.index');
        }

        return view('superuser.sso');
    }

    /**
     * Lempar ke Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Callback dari Google.
     *
     * - google_id cocok -> login (cek is_active).
     * - email cocok tapi google_id masih kosong (akun lama) -> link otomatis, lalu login.
     * - email belum ada -> buat akun PENDING (is_active=0), tunggu verifikasi admin/dev.
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('auth.superuser.sso')
                ->withErrors(['Login Google gagal, silakan coba lagi.']);
        }

        $googleId = $googleUser->getId();
        $email = strtolower(trim($googleUser->getEmail()));
        $name = $googleUser->getName() ?: $email;

        // Mode hubungkan: user sedang login password dan klik "Hubungkan Google".
        // Ini jalur akun lama yang emailnya beda / random -> mapping manual sekali.
        if (Auth::guard('superuser')->check()) {
            $current = Auth::guard('superuser')->user();
            $taken = Superuser::where('google_id', $googleId)
                ->where('id', '!=', $current->id)
                ->first();

            if ($taken) {
                return redirect()->route('superuser.profile.index')
                    ->withErrors(['Akun Google ini sudah terhubung ke user lain (' . e($taken->username) . '). Minta admin gabungkan manual.']);
            }

            $emailOwner = Superuser::where('email', $email)
                ->where('id', '!=', $current->id)
                ->first();

            if ($emailOwner) {
                return redirect()->route('superuser.profile.index')
                    ->withErrors(['Email Google ini sudah dipakai user lain (' . e($emailOwner->username) . '). Minta admin gabungkan manual.']);
            }

            $current->google_id = $googleId;
            $current->avatar = $googleUser->getAvatar() ?: $current->avatar;
            // Kalau email lama random, ganti ke gmail asli sekalian.
            if (strtolower(trim($current->email)) !== $email) {
                $current->email = $email;
            }
            $current->email_verified_at = $current->email_verified_at ?: now();
            $current->save();

            return redirect()->route('superuser.profile.index')
                ->with('status', 'Akun Google berhasil dihubungkan. Ke depan bisa login 1 klik.');
        }

        // 1. Sudah pernah link Google sebelumnya.
        $user = Superuser::where('google_id', $googleId)->first();

        // 2. Akun lama: email sama, google_id masih kosong -> link otomatis.
        if (!$user) {
            $user = Superuser::where('email', $email)->first();

            if ($user && empty($user->google_id)) {
                $user->google_id = $googleId;
                $user->avatar = $googleUser->getAvatar();
                $user->email_verified_at = $user->email_verified_at ?: now();
                $user->save();
            }
        }

        // 3. Pendaftar baru -> buat PENDING, jangan langsung aktif.
        if (!$user) {
            $base = strstr($email, '@', true) ?: 'user';
            $base = preg_replace('/[^a-z0-9]+/i', '', strtolower($base));
            $username = $base;
            $counter = 1;
            while (Superuser::where('username', $username)->exists()) {
                $counter++;
                $username = $base . $counter;
            }

            $user = new Superuser();
            $user->username = $username;
            $user->email = $email;
            $user->name = $name;
            $user->password = bcrypt(Str::random(32));
            $user->google_id = $googleId;
            $user->avatar = $googleUser->getAvatar();
            $user->email_verified_at = now();
            $user->is_active = 0;
            $user->is_superuser = 0;
            $user->save();

            return redirect()->route('auth.superuser.sso')
                ->withErrors(['Pendaftaran berhasil. Akun menunggu verifikasi admin sebelum bisa dipakai.']);
        }

        // Gate maintenance: samakan dengan login password (hanya Developer/SuperAdmin lolos).
        if (app()->isDownForMaintenance() && !$this->isMaintenanceAdmin($user)) {
            return redirect()->route('auth.superuser.index')
                ->withErrors(['Sistem sedang dalam pemeliharaan. Coba lagi nanti.']);
        }

        // Akun nonaktif (resign / belum di-approve) -> tolak dengan pesan jelas.
        if (!$user->is_active) {
            return redirect()->route('auth.superuser.sso')
                ->withErrors(['Akun belum aktif. Hubungi admin untuk verifikasi.']);
        }

        Auth::guard('superuser')->login($user, true);
        $request->session()->regenerate();

        return redirect()->route('superuser.index');
    }

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
}
