<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualanDoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan_do', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')->unique();
            $table->string('do_code')->unique()->nullable();
            $table->bigInteger('warehouse_id');
            $table->bigInteger('customer_id');
            $table->bigInteger('customer_other_address_id')->nullable();
            $table->decimal('idr_rate', 16, 4)->default(0);
            $table->integer('type_transaction')->comments('1.Cash;2.Tempo;3.Marketplace');
            $table->integer('status')->comments('1.Draft;2.Prepare;3.Sending;4.Sent');
            $table->text('note')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penjualan_do');
    }
}
