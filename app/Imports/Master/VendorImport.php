<?php

namespace App\Imports\Master;

use App\Entities\Master\Vendor;
use App\Repositories\CodeRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class VendorImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader(['name', /* 'code', */ 'address', 'email', 'phone', 'website', 'owner_name', 'description'], $row);

        return new Vendor([
            'code' => CodeRepo::generateVendor(),
            'name' => $row['name'],
            'address' => $row['address'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'website' => $row['website'],
            'owner_name' => $row['owner_name'],
            'description' => $row['description'],
            'status' => Vendor::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            // 'code' => 'required|' . Rule::unique('master_vendors', 'code'),
            'name' => 'required',
        ];
    }
}
