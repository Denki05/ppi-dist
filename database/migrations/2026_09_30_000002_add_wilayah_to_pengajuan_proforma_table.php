<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWilayahToPengajuanProformaTable extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            // Wilayah lengkap agar verifikasi admin sales sudah terisi
            if (!Schema::hasColumn('pengajuan_proforma', 'provinsi')) {
                $table->string('provinsi', 100)->nullable()->after('city');
            }
            if (!Schema::hasColumn('pengajuan_proforma', 'kecamatan')) {
                $table->string('kecamatan', 100)->nullable()->after('provinsi');
            }
            if (!Schema::hasColumn('pengajuan_proforma', 'kelurahan')) {
                $table->string('kelurahan', 100)->nullable()->after('kecamatan');
            }
        });
    }

    public function down()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            $drop = [];
            foreach (['provinsi', 'kecamatan', 'kelurahan'] as $col) {
                if (Schema::hasColumn('pengajuan_proforma', $col)) {
                    $drop[] = $col;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
}
