<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGudangStockAdjustmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gudang_stock_adjustment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('prev', 16, 4)->default(0);
            $table->decimal('plus', 16, 4)->default(0);
            $table->decimal('min', 16, 4)->default(0);
            $table->decimal('update', 16, 4)->default(0);
            $table->string('note',255)->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('master_warehouses')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('product_id')
                  ->references('id')
                  ->on('master_products')
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
        Schema::dropIfExists('gudang_stock_adjustment');
    }
}
