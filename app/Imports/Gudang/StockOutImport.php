<?php

namespace App\Imports\Gudang;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class StockOutImport implements ToCollection
{
    // Properti harus array agar bisa di-foreach di view
    public $success = [];
    public $error   = [];

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {

            foreach ($rows as $index => $row) {

                if ($index === 0) {
                    continue; // skip header
                }

                // Validasi kode dokumen
                if (empty($row[0])) {
                    $this->error[] = "Row {$index}: Kode dokumen kosong";
                    continue;
                }

                // Validasi quantity
                if (!is_numeric($row[4]) || $row[4] <= 0) {
                    $this->error[] = "Row {$index} ({$row[0]}): Quantity tidak valid";
                    continue;
                }

                // Validasi dan konversi tanggal
                try {
                    if (is_numeric($row[2])) {
                        // Excel Date (angka serial) → Carbon
                        $docDate = Carbon::instance(ExcelDate::excelToDateTimeObject($row[2]));
                    } else {
                        // String tanggal
                        $docDate = Carbon::parse($row[2]);
                    }
                } catch (\Exception $e) {
                    $this->error[] = "Row {$index} ({$row[0]}): Format tanggal salah";
                    continue;
                }

                // Simpan ke database
                DB::table('temp_out')->insert([
                    'doc_code'             => $row[0],
                    'doc_type'             => $row[1],
                    'doc_date'             => $docDate,
                    'reference_id'         => 0,
                    'product_packaging_id' => $row[3],
                    'quantity'             => $row[4],
                    'warehouse_id'         => $row[5],
                    'source_type'          => 'IMPORT EXCEL',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                $this->success[] = "Row {$index} ({$row[0]}): Berhasil dimasukkan";
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // Masukkan error global jika terjadi exception
            $this->error[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}