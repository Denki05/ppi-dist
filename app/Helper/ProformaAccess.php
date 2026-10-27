<?php

namespace App\Helper;

/**
 * Paket A poin 10: Revisi & Batal proforma hanya untuk
 *  - admin sales (whitelist ID superuser, default: 33), atau
 *  - divisi Management (pimpinan).
 *
 * Superuser/developer lolos seperti pola lama. Daftar ID diatur via
 * .env PROFORMA_REVISI_IDS (koma, mis. "33,41") tanpa perlu coding ulang.
 */
class ProformaAccess
{
    public static function revisiIds(): array
    {
        $raw = (string) (config('proforma.revisi_ids', env('PROFORMA_REVISI_IDS', '33')));
        return array_values(array_filter(array_map(function ($v) {
            return (int) trim($v);
        }, explode(',', $raw))));
    }

    public static function canRevisiBatal($user = null): bool
    {
        if (!$user) {
            return false;
        }
        if (!empty($user->is_superuser)) {
            return true;
        }
        if (in_array((int) ($user->id ?? 0), self::revisiIds(), true)) {
            return true;
        }
        if (method_exists($user, 'hasRole') && $user->hasRole(['Developer', 'SuperAdmin', 'Management', 'Manajemen'])) {
            return true;
        }
        return in_array($user->division ?? '', ['Management', 'Manajemen'], true);
    }
}
