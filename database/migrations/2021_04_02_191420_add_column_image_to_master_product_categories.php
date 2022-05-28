<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnImageToMasterProductCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_product_categories', function (Blueprint $table) {
            $table->string('image_header_list')->nullable();
            $table->string('image_header_price')->nullable();
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
            $table->dropColumn(['image_header_list','image_header_price']);
        });
    }
}
