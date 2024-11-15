<?php

namespace App\Exports\Accounting;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductFinanceImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'id',
                'brand',
                'mitra',
                'code',
                'name',
                'kemasan', 
                'harga_beli', 
                'harga_jual'
            ]
        ];
    }
}
