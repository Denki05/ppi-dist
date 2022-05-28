<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualanDoDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan_do_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('do_id');
            $table->decimal('discount_1', 16, 4)->default(0);
            $table->decimal('discount_2', 16, 4)->default(0);
            $table->decimal('discount_idr', 16, 4)->default(0);
            $table->decimal('total_discount_idr', 16, 4)->default(0);
            $table->decimal('ppn', 16, 4)->default(0);
            $table->decimal('voucher_idr', 16, 4)->default(0);
            $table->decimal('cashback_idr', 16, 4)->default(0);
            $table->decimal('purchase_total_idr', 16, 4)->default(0);
            $table->decimal('delivery_cost_idr', 16, 4)->default(0);
            $table->string('delivery_cost_note')->nullable();
            $table->decimal('other_cost_idr', 16, 4)->default(0);
            $table->string('other_cost_note')->nullable();
            $table->decimal('grand_total_idr', 16, 4)->default(0);
            $table->timestamps();

            $table->foreign('do_id')
                  ->references('id')
                  ->on('penjualan_do')
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
        Schema::dropIfExists('penjualan_do_details');
    }
}
