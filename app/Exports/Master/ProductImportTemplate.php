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
                'searah',
                'kategori',
                'packaging',
                'type',
                'vendor',
                'brand_name',
                'code',
                'name',
                'material_code',
                'material_name',
                'gender',
                'description',
                'qty',
                'warehouse',
                'buying_price',
                'selling_price',
            ]
        ];
    }
}
