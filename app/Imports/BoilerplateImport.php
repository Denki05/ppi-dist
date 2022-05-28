<?php

namespace App\Imports;

use App\Entities\Boilerplate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class BoilerplateImport implements ToModel, WithStartRow
{
    public function model(array $row)
    {
        return new Boilerplate([
            'text' => $row[0],
            'textarea' => $row[1],
            'select' => $row[2],
            'select_multiple' => explode(',', $row[3]),
            'image' => '',
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }
}
