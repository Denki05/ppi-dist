<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstimateCodeToPenjualanSoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            $table->string('estimate_code', 20)->nullable()->after('so_code');
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
            $table->dropColumn('estimate_code');
        });
    }
}
