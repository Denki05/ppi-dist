<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_contacts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('position')->nullable();
            $table->date('dob')->nullable();
            $table->string('npwp')->nullable();
            $table->string('ktp')->nullable();
            $table->text('address')->nullable();

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
        Schema::dropIfExists('master_contacts');
    }
}
