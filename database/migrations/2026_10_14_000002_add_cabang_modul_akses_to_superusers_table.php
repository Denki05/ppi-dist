<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCabangModulAksesToSuperusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->string('cabang', 100)->nullable()->after('division');
            // Daftar modul yang boleh dibuka: MIS, AO, APM, TRS (transaksi), DSG (design).
            // Disimpan JSON, contoh: ["MIS","AO"].
            $table->text('modul_akses')->nullable()->after('cabang');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->dropColumn(['cabang', 'modul_akses']);
        });
    }
}
