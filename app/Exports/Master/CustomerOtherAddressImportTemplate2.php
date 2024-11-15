<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomerOtherAddressImportTemplate2 implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'id',
                'nama',
                'member_default',
                'pic',
                'officer',
                'ar1',
                'ar2',
                'ar3',
                'target_omset',
            ]
        ];
    }
}
