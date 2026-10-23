<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Akun lama belum punya modul_akses. Beri default TRS agar akses transaksi
 * yang sudah berjalan tidak hilang saat guard modul diaktifkan.
 * Admin tetap bisa menambah/mengurangi lewat Edit User atau Checklist Modul.
 */
class BackfillModulAksesDefaultTrs extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('superusers', 'modul_akses')) {
            return;
        }

        $hasMisFlag = Schema::hasColumn('superusers', 'can_access_mis');

        $query = DB::table('superusers')
            ->where('is_superuser', 0)
            ->where(function ($sub) {
                $sub->whereNull('modul_akses')->orWhere('modul_akses', '');
            });

        $columns = ['id', 'modul_akses'];

        if ($hasMisFlag) {
            $columns[] = 'can_access_mis';
        }

        $users = $query->get($columns);

        foreach ($users as $user) {
            $modules = ['TRS'];

            if ($hasMisFlag && (int) ($user->can_access_mis ?? 0) === 1) {
                $modules[] = 'MIS';
            }

            DB::table('superusers')
                ->where('id', $user->id)
                ->update(['modul_akses' => json_encode(array_values(array_unique($modules)))]);
        }
    }

    public function down()
    {
        // Tidak dikembalikan ke NULL: data akses yang sudah disesuaikan admin
        // tidak boleh hilang hanya karena rollback migrasi.
    }
}
