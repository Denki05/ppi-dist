<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualanCanvasingItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan_canvasing_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('canvasing_id');
            $table->integer('product_id');
            $table->decimal('qty', 16, 4)->default(0);
            $table->timestamps();

            $table->foreign('canvasing_id')
                  ->references('id')
                  ->on('penjualan_canvasing')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
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
        Schema::dropIfExists('penjualan_canvasing_item');
    }
}
