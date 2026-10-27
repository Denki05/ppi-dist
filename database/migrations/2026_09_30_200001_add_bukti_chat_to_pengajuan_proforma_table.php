<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBuktiChatToPengajuanProformaTable extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_proforma', 'bukti_chat_path')) {
                $table->string('bukti_chat_path', 255)->nullable()->after('npwp_photo_path')
                    ->comment('Path foto bukti chat kesanggupan bayar (diupload AO sebelum submit lanjutan)');
            }
            if (!Schema::hasColumn('pengajuan_proforma', 'bukti_chat_at')) {
                $table->timestamp('bukti_chat_at')->nullable()->after('bukti_chat_path');
            }
        });
    }

    public function down()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            foreach (['bukti_chat_path', 'bukti_chat_at'] as $col) {
                if (Schema::hasColumn('pengajuan_proforma', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
