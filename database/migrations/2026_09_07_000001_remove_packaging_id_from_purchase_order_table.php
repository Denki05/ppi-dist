<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovePackagingIdFromPurchaseOrderTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->dropForeign(['packaging_id']);
            $table->dropColumn('packaging_id');
        });
    }

    public function down()
    {
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->unsignedBigInteger('packaging_id')->nullable()->after('warehouse_id');
            $table->foreign('packaging_id')->references('id')->on('master_packaging')->nullOnDelete();
        });
    }
}
