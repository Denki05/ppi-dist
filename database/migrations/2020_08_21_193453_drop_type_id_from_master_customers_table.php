<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTypeIdFromMasterCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_customers', function (Blueprint $table) {
            $table->dropForeign('master_customers_type_id_foreign');
            $table->dropColumn('type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->after('category_id')->nullable(); // nullable for exists rows
            $table->foreign('type_id')->references('id')->on('master_customer_types')->onDelete('restrict');
        });
    }
}
