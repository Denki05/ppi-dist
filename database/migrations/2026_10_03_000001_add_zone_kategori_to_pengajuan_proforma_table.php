<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZoneKategoriToPengajuanProformaTable extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            // Zone area (disamakan dengan master_customers.zone / ZONING member)
            if (!Schema::hasColumn('pengajuan_proforma', 'zone')) {
                $table->string('zone', 100)->nullable()->after('kelurahan');
            }
            // Kategori customer (nama master_customer_categories, di-resolve ke
            // category_id saat mutasi agar ikut masuk ke customer existing)
            if (!Schema::hasColumn('pengajuan_proforma', 'kategori')) {
                $table->string('kategori', 100)->nullable()->after('zone');
            }
        });
    }

    public function down()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            $drop = [];
            foreach (['zone', 'kategori'] as $col) {
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
