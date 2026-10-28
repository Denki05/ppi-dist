<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Push data user dari trans (master) ke trial (pusat user) — best-effort.
 * Dipanggil SETELAH commit pada: approve pendaftar, ubah user (ganti
 * password/profil), non-aktifkan (destroy), aktifkan (restore), dan
 * saat user mengganti password sendiri.
 *
 * Konfigurasi (.env di server trans):
 *   TRIAL_SYNC_URL=https://lssoft88.xyz/api/superusers/sync
 *   TRIAL_SYNC_KEY=<secret>
 *
 * Jika TRIAL_SYNC_URL kosong -> dilewati diam-diam (mis. di lokal).
 */
class UserSyncService
{
    /**
     * @param  \App\Entities\Account\User  $user
     * @return void
     */
    public static function push($user)
    {
        $url = config('services.trial.sync_url');
        if (!$url || empty($user)) {
            return;
        }

        $modul = [];
        if (!empty($user->modul_akses)) {
            $decoded = is_array($user->modul_akses)
                ? $user->modul_akses
                : json_decode((string) $user->modul_akses, true);
            $modul = is_array($decoded) ? array_values($decoded) : [];
        }

        $payload = [
            'username'          => $user->username,
            'name'              => $user->name,
            'email'             => $user->email,
            'password'          => $user->password,
            'division'          => $user->division,
            'jabatan'           => $user->jabatan ?? null,
            'cabang'            => $user->cabang ?? null,
            'is_superuser'      => (int) ($user->is_superuser ?? 0),
            'is_active'         => (int) ($user->is_active ?? 0),
            'modul_akses'       => $modul,
            'can_access_mis'    => in_array('MIS', $modul, true) ? 1 : 0,
            'google_id'         => $user->google_id ?? null,
            'avatar'            => $user->avatar ?? null,
            'email_verified_at' => $user->email_verified_at ?? null,
            'google_linked_at'  => $user->google_linked_at ?? null,
        ];

        try {
            $client = new \GuzzleHttp\Client([
                'timeout'         => 5,
                'connect_timeout' => 3,
                'verify'          => (bool) config('services.trial.verify_tls', true),
                'http_errors'     => false,
            ]);
            $res = $client->post($url, [
                'json'    => $payload,
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-API-KEY'    => config('services.trial.key'),
                ],
            ]);
            Log::info('User sync pushed', [
                'username' => $user->username,
                'http'     => $res->getStatusCode(),
            ]);
        } catch (\Throwable $e) {
            // Best-effort: perubahan user di trans tidak boleh gagal karena trial down.
            Log::warning('User sync push gagal (diabaikan)', [
                'username' => $user->username ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
