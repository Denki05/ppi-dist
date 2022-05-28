<?php

namespace App\Imports\Master;

use App\Entities\Master\CustomerCategory;
use App\Repositories\CodeRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class CustomerCategoryImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader([/* 'code', */ 'name', 'score', 'description'], $row);

        return new CustomerCategory([
            'code' => CodeRepo::generateCustomerCategory(),
            'name' => $row['name'],
            'score' => $row['score'],
            'description' => $row['description'],
            'status' => CustomerCategory::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            // 'code' => 'required|' . Rule::unique('master_customer_categories', 'code'),
            'name' => 'required',
            'score' => 'required'
        ];
    }
}
