<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOriginWarehouseIdPenjualanSoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            $table->bigInteger('origin_warehouse_id')->nullable()->change();
            $table->bigInteger('type_transaction')->nullable()->change();
            $table->bigInteger('so_for')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
