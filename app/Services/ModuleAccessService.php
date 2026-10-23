<?php

namespace App\Services;

/**
 * Sumber tunggal untuk membaca hak akses modul user.
 *
 * Nilai modul_akses disimpan sebagai JSON array, contoh: ["MIS","AO"].
 * Akun superuser selalu dianggap punya semua modul.
 * Akun lama dengan modul_akses NULL memakai default config modules.legacy_default
 * supaya akses transaksi yang sudah berjalan tidak hilang.
 */
class ModuleAccessService
{
    public static function codes()
    {
        return array_keys(config('modules.items', []));
    }

    public static function normalize($value)
    {
        if (is_array($value)) {
            $decoded = $value;
        } elseif (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            if (!is_array($decoded)) {
                $decoded = preg_split('/[,|]/', $value);
            }
        } else {
            $decoded = [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        $allowed = self::codes();
        $result = [];

        foreach ($decoded as $code) {
            if (!is_scalar($code)) {
                continue;
            }

            $code = strtoupper(trim((string) $code));

            if (in_array($code, $allowed, true) && !in_array($code, $result, true)) {
                $result[] = $code;
            }
        }

        return $result;
    }

    /**
     * Modul efektif yang dimiliki user (termasuk fallback akun lama).
     *
     * @param  object|null  $user
     * @return array
     */
    public static function forUser($user)
    {
        if (!$user) {
            return [];
        }

        if ((int) ($user->is_superuser ?? 0) === 1) {
            return self::codes();
        }

        $raw = $user->modul_akses ?? null;

        if ($raw === null || $raw === '') {
            $modules = self::normalize(config('modules.legacy_default', ['TRS']));
        } else {
            $modules = self::normalize($raw);
        }

        if ((int) ($user->can_access_mis ?? 0) === 1 && !in_array('MIS', $modules, true)) {
            $modules[] = 'MIS';
        }

        return array_values(array_unique($modules));
    }

    public static function hasAccess($user, $module)
    {
        if (!$user || (int) ($user->is_active ?? 0) !== 1) {
            return false;
        }

        $module = strtoupper(trim((string) $module));

        return in_array($module, self::forUser($user), true);
    }

    public static function item($code)
    {
        $code = strtoupper(trim((string) $code));

        return config('modules.items.' . $code);
    }

    /**
     * Daftar item modul (kode + konfigurasi) untuk view.
     */
    public static function itemsForUser($user)
    {
        $items = [];

        foreach (self::forUser($user) as $code) {
            $item = self::item($code);

            if ($item) {
                $item['code'] = $code;
                $items[$code] = $item;
            }
        }

        return $items;
    }
}
