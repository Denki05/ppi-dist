<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VendorImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['name', 'address', 'email', 'phone', 'website', 'owner_name', 'description']
        ];
    }
}
