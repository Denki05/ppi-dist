<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EkspedisiImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'name', 'address',
                'email', 'phone', 'fax',
                'website', 'owner_name'
            ]
        ];
    }
}
