<?php

namespace App\Exports;

use App\Entities\Boilerplate;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BoilerplateExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'text', 'textarea', 'select', 'select_multiple'
    ];

    public function query()
    {
        return Boilerplate::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->column;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->text,
            $row->textarea,
            $row->select,
            $row->select_multiple,
        ];
    }
}
