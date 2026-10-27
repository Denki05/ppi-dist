<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddAoOrderNumberToPenjualanSoTable extends Migration
{
    public function up()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualan_so', 'ao_order_number')) {
                // Nomor order AO asal (kunci idempotensi kiriman dari modul AO)
                $table->string('ao_order_number', 50)->nullable()->after('so_code');
                $table->index('ao_order_number', 'idx_penjualan_so_ao_order_number');
            }
        });

        // Backfill dari penanda [AO:xxx] di kolom note (data lama)
        DB::statement("UPDATE penjualan_so
            SET ao_order_number = SUBSTRING_INDEX(SUBSTRING_INDEX(note, '[AO:', -1), ']', 1)
            WHERE ao_order_number IS NULL AND note LIKE '%[AO:%]%'");
    }

    public function down()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            if (Schema::hasColumn('penjualan_so', 'ao_order_number')) {
                $table->dropIndex('idx_penjualan_so_ao_order_number');
                $table->dropColumn('ao_order_number');
            }
        });
    }
}