<?php

namespace App\Imports\Master;

use App\Entities\Master\Ekspedisi;
use App\Repositories\EkspedisiRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class EkspedisiImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader([
            'name', 'address',
            'email', 'phone', 'fax',
            'website', 'owner_name'
        ], $row);

        return new Ekspedisi([
            'code' => EkspedisiRepo::generateCode(),
            'name' => $row['name'],
            'address' => $row['address'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'fax' => $row['fax'],
            'website' => $row['website'],
            'owner_name' => $row['owner_name'],
            'status' => Ekspedisi::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'name' => 'required'
        ];
    }
}
