<?php

namespace App\Imports\Master;

use App\Entities\Master\ProductCategory;
use App\Repositories\CodeRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ProductCategoryImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader([/* 'code', */ 'name', 'description'], $row);

        return new ProductCategory([
            'code' => CodeRepo::generateProductCategory(),
            'name' => $row['name'],
            'description' => $row['description'],
            'status' => ProductCategory::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            // 'code' => 'required|' . Rule::unique('master_product_categories', 'code'),
            'name' => 'required'
        ];
    }
}
