<?php

namespace App\Exports\Master;

use App\Entities\Master\Question;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuestionExport implements FromQuery, WithHeadings, ShouldAutoSize
{
    private $column = [
        'id', 'question', 'score'
    ];

    public function query()
    {
        return Question::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->column;
    }
}
