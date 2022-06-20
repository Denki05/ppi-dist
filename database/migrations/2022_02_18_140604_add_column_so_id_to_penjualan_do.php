<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSoIdToPenjualanDo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan_do', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable();

            $table->foreign('so_id')
                  ->references('id')
                  ->on('penjualan_so')
                  ->onDelete('restrict')
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
        Schema::table('penjualan_do', function (Blueprint $table) {
            $table->dropColumn(['so_id']);
        });
    }
}
