<?php

namespace App\Imports\Master;

use App\Entities\Master\Contact;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Vendor;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
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

class ContactImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;

    public function  __construct($customer_other_address_id, $vendor_id)
    {
        $this->brand_reference_id = $brand_reference_id;
        $this->vendor_id = $vendor_id;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        $collect_error = [];

        $member = CustomerOtherAddress::find($this->customer_other_address_id);

        $vendor = Vendor::find($this->vendor_id);

        if($member == null || $vendor == null) {
            $collect_error = array('Something went wrong, please reload page!');
        } else {
            foreach ($rows as $row) {
                $member = CustomerOtherAddress::where('name', $row['account_name'])->first();
                $vendor = Vendor::where('name', $row['account_name'])->first();
                if($member == null || $vendor == null) {
                    $collect_error = array('Account Name '.$row['account_name'].' NOT FOUND : all import aborted!');
                    break;
                }

                $name_contact = $row['name'];
                $dob =  ($row['dob'] != null) ? date('Y-m-d', strtotime($row['dob'])) : null;
                $is_for = $row['is_for'];
                $account = $row['account_name'];
                $posisi = $row['position'];
                $telp = $row['telepon'];
                $email = $row['email'];
                $no_ktp = $row['ktp'];
                $no_npwp = $row['npwp'];
                $status = Contact::STATUS['ACTIVE'];

                if($row['is_for'] == 1){
                    
                }

                

                // if($sub_brand_reference->save()) {
                // } else {
                //     $collect_error = array('Something went wrong, please reload page!');
                //     break;
                // }
                
            }
        }

        if($collect_error) {
            $this->error = $collect_error;
            DB::rollBack();
        } 
        
        DB::commit();
    }

    public function startRow(): int
    {
        return 2;
    }
}
