<?php

namespace App\Imports\Master;

use App\Entities\Master\Mitra;
use App\Entities\Master\MitraSetting;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use DB;

class MitraSettingImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function collection(Collection $rows)
    {
        $bulanNama = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        DB::beginTransaction();

        try {
            $collect_error = [];
            $collect_success = [];

            foreach ($rows as $row) {
                $mitra = Mitra::where('name', $row['mitra'])->first();

                if (!$mitra) {
                    $collect_error[] = "Mitra dengan nama {$row['mitra']} tidak ditemukan.";
                    continue;
                }

                // Validasi bulan
                $bulan = (int) $row['bulan'];
                if ($bulan < 1 || $bulan > 12) {
                    $collect_error[] = "Bulan {$row['bulan']} tidak valid. Gunakan angka 1–12.";
                    continue;
                }

                // ✅ Cek duplikasi bulan per mitra
                $exists = MitraSetting::where('mitra_id', $mitra->id)
                    ->where('bulan', $bulan)
                    ->exists();

                if ($exists) {
                    $collect_error[] = "Bulan {$bulanNama[$bulan]} untuk Mitra {$mitra->name} sudah ada.";
                    continue;
                }

                // ✅ Simpan data
                $mitra_setting = new MitraSetting();
                $mitra_setting->mitra_id = $mitra->id;
                $mitra_setting->bulan = $row['bulan'];
                $mitra_setting->batas_bawah = $row['batas_bawah'];
                $mitra_setting->batas_atas = $row['batas_atas'];
                $mitra_setting->save();

                $collect_success[] = "Setting berhasil: Mitra {$mitra->name}, Bulan {$row['bulan']}";
            }

            // Feedback jika kosong
            if (empty($collect_success)) {
                $collect_success[] = 'Tidak ada data yang berhasil diimport.';
            }

            if (empty($collect_error)) {
                $collect_error[] = 'Tidak ada error pada import.';
            }

            $this->error = $collect_error;
            $this->success = $collect_success;

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error = ['Error: ' . $e->getMessage()];
            $this->success = [];
        }
    }

    public function startRow(): int
    {
        return 2; // Mulai baca dari baris kedua (abaikan header)
    }
}