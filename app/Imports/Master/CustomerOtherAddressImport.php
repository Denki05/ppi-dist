<?php

namespace App\Imports\Master;

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

class CustomerOtherAddressImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try{
            $collect_error = [];
            $collect_success = [];

            foreach ($rows as $row) 
            {
                $member = CustomerOtherAddress::where('id', $row['id'])
                        ->update([
                            'zone' => $row['zone']
                        ]);

                $collect_success[] = $row['nama'].' '.$row['kota'];
            }

            if (!$collect_success) {
                $collect_success[] = 'No successful import.';
            }

            if (!$collect_error) {
                $collect_error[] = 'No failed import.';
            }

            $this->error = $collect_error;
            $this->success = $collect_success;

            DB::commit();
        }catch (\Exception $e) {
            dd($e);
            $this->error = $e->getMessage();
            DB::rollBack();
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
