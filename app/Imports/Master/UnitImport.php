<?php

namespace App\Imports\Master;

use App\Entities\Master\Unit;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class UnitImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader(['name', 'abbreviation', 'description'], $row);

        return new Unit([
            'name' => $row['name'],
            'abbreviation' => $row['abbreviation'],
            'description' => $row['description'],
            'status' => Unit::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'name' => 'required',
            'abbreviation' => 'required|' . Rule::unique('master_units', 'abbreviation')
        ];
    }
}
