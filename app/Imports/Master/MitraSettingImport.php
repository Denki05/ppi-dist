<?php

namespace App\Imports\Master;

use App\Entities\Master\Mitra;
use App\Entities\Master\MitraSetting;
use App\Entities\Master\CustomerOtherAddress;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Validators\Failure;
use DB;

class MitraSettingImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function collection(Collection $rows)
    {
        // dd($rows);

        DB::beginTransaction();

        try {
            $collect_error = [];
            $collect_success = [];

            foreach ($rows as $row) {
                // Find product using extracted ID
                $mitra = Mitra::where('name', $row['mitra'])->first();

                if (!$mitra) {
                    $collect_error[] = 'Mitra with name ' . $row['mitra'] . ' not found.';
                    continue; // Continue processing other rows
                }

                $mitra_setting = new MitraSetting;
                $mitra_setting->mitra_id = $mitra->id;
                $mitra_setting->bulan = $row['bulan'];
                $mitra_setting->batas_bawah = $row['batas_bawah'];
                $mitra_setting->batas_atas = $row['batas_atas'];
                $mitra_setting->save();

                $collect_success[] = "Setting Mitra: {$mitra->name}";
            }

            // Provide feedback for success and errors
            if (empty($collect_success)) {
                $collect_success[] = 'No successful import.';
            }

            if (empty($collect_error)) {
                $collect_error[] = 'No failed import.';
            }

            $this->error = $collect_error;
            $this->success = $collect_success;

            DB::commit();
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            $this->error = ['Error: ' . $e->getMessage()];
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}