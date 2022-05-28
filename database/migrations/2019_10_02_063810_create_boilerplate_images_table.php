<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoilerplateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boilerplate_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('boilerplate_id');
            $table->foreign('boilerplate_id')->references('id')->on('boilerplates')->onDelete('cascade');
            $table->string('image');
            
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
        Schema::dropIfExists('boilerplate_images');
    }
}
