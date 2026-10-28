<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Membuat URL masuk modul beserta token SSO bertanda tangan HMAC.
 *
 * Token tidak disimpan di database. Modul tujuan memverifikasi signature
 * memakai MODULE_SSO_KEY yang sama, lalu mengecek masa berlaku token.
 */
class ModuleSsoService
{
    public static function urlFor($module, $user)
    {
        $module = strtoupper(trim((string) $module));
        $item = config('modules.items.' . $module);

        if (!$item) {
            return route('portal.modules');
        }

        if (!empty($item['internal_route'])) {
            return route($item['internal_route']);
        }

        $base = rtrim((string) $item['url'], '/');

        if (empty($item['sso_path'])) {
            return $base;
        }

        // Fail closed: key kosong membuat token bisa dipalsukan siapa saja.
        // Tanpa key, jangan kirim token; user masuk lewat halaman login modul.
        if ((string) config('modules.sso.key', '') === '') {
            Log::error('MODULE_SSO_KEY kosong; token SSO tidak dibuat.', ['module' => $module]);

            return $base;
        }

        $token = self::makeToken($module, $user);
        $separator = strpos($base, '?') === false ? '?' : '&';

        return $base . $item['sso_path'] . $separator . http_build_query(['sso_token' => $token]);
    }

    public static function makeToken($module, $user)
    {
        $key = (string) config('modules.sso.key', '');

        if ($key === '') {
            throw new \RuntimeException('MODULE_SSO_KEY belum diatur.');
        }

        if (strlen($key) < 32) {
            Log::warning('MODULE_SSO_KEY terlalu pendek; gunakan minimal 32 karakter acak.');
        }

        $now = time();

        $payload = [
            'sub'      => (string) $user->id,
            'username' => (string) $user->username,
            'name'     => (string) $user->name,
            'email'    => (string) $user->email,
            'module'   => strtoupper(trim((string) $module)),
            'iat'      => $now,
            'exp'      => $now + (int) config('modules.sso.ttl', 300),
            'nonce'    => bin2hex(random_bytes(8)),
        ];

        $body = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $signature = hash_hmac('sha256', $body, $key);

        return $body . '.' . $signature;
    }

    public static function base64UrlEncode($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
