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

HeadingRowFormatter::default('none');

class ContactImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;

    public function  __construct($manage_id)
    {
        $this->manage_id = $manage_id;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        $collect_error = [];

        $member = CustomerOtherAddress::find($this->manage_id);
        $vendor = Vendor::find($this->manage_id);

        if($member == null && $vendor == null) {
            $collect_error = array('Something went wrong, please reload page!');
        } else {
            foreach ($rows as $row) {
                $member = CustomerOtherAddress::where('name', $row['account_name'])->first();
                $vendor = Vendor::where('name', $row['account_name'])->first();
                if($member == null && $vendor == null) {
                    $collect_error = array('Account Name '.$row['account_name'].' NOT FOUND : all import aborted!');
                    break;
                }

                // create ID contact
                $get_max_id = DB::table('master_contacts')
                    ->max('id');

                if($get_max_id == null){
                    $no = 1;
                    $kd = sprintf("%03s", $no);
                }else{
                    $explode = explode("-", $get_max_id);
                        
                    $no = 1;
                    $tmp = $explode['1'] + $no;
                    $kd = sprintf("%03s", $tmp);
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
                    $contact = new Contact;

                    $contact->id = $member->id . '.' . $is_for . '-' . $kd;
                    $contact->name = $name_contact;
                    $contact->dob = $dob;
                    $contact->is_for = $is_for;
                    $contact->manage_id = $member->id;
                    $contact->position = $posisi;
                    $contact->phone = $telp;
                    $contact->ktp = implode("/", [$name_contact, $no_ktp]);
                    $contact->npwp = implode("/", [$name_contact, $no_npwp]);
                    $contact->status = $status;
                    if($contact->save()) {
                    } else {
                        $collect_error = array('Something went wrong, please reload page!');
                        break;
                    }
                }else{
                    $contact = new Contact;

                    $contact->id = $vendor->id . '.' . $is_for . '-' . $kd;
                    $contact->name = $name_contact;
                    $contact->dob = $dob;
                    $contact->is_for = $is_for;
                    $contact->manage_id = $vendor->id;
                    $contact->position = $posisi;
                    $contact->phone = $telp;
                    $contact->ktp = implode("/", [$name_contact, $no_ktp]);
                    $contact->npwp = implode("/", [$name_contact, $no_npwp]);
                    $contact->status = $status;
                    if($contact->save()) {
                    } else {
                        $collect_error = array('Something went wrong, please reload page!');
                        break;
                    }
                }
            }

            if($collect_error) {
                $this->error = $collect_error;
                DB::rollBack();
            } 
            
            DB::commit();
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
