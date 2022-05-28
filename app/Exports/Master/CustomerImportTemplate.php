<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomerImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'name', 'category_id', /* 'type_id', */
                'email', 'phone', 'npwp', 'address',
                'owner_name', 'plafon_piutang', 'gps_latitude', 'gps_longitude',
                'zipcode'
            ]
        ];
    }
}
