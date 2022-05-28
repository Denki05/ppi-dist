<?php

namespace App\Imports\Master;

use App\Entities\Master\BrandReference;
use App\Entities\Master\SubBrandReference;
use App\Repositories\CodeRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class SubBrandReferenceImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader([
            'brand_reference_id',
            // 'code',
            'name',
            'link',
            'description'
        ], $row);

        return new SubBrandReference([
            'brand_reference_id' => $row['brand_reference_id'],
            'code' => CodeRepo::generateSubBrandReference(),
            'name' => $row['name'],
            'link' => $row['link'],
            'description' => $row['description'],
            'status' => SubBrandReference::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'brand_reference_id' => 'required|' . Rule::in(BrandReference::select('id')->pluck('id')->toArray()),
            // 'code' => 'required|' . Rule::unique('master_sub_brand_references', 'code'),
            'name' => 'required'
        ];
    }
}
