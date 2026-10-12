<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Batal by AO: estimate di AO dihapus, pengajuan + mutasi DITAHAN.
// Estimate baru yang cocok (nama+HP+AO) langsung auto-approve pakai
// mutasi lama, tanpa pengajuan/mutasi ulang.
class AddBatalAoToPengajuanProformaTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('pengajuan_proforma', 'tunggu_estimate_baru')) {
            Schema::table('pengajuan_proforma', function (Blueprint $table) {
                $table->boolean('tunggu_estimate_baru')->default(false)->after('revisi_ao');
            });
        }
        if (!Schema::hasColumn('pengajuan_proforma', 'estimate_lama')) {
            Schema::table('pengajuan_proforma', function (Blueprint $table) {
                $table->string('estimate_lama', 100)->nullable()->after('tunggu_estimate_baru');
            });
        }
        if (!Schema::hasColumn('pengajuan_proforma', 'estimate_baru')) {
            Schema::table('pengajuan_proforma', function (Blueprint $table) {
                $table->string('estimate_baru', 100)->nullable()->after('estimate_lama');
            });
        }
    }

    public function down()
    {
        foreach (['estimate_baru', 'estimate_lama', 'tunggu_estimate_baru'] as $col) {
            if (Schema::hasColumn('pengajuan_proforma', $col)) {
                Schema::table('pengajuan_proforma', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
}
