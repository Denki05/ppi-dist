<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddHalalSumberSg extends Migration
{
    /**
     * Dual sertifikat: ID (BPJPH/KMI) + SG (MUIS/SENSES).
     * - halal_documents.sumber : ID | SG (+ info QR/perusahaan/expired SG)
     * - halal_lists.sumber + unique per dokumen (agar no 1-92 SG tidak bentrok 1-725 ID)
     * - halal_helpers.sumber : 1 produk bisa punya 2 baris (ID + SG)
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('halal_documents')) {
            Schema::table('halal_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('halal_documents', 'sumber')) {
                    $table->string('sumber', 10)->default('ID')->after('jenis');
                }
                if (!Schema::hasColumn('halal_documents', 'lembaga')) {
                    $table->string('lembaga', 50)->nullable()->after('nomor_sertifikat');
                }
                if (!Schema::hasColumn('halal_documents', 'nama_perusahaan')) {
                    $table->string('nama_perusahaan', 255)->nullable()->after('lembaga');
                }
                if (!Schema::hasColumn('halal_documents', 'qr_url')) {
                    $table->text('qr_url')->nullable()->after('nama_perusahaan');
                }
                if (!Schema::hasColumn('halal_documents', 'tanggal_kedaluwarsa')) {
                    $table->date('tanggal_kedaluwarsa')->nullable()->after('tanggal_versi');
                }
            });
            DB::table('halal_documents')->whereNull('sumber')->orWhere('sumber', '')->update(['sumber' => 'ID']);
        }

        if (Schema::hasTable('halal_lists')) {
            // Lepas unique global nomor_urut agar SG 1-92 tidak bentrok ID 1-725.
            try {
                DB::statement('ALTER TABLE halal_lists DROP INDEX halal_lists_nomor_urut_unique');
            } catch (\Exception $e) {
            }
            Schema::table('halal_lists', function (Blueprint $table) {
                if (!Schema::hasColumn('halal_lists', 'sumber')) {
                    $table->string('sumber', 10)->default('ID')->after('document_id');
                }
            });
            DB::table('halal_lists')->whereNull('sumber')->orWhere('sumber', '')->update(['sumber' => 'ID']);
            // Unique per dokumen.
            try {
                DB::statement('CREATE UNIQUE INDEX halal_lists_doc_nomor_unique ON halal_lists (document_id, nomor_urut)');
            } catch (\Exception $e) {
            }
        }

        if (Schema::hasTable('halal_helpers')) {
            Schema::table('halal_helpers', function (Blueprint $table) {
                if (!Schema::hasColumn('halal_helpers', 'sumber')) {
                    $table->string('sumber', 10)->default('ID')->after('merek');
                }
                if (!Schema::hasColumn('halal_helpers', 'list_document_id')) {
                    $table->unsignedBigInteger('list_document_id')->nullable()->after('nomor_urut_list_halal');
                }
                if (!Schema::hasColumn('halal_helpers', 'pdf_document_id')) {
                    $table->unsignedBigInteger('pdf_document_id')->nullable()->after('list_document_id');
                }
                if (!Schema::hasColumn('halal_helpers', 'nama_perusahaan')) {
                    $table->string('nama_perusahaan', 255)->nullable()->after('nomor_sertifikat');
                }
                if (!Schema::hasColumn('halal_helpers', 'tanggal_kedaluwarsa')) {
                    $table->date('tanggal_kedaluwarsa')->nullable()->after('tanggal_pdf');
                }
            });
            DB::table('halal_helpers')->whereNull('sumber')->orWhere('sumber', '')->update(['sumber' => 'ID']);
            try {
                DB::statement('CREATE INDEX halal_helpers_sumber_idx ON halal_helpers (sumber)');
            } catch (\Exception $e) {
            }
            try {
                DB::statement('CREATE UNIQUE INDEX halal_helpers_kode_merek_sumber_unique ON halal_helpers (kode_produk(100), merek(50), sumber(10))');
            } catch (\Exception $e) {
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('halal_helpers')) {
            try {
                DB::statement('DROP INDEX halal_helpers_kode_merek_sumber_unique ON halal_helpers');
            } catch (\Exception $e) {
            }
            try {
                DB::statement('DROP INDEX halal_helpers_sumber_idx ON halal_helpers');
            } catch (\Exception $e) {
            }
            Schema::table('halal_helpers', function (Blueprint $table) {
                if (Schema::hasColumn('halal_helpers', 'tanggal_kedaluwarsa')) {
                    $table->dropColumn('tanggal_kedaluwarsa');
                }
                if (Schema::hasColumn('halal_helpers', 'nama_perusahaan')) {
                    $table->dropColumn('nama_perusahaan');
                }
                if (Schema::hasColumn('halal_helpers', 'pdf_document_id')) {
                    $table->dropColumn('pdf_document_id');
                }
                if (Schema::hasColumn('halal_helpers', 'list_document_id')) {
                    $table->dropColumn('list_document_id');
                }
                if (Schema::hasColumn('halal_helpers', 'sumber')) {
                    $table->dropColumn('sumber');
                }
            });
        }
        if (Schema::hasTable('halal_lists')) {
            try {
                DB::statement('DROP INDEX halal_lists_doc_nomor_unique ON halal_lists');
            } catch (\Exception $e) {
            }
            Schema::table('halal_lists', function (Blueprint $table) {
                if (Schema::hasColumn('halal_lists', 'sumber')) {
                    $table->dropColumn('sumber');
                }
            });
            try {
                DB::statement('CREATE UNIQUE INDEX halal_lists_nomor_urut_unique ON halal_lists (nomor_urut)');
            } catch (\Exception $e) {
            }
        }
        if (Schema::hasTable('halal_documents')) {
            Schema::table('halal_documents', function (Blueprint $table) {
                foreach (['tanggal_kedaluwarsa', 'qr_url', 'nama_perusahaan', 'lembaga', 'sumber'] as $col) {
                    if (Schema::hasColumn('halal_documents', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
