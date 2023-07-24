<?php

namespace App\Imports\Master;

use App\Entities\Master\BrandReference;
use App\Entities\Master\SubBrandReference;
use App\Repositories\CodeRepo;
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

class SubBrandReferenceImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;

    public function  __construct($brand_reference_id)
    {
        $this->brand_reference_id = $brand_reference_id;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        $collect_error = [];

        $brand_reference = BrandReference::find($this->brand_reference_id);

        if($brand_reference == null) {
            $collect_error = array('Something went wrong, please reload page!');
        } else {
            foreach ($rows as $row) {
                $brand_reference = BrandReference::where('name', $row['brand_name'])->first();
                if($brand_reference == null) {
                    $collect_error = array('BRAND '.$row['brand_name'].' NOT FOUND : all import aborted!');
                    break;
                }

                $searah_code = CodeRepo::generateSubBrandReference();
                $searah = $row['name'];
                $link = $row['link'];
                $note = $row['description'];
                $status = SubBrandReference::STATUS['ACTIVE'];

                $sub_brand_reference = new SubBrandReference;
                $sub_brand_reference->brand_reference_id = $brand_reference->id;
                $sub_brand_reference->code = $searah_code;
                $sub_brand_reference->name = $searah;
                $sub_brand_reference->link = $link;
                $sub_brand_reference->description = $note;
                $sub_brand_reference->status = $status;

                if($sub_brand_reference->save()) {
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

    public function startRow(): int
    {
        return 2;
    }
}
