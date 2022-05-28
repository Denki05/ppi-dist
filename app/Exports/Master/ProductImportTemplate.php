<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
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
            ]
        ];
    }
}
