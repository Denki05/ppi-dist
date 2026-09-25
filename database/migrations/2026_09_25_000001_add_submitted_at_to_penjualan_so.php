<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSubmittedAtToPenjualanSo extends Migration
{
    public function up()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            // Waktu SO diajukan/dilanjutkan ke admin (status -> LANJUTAN).
            // Dipakai sebagai basis tanggal tab SO Lanjutan (ganti created_at).
            $table->timestamp('submitted_at')->nullable()->after('status');
        });

        // Backfill best-effort untuk data lama: pakai updated_at
        // (waktu save terakhir, mendekati waktu submit/tutup),
        // fallback created_at bila updated_at kosong.
        DB::table('penjualan_so')
            ->whereIn('status', [2, 4])
            ->whereNull('submitted_at')
            ->update(['submitted_at' => DB::raw('COALESCE(updated_at, created_at)')]);
    }

    public function down()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
}
