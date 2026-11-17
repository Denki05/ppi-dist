<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryCostIdrToPenjualanSoTable extends Migration
{
    public function up()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualan_so', 'delivery_cost_idr')) {
                // Ongkir dari modul AO; dipakai saat proforma dibuat otomatis.
                $table->decimal('delivery_cost_idr', 15, 2)->default(0)->after('disc_kemasan');
            }
        });
    }

    public function down()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            if (Schema::hasColumn('penjualan_so', 'delivery_cost_idr')) {
                $table->dropColumn('delivery_cost_idr');
            }
        });
    }
}
