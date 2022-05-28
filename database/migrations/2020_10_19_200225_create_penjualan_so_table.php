<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualanSoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan_so', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->bigInteger('sales_senior_id');
            $table->bigInteger('sales_id');
            $table->bigInteger('origin_warehouse_id');
            $table->bigInteger('destination_warehouse_id')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->integer('type_transaction')->comments('1.Cash;2.Tempo;3.Marketing');
            $table->integer('status')->comments('1.Draft');
            $table->integer('so_for')->comments('1.Customer;2.Gudang');
            $table->timestamps();


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
        Schema::dropIfExists('penjualan_so');
    }
}
