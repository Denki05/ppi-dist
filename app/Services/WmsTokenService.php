<?php

namespace App\Services;

/**
 * Token API untuk project WMS (SPV Gudang).
 *
 * Memakai format dan kunci yang sama dengan handoff SSO modul
 * (MODULE_SSO_KEY), sehingga WMS tidak perlu menyimpan kunci sendiri.
 * Verifikasi dilakukan tanpa query database.
 */
class WmsTokenService
{
    public static function issue($user, $module = 'WMS')
    {
        $now = time();

        $payload = [
            'sub'      => (string) $user->id,
            'username' => (string) $user->username,
            'name'     => (string) $user->name,
            'email'    => (string) $user->email,
            'division' => (string) ($user->division ?? ''),
            'jabatan'  => (string) ($user->jabatan ?? ''),
            'module'   => strtoupper(trim((string) $module)),
            'iat'      => $now,
            'exp'      => $now + (int) config('wms.api_token_ttl', 28800),
            'nonce'    => bin2hex(random_bytes(8)),
        ];

        $body = self::base64UrlEncode(json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));

        return $body . '.' . hash_hmac('sha256', $body, self::key());
    }

    /**
     * @return array|null payload bila valid, null bila tidak.
     */
    public static function verify($token, $module = 'WMS')
    {
        $key = self::key();

        if ($key === '' || !is_string($token) || $token === '') {
            return null;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 2) {
            return null;
        }

        list($body, $signature) = $parts;

        if (!hash_equals(hash_hmac('sha256', $body, $key), $signature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($body), true);

        if (!is_array($payload)) {
            return null;
        }

        if (strtoupper((string) ($payload['module'] ?? '')) !== strtoupper(trim((string) $module))) {
            return null;
        }

        if ((int) ($payload['exp'] ?? 0) < time()) {
            return null;
        }

        return $payload;
    }

    public static function key()
    {
        return (string) config('modules.sso.key', '');
    }

    public static function base64UrlEncode($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public static function base64UrlDecode($value)
    {
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}
