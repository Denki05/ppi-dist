<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnOngkirToPenjualanSo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            $table->unsignedBigInteger('ekspedisi_id')->nullable();
            $table->foreign('ekspedisi_id')
                  ->references('id')
                  ->on('master_ekspedisi')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            $table->dropForeign(['ekspedisi_id']);
            $table->dropColumn(['ekspedisi_id']);
        });
    }
}
