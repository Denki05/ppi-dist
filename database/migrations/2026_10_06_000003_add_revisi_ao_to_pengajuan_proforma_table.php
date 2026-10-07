<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Revisi pasca-mutasi: penanda pengajuan sedang direvisi di AO.
// Update proforma dari AO hanya diterima bila flag ini true.
class AddRevisiAoToPengajuanProformaTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('pengajuan_proforma', 'revisi_ao')) {
            Schema::table('pengajuan_proforma', function (Blueprint $table) {
                $table->boolean('revisi_ao')->default(false)->after('member_dibuat_baru');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('pengajuan_proforma', 'revisi_ao')) {
            Schema::table('pengajuan_proforma', function (Blueprint $table) {
                $table->dropColumn('revisi_ao');
            });
        }
    }
}
