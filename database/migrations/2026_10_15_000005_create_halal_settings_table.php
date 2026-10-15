<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHalalSettingsTable extends Migration
{
    /**
     * Flag internal fitur halal (mis. konfirmasi peta merek).
     * Tabel sendiri agar tidak tergantung tabel `settings`
     * yang belum tentu ada di semua DB (mis. ppi_araya).
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('halal_settings')) {
            Schema::create('halal_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('halal_settings');
    }
}
