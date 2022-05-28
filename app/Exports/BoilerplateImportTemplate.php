<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BoilerplateImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['text', 'textarea', 'select', 'select_multiple']
        ];
    }
}
