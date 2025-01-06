<?php

namespace App\Exports\Gudang;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'id',
                'code',
                'name',
                'brand',
                'packaging',
                'quantity',
            ]
        ];
    }
}
