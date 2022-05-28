<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancePayableDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_payable_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payable_id');
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('prev_account_receivable', 16, 4)->default(0);
            $table->decimal('total', 16, 4)->default(0);
            $table->timestamps();

            $table->foreign('payable_id')
                  ->references('id')
                  ->on('finance_payable')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('invoice_id')
                  ->references('id')
                  ->on('finance_invoicing')
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
        Schema::dropIfExists('finance_payable_detail');
    }
}
