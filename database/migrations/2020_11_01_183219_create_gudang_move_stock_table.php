<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGudangMoveStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gudang_move_stock', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->string('code_transaction',255);
            $table->decimal('stock_in', 16, 4)->default(0);
            $table->decimal('stock_out', 16, 4)->default(0);
            $table->decimal('stock_balance', 16, 4)->default(0);
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
        Schema::dropIfExists('gudang_move_stock');
    }
}
