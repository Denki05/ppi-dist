<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterSubBrandReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_sub_brand_references', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('brand_reference_id');
            $table->foreign('brand_reference_id')->references('id')->on('master_brand_references')->onDelete('cascade');

            $table->string('code')->unique();
            $table->string('name');
            $table->string('link')->nullable();
            $table->text('description')->nullable();

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
        Schema::dropIfExists('master_sub_brand_references');
    }
}
