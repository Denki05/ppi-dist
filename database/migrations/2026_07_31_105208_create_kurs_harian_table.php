<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKursHarianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kurs_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('jam_efektif');
            $table->decimal('nilai_kurs', 15, 2);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['tanggal', 'jam_efektif']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kurs_harian');
    }
}
