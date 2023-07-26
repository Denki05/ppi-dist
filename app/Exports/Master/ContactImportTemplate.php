<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ContactImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['name', 'dob', 'is_for', 'account_name', 'position', 'telepon', 'email', 'ktp', 'npwp']
        ];
    }
}
