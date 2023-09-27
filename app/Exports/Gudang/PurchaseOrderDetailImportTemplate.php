<?php

namespace App\Exports\Gudang;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PurchaseOrderDetailImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'brand',
                'product',
                'qty',
                'packaging',
                'note',
                'customer',
            ]
        ];
    }
}
