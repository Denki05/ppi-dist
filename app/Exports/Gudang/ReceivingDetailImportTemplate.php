<?php

namespace App\Exports\Gudang;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReceivingDetailImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'PO CODE',
                'variant',
                'packaging',
                'no sj',
                'note',
            ]
        ];
    }
}
