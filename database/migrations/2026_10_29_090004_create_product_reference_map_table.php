<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductReferenceMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_reference_map', function (Blueprint $table) {
            $table->bigIncrements('id');

            // master_products.id bertipe varchar(50) di database ini, bukan auto increment
            $table->string('product_id', 50)->unique();
            $table->foreign('product_id')->references('id')->on('master_products')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('fragrance_reference_id');
            $table->foreign('fragrance_reference_id')->references('id')->on('fragrance_references')->onDelete('restrict')->onUpdate('cascade');

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
        Schema::dropIfExists('product_reference_map');
    }
}
