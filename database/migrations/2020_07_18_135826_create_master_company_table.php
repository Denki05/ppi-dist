<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_company', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');

            $table->text('address');
            $table->string('provinsi')->nullable();
            $table->string('kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();

            $table->string('text_provinsi')->nullable();
            $table->string('text_kota')->nullable();
            $table->string('text_kecamatan')->nullable();
            $table->string('text_kelurahan')->nullable();

            $table->string('zipcode')->nullable();

            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('logo')->nullable();

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
        Schema::dropIfExists('master_company');
    }
}
