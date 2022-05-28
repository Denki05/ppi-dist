<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualanDoMutationItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan_do_mutation_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_id');
            $table->unsignedBigInteger('do_mutation_id');
            $table->unsignedBigInteger('so_item_id');
            $table->integer('packaging');
            $table->decimal('qty', 16, 4)->default(0);
            $table->decimal('price', 16, 4)->default(0);
            $table->string('note', 255);
            $table->timestamps();

            $table->foreign('so_item_id')
                  ->references('id')
                  ->on('penjualan_so_item')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('do_mutation_id')
                  ->references('id')
                  ->on('penjualan_do_mutation')
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
        Schema::dropIfExists('penjualan_do_mutation_item');
    }
}
