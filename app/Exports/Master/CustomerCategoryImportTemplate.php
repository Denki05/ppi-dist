<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomerCategoryImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['name', 'score', 'description']
        ];
    }
}
