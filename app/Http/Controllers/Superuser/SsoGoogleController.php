<?php

namespace App\Http\Controllers\Superuser;

use App\Entities\Account\Superuser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('auth.superuser.sso')
                ->withErrors(['Login Google sedang tidak tersedia (konfigurasi belum lengkap). Hubungi admin.']);
        }
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
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $name = $googleUser->getName() ?: $email;

        // Email wajib ada dari Google (kalau user tidak berbagi email).
        if ($email === '') {
            return redirect()->route('auth.superuser.sso')
                ->withErrors(['Akun Google tidak membagikan email. Izinkan akses email lalu coba lagi.']);
        }

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

            try {
                DB::transaction(function () use ($current, $googleId, $googleUser, $email) {
                    $current->google_id = $googleId;
                    $current->avatar = $googleUser->getAvatar() ?: $current->avatar;
                    // Kalau email lama random, ganti ke gmail asli sekalian.
                    if (strtolower(trim((string) $current->email)) !== $email) {
                        $current->email = $email;
                    }
                    $current->email_verified_at = $current->email_verified_at ?: now();
                    $current->google_linked_at = $current->google_linked_at ?: now();
                    $current->save();
                });
            } catch (\Throwable $e) {
                report($e);

                return redirect()->route('superuser.profile.index')
                    ->withErrors(['Gagal menghubungkan akun Google. Silakan coba lagi.']);
            }

            return redirect()->route('superuser.profile.index')
                ->with('status', 'Akun Google berhasil dihubungkan. Ke depan bisa login 1 klik.');
        }

        // 1. Sudah pernah link Google sebelumnya.
        $user = Superuser::where('google_id', $googleId)->first();

        // 2. Akun lama: email sama, google_id masih kosong -> link otomatis.
        if (!$user) {
            $user = Superuser::where('email', $email)->first();

            if ($user && empty($user->google_id)) {
                try {
                    DB::transaction(function () use ($user, $googleId, $googleUser) {
                        $user->google_id = $googleId;
                        $user->avatar = $googleUser->getAvatar();
                        $user->email_verified_at = $user->email_verified_at ?: now();
                        $user->google_linked_at = $user->google_linked_at ?: now();
                        $user->save();
                    });
                } catch (\Throwable $e) {
                    report($e);

                    return redirect()->route('auth.superuser.sso')
                        ->withErrors(['Gagal menautkan akun ke Google. Silakan coba lagi.']);
                }
            }
        }

        // 3. Pendaftar baru -> validasi domain lalu buat PENDING (is_active=0).
        if (!$user) {
            $allowedDomains = config('services.google.allowed_domains', []);
            $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));

            if (!empty($allowedDomains) && !in_array($domain, $allowedDomains, true)) {
                return redirect()->route('auth.superuser.sso')
                    ->withErrors(['Email ' . ($domain ?: '-') . ' tidak diizinkan. Gunakan Gmail atau email kantor.']);
            }

            try {
                $user = DB::transaction(function () use ($email, $name, $googleId, $googleUser) {
                    $base = strstr($email, '@', true) ?: 'user';
                    $base = preg_replace('/[^a-z0-9]+/i', '', strtolower($base)) ?: 'user';
                    $username = $base;
                    $counter = 1;
                    while (Superuser::where('username', $username)->exists()) {
                        $counter++;
                        $username = $base . $counter;
                    }

                    $newUser = new Superuser();
                    $newUser->username = $username;
                    $newUser->email = $email;
                    $newUser->name = $name;
                    $newUser->password = bcrypt(Str::random(32));
                    $newUser->google_id = $googleId;
                    $newUser->avatar = $googleUser->getAvatar();
                    $newUser->email_verified_at = now();
                    $newUser->google_linked_at = now();
                    $newUser->is_active = 0;
                    $newUser->is_superuser = 0;
                    $newUser->save();

                    return $newUser;
                });
            } catch (\Throwable $e) {
                report($e);

                return redirect()->route('auth.superuser.sso')
                    ->withErrors(['Pendaftaran gagal diproses. Silakan coba lagi atau hubungi admin.']);
            }

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

        // Catat login Google terakhir (untuk info di Profile).
        try {
            DB::transaction(function () use ($user) {
                $user->google_last_login_at = now();
                $user->save();
            });
        } catch (\Throwable $e) {
            report($e);
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
