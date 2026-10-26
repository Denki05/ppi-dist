<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeKtpNullableInPengajuanProformaTable extends Migration
{
    /**
     * Kontrak API pengajuan menyatakan ktp nullable, tetapi tabel v1 membuatnya
     * NOT NULL. MySQL sql_mode strict (production) menolak NULL sehingga setiap
     * pengajuan dari AO tanpa KTP gagal dengan 1048 -> HTTP 500.
     */
    public function up()
    {
        if (!Schema::hasTable('pengajuan_proforma') || !Schema::hasColumn('pengajuan_proforma', 'ktp')) {
            return;
        }

        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            $table->string('ktp', 20)->nullable()->change();
        });
    }

    public function down()
    {
        if (!Schema::hasTable('pengajuan_proforma') || !Schema::hasColumn('pengajuan_proforma', 'ktp')) {
            return;
        }

        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            $table->string('ktp', 20)->change();
        });
    }
}
