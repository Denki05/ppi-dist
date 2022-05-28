<?php

namespace App\Imports\Master;

use App\Entities\Master\BrandReference;
use App\Entities\Master\Product;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\ProductType;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\Unit;
use App\Entities\Master\Warehouse;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ProductImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader([
            'brand_reference_id',
            'sub_brand_reference_id',
            'category_id',
            'type_id',
            'code',
            'name',
            'material_code',
            'material_name',
            'description',
            'default_quantity',
            'default_unit_id',
            'default_warehouse_id',
            'buying_price',
            'selling_price',
        ], $row);

        return new Product([
            'brand_reference_id' => $row['brand_reference_id'],
            'sub_brand_reference_id' => $row['sub_brand_reference_id'],
            'category_id' => $row['category_id'],
            'type_id' => $row['type_id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'material_code' => $row['material_code'],
            'material_name' => $row['material_name'],
            'description' => $row['description'],
            'default_quantity' => $row['default_quantity'],
            'default_unit_id' => $row['default_unit_id'],
            'default_warehouse_id' => $row['default_warehouse_id'],
            'buying_price' => $row['buying_price'],
            'selling_price' => $row['selling_price'],
            'status' => Product::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'brand_reference_id' => 'required|' . Rule::in(BrandReference::select('id')->pluck('id')->toArray()),
            'sub_brand_reference_id' => 'required|' . Rule::in(SubBrandReference::select('id')->pluck('id')->toArray()),
            'category_id' => 'required|' . Rule::in(ProductCategory::select('id')->pluck('id')->toArray()),
            'type_id' => 'required|' . Rule::in(ProductType::select('id')->pluck('id')->toArray()),
            'code' => 'required',
            'name' => 'required',
            'material_code' => 'required',
            'material_name' => 'required',
            'default_quantity' => 'required|numeric',
            'default_unit_id' => 'required|integer|' . Rule::in(Unit::select('id')->pluck('id')->toArray()),
            'default_warehouse_id' => 'required|integer|' . Rule::in(Warehouse::select('id')->pluck('id')->toArray()),
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
        ];
    }
}
