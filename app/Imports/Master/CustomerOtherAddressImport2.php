<?php

namespace App\Imports\Master;

use App\Entities\Master\Customer;
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

class CustomerOtherAddressImport2 implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            $collect_error = [];
            $collect_success = [];

            foreach ($rows as $row) {
                $get_member = CustomerOtherAddress::find($row['id']);

                if ($get_member) {
                    // update member field
                    $update_member = CustomerOtherAddress::where('id', $get_member->id)
                                ->update([
                                    'officer' => $row['officer'],
                                    'account_representative' => $row['ar1'],
                                    'account_representative_optional_1' => $row['ar2'],
                                    'account_representative_optional_2' => $row['ar3'],
                                    'setting_income_target' => $row['target_omset'],
                                ]);

                    // update store PIC field
                    if ($row['member_default'] === "YES") {
                        $update_store = Customer::where('id', $get_member->customer_id)
                                    ->update([
                                        'pic' => $row['pic'],
                                    ]);
                    }

                    $collect_success[] = $row['nama'];
                } else {
                    $collect_error[] = "Member with ID " . $row['id'] . " not found.";
                }
            }

            if (empty($collect_success)) {
                $collect_success[] = 'No successful import.';
            }

            if (empty($collect_error)) {
                $collect_error[] = 'No failed import.';
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the exception instead of dd
            Log::error('Error updating collection: ' . $e->getMessage());
            $this->error = [$e->getMessage()];
            return;
        }

        $this->error = $collect_error;
        $this->success = $collect_success;
    }

    public function startRow(): int
    {
        return 2;
    }
}