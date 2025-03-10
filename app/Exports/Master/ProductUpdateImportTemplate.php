<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductUpdateImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'id',
                'brand',
                'code',
                'name',
                'packaging',
                'status',
                'ratio',
            ]
        ];
    }
}
