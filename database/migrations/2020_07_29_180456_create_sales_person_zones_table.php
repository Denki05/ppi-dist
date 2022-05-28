<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesPersonZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_person_zones', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('sales_person_id');
            $table->foreign('sales_person_id')->references('id')->on('sales_persons')->onDelete('cascade');

            $table->string('provinsi')->nullable();
            $table->string('kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();

            $table->string('text_provinsi')->nullable();
            $table->string('text_kota')->nullable();
            $table->string('text_kecamatan')->nullable();
            $table->string('text_kelurahan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_person_zones');
    }
}
