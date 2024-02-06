<?php

namespace App\Exports\Penjualan;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SettingPriceImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'brand',
                'name',
                'kemasan',
                'price',
            ]
        ];
    }
}
