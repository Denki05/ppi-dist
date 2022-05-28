<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualanDoOtherCostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan_do_other_cost', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('do_id');
            $table->string('note',255);
            $table->decimal('cost_idr', 16, 4)->default(0);
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
        Schema::dropIfExists('penjualan_do_other_cost');
    }
}
