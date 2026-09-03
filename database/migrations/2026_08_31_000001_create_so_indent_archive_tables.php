<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoIndentArchiveTables extends Migration
{
    public function up()
    {
        // Tabel Header Archive
        Schema::create('so_indent_archive', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('original_so_id')->unsigned()->comment('ID SO asli sebelum diarsipkan');
            $table->string('so_code')->nullable();
            $table->string('code')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('customer_other_address_id')->nullable();
            $table->bigInteger('brand_name')->nullable();
            $table->integer('type_transaction')->nullable();
            $table->decimal('idr_rate', 16, 4)->default(0);
            $table->text('note')->nullable();
            $table->integer('status')->nullable();
            $table->boolean('is_estimate')->default(0);
            $table->string('estimate_code')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('original_so_id');
            $table->index('so_code');
            $table->index('estimate_code');
        });

        // Tabel Item Archive
        Schema::create('so_indent_archive_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('archive_id')->unsigned();
            $table->bigInteger('original_so_item_id')->unsigned()->comment('ID SO Item asli');
            $table->bigInteger('product_packaging_id')->nullable();
            $table->decimal('price', 16, 4)->default(0);
            $table->decimal('qty', 16, 4)->default(0);
            $table->bigInteger('packaging_id')->nullable();
            $table->boolean('free_product')->default(0);
            $table->decimal('disc_usd', 16, 4)->default(0);
            $table->timestamps();

            $table->foreign('archive_id')->references('id')->on('so_indent_archive')->onDelete('cascade');
            $table->index('archive_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('so_indent_archive_item');
        Schema::dropIfExists('so_indent_archive');
    }
}
