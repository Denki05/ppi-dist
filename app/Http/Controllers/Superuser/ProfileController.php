<?php

namespace App\Http\Controllers\Superuser;

use App\Entities\Account\Superuser;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use App\Services\ModuleAccessService;
use App\Services\UserSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Validator;

class ProfileController extends Controller
{
    public function index()
    {
        $superuser = Auth::guard('superuser')->user();

        // Profil versi transaksi hanya untuk user yang punya akses TRS.
        // User modul lain (mis. Design/DSG) memakai profil mandiri di portal.
        if (!$superuser || !ModuleAccessService::hasAccess($superuser, 'TRS')) {
            return redirect()->route('portal.profile');
        }

        return view('superuser.profile.index');
    }

    /**
     * Profil mandiri di portal: bisa dibuka tanpa akses transaksi.
     */
    public function portal()
    {
        return view('superuser.portal.profile', [
            'superuser' => Auth::guard('superuser')->user(),
        ]);
    }

    public function update(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('superuser.profile.index');
        }

        $validator = Validator::make($request->all(), [
            'name'             => 'nullable|string',
            'email'            => 'nullable|email|max:255',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_password' => 'nullable|string',
            'new_password'     => 'nullable|min:8|max:16|confirmed',
        ]);

        if ($validator->fails()) {
            $response['notification'] = [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => $validator->errors()->all(),
            ];

            return $this->response(400, $response);
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        if ($superuser === null) {
            abort(404);
        }

        $newEmail = strtolower(trim((string) $request->input('email', '')));
        $emailChanged = $newEmail !== '' && $newEmail !== strtolower(trim((string) $superuser->email));
        $wantsPasswordChange = $request->filled('new_password');

        // Email dan password adalah perubahan sensitif: wajib verifikasi password.
        if (($emailChanged || $wantsPasswordChange) && !$this->passwordMatches($request, $superuser)) {
            return $this->response(403, [
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Password saat ini wajib diisi dan harus benar untuk mengubah email/password.',
                ],
            ]);
        }

        $superuser->name = $request->input('name');

        // Update email (akun lama pakai email random -> ganti ke gmail asli).
        // Email berubah = tautan Google & verifikasi lama dilepas, sesi auto-login lain dicabut.
        if ($emailChanged) {
            $exists = Superuser::where('email', $newEmail)
                ->where('id', '!=', $superuser->id)
                ->exists();

            if ($exists) {
                return $this->response(400, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Email sudah dipakai user lain.',
                    ],
                ]);
            }

            $superuser->email = $newEmail;
            $superuser->email_verified_at = null;
            $superuser->google_id = null;
            $superuser->google_linked_at = null;
            $superuser->remember_token = null;
        }

        if (!empty($request->file('image'))) {
            if (is_file_exists(Superuser::$directory_image.$superuser->image)) {
                remove_file(Superuser::$directory_image.$superuser->image);
            }

            $superuser->image = UploadMedia::image($request->file('image'), Superuser::$directory_image);
        }

        if ($wantsPasswordChange) {
            $superuser->password = Hash::make($request->input('new_password'));
            $superuser->remember_token = null;
        }

        if ($superuser->save()) {
            // Sinkronkan perubahan profil / ganti password ke DB trial.
            UserSyncService::push($superuser);

            if ($emailChanged) {
                $message = 'Email diperbarui. Silakan hubungkan ulang Google di bawah agar bisa login 1 klik.';
            } elseif ($wantsPasswordChange) {
                $message = 'Password diperbarui. Perangkat lain yang memakai auto-login perlu login ulang.';
            } else {
                $message = 'Success';
            }

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => $message,
            ];

            $response['redirect_to'] = 'reload()';

            return $this->response(200, $response);
        }

        return $this->response(500, [
            'notification' => [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => 'Gagal menyimpan perubahan.',
            ],
        ]);
    }

    /**
     * Mulai proses hubungkan Google: wajib verifikasi password dulu.
     * Hasil verifikasi disimpan di session sebagai izin sekali pakai (5 menit).
     */
    public function startGoogleLink(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('superuser.profile.index');
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        if ($superuser === null) {
            abort(404);
        }

        if (!$this->passwordMatches($request, $superuser)) {
            return $this->response(403, [
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Password saat ini wajib diisi dan harus benar untuk menghubungkan Google.',
                ],
            ]);
        }

        if (!empty($superuser->google_id)) {
            return $this->response(400, [
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Akun sudah terhubung Google. Lepas dulu jika ingin mengganti akun Google.',
                ],
            ]);
        }

        $request->session()->put('google_link_user_id', $superuser->id);
        $request->session()->put('google_link_expires_at', now()->addMinutes(5)->timestamp);

        Log::info('SSO Google link started', [
            'user_id'  => $superuser->id,
            'username' => $superuser->username,
        ]);

        return $this->response(200, [
            'notification' => [
                'alert' => 'notify',
                'type' => 'info',
                'content' => 'Password benar. Anda akan diarahkan ke Google untuk memilih akun.',
            ],
            'redirect_to' => route('auth.superuser.sso.redirect'),
        ]);
    }

    /**
     * Lepas hubungan Google (misal ganti gmail). Wajib verifikasi password.
     */
    public function unlinkGoogle(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('superuser.profile.index');
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        if ($superuser === null) {
            abort(404);
        }

        if (!$this->passwordMatches($request, $superuser)) {
            return $this->response(403, [
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Password saat ini wajib diisi dan harus benar untuk melepas Google.',
                ],
            ]);
        }

        $superuser->google_id = null;
        $superuser->google_linked_at = null;
        $superuser->email_verified_at = null;
        $superuser->save();

        Log::info('SSO Google unlinked', [
            'user_id'  => $superuser->id,
            'username' => $superuser->username,
        ]);

        UserSyncService::push($superuser);

        $response['notification'] = [
            'alert' => 'notify',
            'type' => 'success',
            'content' => 'Hubungan Google dilepas. Login password tetap bisa. Hubungkan lagi kapan saja.',
        ];
        $response['redirect_to'] = 'reload()';

        return $this->response(200, $response);
    }

    protected function passwordMatches(Request $request, $superuser)
    {
        $currentPassword = (string) $request->input('current_password', '');

        return $currentPassword !== ''
            && Hash::check($currentPassword, $superuser->getAuthPassword());
    }

    /**
     * Logout dari semua perangkat lain dengan mereset remember_token.
     * Semua cookie auto-login ("Ingat saya") di perangkat lain jadi tidak valid.
     * Session perangkat ini tetap aktif.
     */
    public function logoutAllDevices(Request $request)
    {
        if ($request->ajax()) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());

            if ($superuser === null) {
                abort(404);
            }

            $superuser->remember_token = null;
            $superuser->save();

            // Rotasi ID session perangkat ini agar lebih aman, tetap login.
            $request->session()->regenerate();

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Auto-login dimatikan di semua perangkat. Perangkat ini tetap aktif.',
            ];
            $response['redirect_to'] = 'reload()';

            return $this->response(200, $response);
        }
    }
}
