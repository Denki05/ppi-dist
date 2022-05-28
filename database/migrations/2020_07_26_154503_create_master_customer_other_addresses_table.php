<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterCustomerOtherAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_customer_other_addresses', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('master_customers')->onDelete('cascade');

            $table->string('label');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->text('address');

            $table->string('gps_latitude')->nullable();
            $table->string('gps_longitude')->nullable();

            $table->string('provinsi')->nullable();
            $table->string('kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();

            $table->string('text_provinsi')->nullable();
            $table->string('text_kota')->nullable();
            $table->string('text_kecamatan')->nullable();
            $table->string('text_kelurahan')->nullable();

            $table->string('zipcode')->nullable();

            $table->integer('status');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_customer_other_addresses');
    }
}
