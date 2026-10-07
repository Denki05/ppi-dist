<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// File bukti capture (1-3) dari AO disimpan di transaksi agar bisa dilihat
// admin saat verifikasi (JSON daftar path storage local).
class AddBuktiListToPengajuanProformaTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('pengajuan_proforma', 'bukti_list')) {
            Schema::table('pengajuan_proforma', function (Blueprint $table) {
                $table->text('bukti_list')->nullable()->after('bukti_ada');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('pengajuan_proforma', 'bukti_list')) {
            Schema::table('pengajuan_proforma', function (Blueprint $table) {
                $table->dropColumn('bukti_list');
            });
        }
    }
}
