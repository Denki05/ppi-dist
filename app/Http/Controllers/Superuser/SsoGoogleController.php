<?php

namespace App\Http\Controllers\Superuser;

use App\Entities\Account\Superuser;
use App\Http\Controllers\Controller;
use App\Services\ModuleAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            return redirect()->route('portal.modules');
        }

        return view('superuser.sso');
    }

    /**
     * Lempar ke Google.
     */
    public function redirect()
    {
        try {
            return Socialite::driver('google')
                ->with(['prompt' => 'select_account'])
                ->redirect();
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
     * - email cocok, google_id kosong, akun aktif + email terverifikasi -> link otomatis.
     * - email cocok tapi sudah terhubung ke google_id lain -> ditolak (cegah takeover).
     * - email cocok tapi email lokal belum terverifikasi -> login password dulu.
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

        $raw = method_exists($googleUser, 'getRaw') ? (array) $googleUser->getRaw() : [];
        $googleId = trim((string) $googleUser->getId());
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $name = $googleUser->getName() ?: $email;
        $emailVerified = filter_var($raw['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($googleId === '') {
            Log::warning('SSO Google ditolak: Google ID kosong', ['email' => $email]);

            return redirect()->route('auth.superuser.sso')
                ->withErrors(['Akun Google tidak mengirim ID yang valid. Coba lagi.']);
        }

        // Email wajib ada dari Google (kalau user tidak berbagi email).
        if ($email === '') {
            return redirect()->route('auth.superuser.sso')
                ->withErrors(['Akun Google tidak membagikan email. Izinkan akses email lalu coba lagi.']);
        }

        // Email Google wajib sudah terverifikasi agar aman dipakai menautkan akun.
        if (!$emailVerified) {
            Log::warning('SSO Google ditolak: email belum terverifikasi', ['email' => $email]);

            return redirect()->route('auth.superuser.sso')
                ->withErrors(['Email Google belum terverifikasi. Verifikasi email Google Anda lalu coba lagi.']);
        }

        // Mode hubungkan: wajib step-up password lewat tombol "Hubungkan" di Profile.
        // Tautan Google yang dibuka paksa tidak bisa menempel ke akun yang login.
        if (Auth::guard('superuser')->check()) {
            $current = Auth::guard('superuser')->user();

            if (!$this->hasValidLinkIntent($request, $current->id)) {
                Log::warning('SSO Google link ditolak: tanpa step-up password', ['user_id' => $current->id]);

                return redirect()->route('superuser.profile.index')
                    ->withErrors(['Verifikasi password dulu sebelum menghubungkan Google. Buka Profile lalu klik Hubungkan.']);
            }

            // Sekali pakai: intent dihapus walau proses di bawah gagal.
            $this->clearLinkIntent($request);

            $allowedDomains = config('services.google.allowed_domains', []);
            $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));

            if (!empty($allowedDomains) && !in_array($domain, $allowedDomains, true)) {
                return redirect()->route('superuser.profile.index')
                    ->withErrors(['Email ' . ($domain ?: '-') . ' tidak diizinkan untuk login Google.']);
            }

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
                    $current->email_verified_at = now();
                    $current->google_linked_at = now();
                    $current->save();
                });
            } catch (\Throwable $e) {
                report($e);

                return redirect()->route('superuser.profile.index')
                    ->withErrors(['Gagal menghubungkan akun Google. Silakan coba lagi.']);
            }

            Log::info('SSO Google linked', [
                'user_id'  => $current->id,
                'username' => $current->username,
            ]);

            return redirect()->route('superuser.profile.index')
                ->with('status', 'Akun Google berhasil dihubungkan. Ke depan bisa login 1 klik.');
        }

        // 1. Sudah pernah link Google sebelumnya.
        $user = Superuser::where('google_id', $googleId)->first();

        // 2. Akun lama: email sama. Auto-link HANYA jika akun aktif dan email
        // lokal sudah terverifikasi. Selain itu minta login password dulu.
        if (!$user) {
            $emailOwner = Superuser::where('email', $email)->first();

            if ($emailOwner) {
                if (!empty($emailOwner->google_id)) {
                    Log::warning('SSO Google ditolak: email sudah terhubung Google lain', [
                        'email'   => $email,
                        'user_id' => $emailOwner->id,
                    ]);

                    return redirect()->route('auth.superuser.sso')
                        ->withErrors(['Email ini sudah terhubung ke akun Google lain. Login memakai akun Google yang benar atau hubungi admin.']);
                }

                if (!$emailOwner->is_active) {
                    return redirect()->route('auth.superuser.sso')
                        ->withErrors(['Akun belum aktif. Hubungi admin untuk verifikasi.']);
                }

                if ($emailOwner->email_verified_at === null) {
                    Log::info('SSO Google auto-link ditunda: email lokal belum terverifikasi', [
                        'user_id' => $emailOwner->id,
                    ]);

                    return redirect()->route('auth.superuser.index')
                        ->withErrors(['Akun dengan email ini sudah ada. Login dulu memakai password, lalu hubungkan Google dari halaman Profile.']);
                }

                try {
                    DB::transaction(function () use ($emailOwner, $googleId, $googleUser) {
                        $emailOwner->google_id = $googleId;
                        $emailOwner->avatar = $googleUser->getAvatar();
                        $emailOwner->email_verified_at = $emailOwner->email_verified_at ?: now();
                        $emailOwner->google_linked_at = now();
                        $emailOwner->save();
                    });

                    Log::info('SSO Google auto-linked', ['user_id' => $emailOwner->id]);
                } catch (\Throwable $e) {
                    report($e);

                    return redirect()->route('auth.superuser.sso')
                        ->withErrors(['Gagal menautkan akun ke Google. Silakan coba lagi.']);
                }

                $user = $emailOwner;
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

        Log::info('SSO Google login', [
            'user_id'  => $user->id,
            'username' => $user->username,
        ]);

        // Portal modul bisa belum terpasang saat deploy bertahap.
        // Tanpa service/route portal, login tetap diarahkan ke dashboard.
        $modulePortalReady = class_exists(ModuleAccessService::class)
            && \Illuminate\Support\Facades\Route::has('portal.modules');

        if (!$modulePortalReady) {
            return redirect()->route('superuser.index');
        }

        $modules = ModuleAccessService::forUser($user);

        // Gateway selalu tampil agar user sempat cek profil / tautan Google.
        if (count($modules) === 0) {
            return redirect()->route('portal.modules')
                ->with('error', 'Akun Anda belum memiliki akses modul. Hubungi admin untuk mendapatkan akses.');
        }

        return redirect()->route('portal.modules');
    }

    /**
     * Cek izin menautkan Google: hanya sesi yang baru saja verifikasi password.
     */
    protected function hasValidLinkIntent(Request $request, $userId = null)
    {
        $intentUser = (int) $request->session()->get('google_link_user_id', 0);
        $expiresAt  = (int) $request->session()->get('google_link_expires_at', 0);

        if ($intentUser <= 0 || $expiresAt < time()) {
            return false;
        }

        if ($userId !== null && $intentUser !== (int) $userId) {
            return false;
        }

        return true;
    }

    protected function clearLinkIntent(Request $request)
    {
        $request->session()->forget(['google_link_user_id', 'google_link_expires_at']);
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
