<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class QuestionImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['question', 'score']
        ];
    }
}
