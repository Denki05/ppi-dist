<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Penerima sinkronisasi user dari trans (master) di server TRIAL.
 * POST /api/superusers/sync  (middleware trial.sync)
 *
 * Upsert ke tabel superusers berdasarkan username (fallback email).
 * Payload dikirim trans via UserSyncService (bcrypt hash ikut, agar
 * modul lain bisa password_verify sendiri).
 */
class UserSyncController extends Controller
{
    /** Kolom yang boleh ditulis (whitelist). */
    private $writable = [
        'username', 'name', 'email', 'password', 'division', 'cabang',
        'is_superuser', 'is_active', 'modul_akses', 'can_access_mis',
        'google_id', 'avatar', 'email_verified_at', 'google_linked_at',
        'google_last_login_at',
    ];

    public function upsert(Request $request)
    {
        $data = $request->all();

        $username = strtolower(trim((string) ($data['username'] ?? '')));
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        if ($username === '' || $email === '') {
            return response()->json([
                'success' => false,
                'message' => 'Field username & email wajib dikirim.',
            ], 422);
        }

        $existing = DB::table('superusers')->where('username', $username)->first();
        if (!$existing) {
            $existing = DB::table('superusers')->where('email', $email)->first();
        }

        $attrs = [];
        foreach ($this->writable as $col) {
            if (array_key_exists($col, $data) && Schema::hasColumn('superusers', $col)) {
                $attrs[$col] = $data[$col];
            }
        }

        if (array_key_exists('email', $attrs)) {
            $attrs['email'] = strtolower(trim((string) $attrs['email']));
        }

        // Normalisasi modul_akses + turunkan can_access_mis.
        if (array_key_exists('modul_akses', $data)) {
            $mods = is_array($data['modul_akses'])
                ? $data['modul_akses']
                : json_decode((string) $data['modul_akses'], true);
            $mods = is_array($mods)
                ? array_values(array_intersect($mods, ['MIS', 'CRM', 'AO', 'APM', 'TRS', 'DSG']))
                : [];

            if (Schema::hasColumn('superusers', 'modul_akses')) {
                $attrs['modul_akses'] = json_encode($mods);
            }

            if (!array_key_exists('can_access_mis', $data) && Schema::hasColumn('superusers', 'can_access_mis')) {
                $attrs['can_access_mis'] = in_array('MIS', $mods, true) ? 1 : 0;
            }
        }

        // Email berubah: tautan Google lama tidak berlaku lagi, sesi auto-login dicabut.
        if ($existing) {
            $incomingEmail = strtolower(trim((string) ($attrs['email'] ?? '')));

            if ($incomingEmail !== '' && $incomingEmail !== strtolower(trim((string) $existing->email))) {
                foreach (['google_id', 'google_linked_at', 'email_verified_at', 'remember_token'] as $col) {
                    if (Schema::hasColumn('superusers', $col)) {
                        $attrs[$col] = null;
                    }
                }
            }

            if (array_key_exists('password', $attrs) && $attrs['password'] !== $existing->password) {
                if (Schema::hasColumn('superusers', 'remember_token')) {
                    $attrs['remember_token'] = null;
                }
            }
        }

        $attrs['updated_at'] = now();

        try {
            $id = DB::transaction(function () use ($existing, $attrs, $username, $email) {
                if ($existing) {
                    DB::table('superusers')->where('id', $existing->id)->update($attrs);
                    return $existing->id;
                }

                $attrs['username'] = $username;
                $attrs['email'] = $email;
                $attrs['created_at'] = now();

                if (!array_key_exists('password', $attrs)) {
                    $attrs['password'] = bcrypt(Str::random(32));
                }
                if (!array_key_exists('is_active', $attrs)) {
                    $attrs['is_active'] = 0;
                }
                if (!array_key_exists('is_superuser', $attrs)) {
                    $attrs['is_superuser'] = 0;
                }
                if (!array_key_exists('name', $attrs)) {
                    $attrs['name'] = $username;
                }

                return DB::table('superusers')->insertGetId($attrs);
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan user.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'action'  => $existing ? 'updated' : 'created',
            'id'      => $id,
        ]);
    }
}
