<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBuktiListToPenjualanSoTable extends Migration
{
    public function up()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualan_so', 'bukti_list')) {
                // JSON path foto bukti chat yang dikirim modul AO saat order CASH.
                $table->text('bukti_list')->nullable()->after('ao_order_number');
            }
        });
    }

    public function down()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            if (Schema::hasColumn('penjualan_so', 'bukti_list')) {
                $table->dropColumn('bukti_list');
            }
        });
    }
}
