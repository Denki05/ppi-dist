<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTypeIdFromMasterProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_product_categories', function (Blueprint $table) {
            $table->dropForeign('master_product_categories_type_id_foreign');
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
        Schema::table('master_product_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->after('id')->nullable(); // nullable for exists rows
            $table->foreign('type_id')->references('id')->on('master_product_types')->onDelete('restrict');
        });
    }
}
