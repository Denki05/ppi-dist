<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SubBrandReferenceImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'brand_name', 
                'name', 
                'link', 
                'description'
            ]
        ];
    }
}
