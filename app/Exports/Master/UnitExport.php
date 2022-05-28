<?php

namespace App\Exports\Master;

use App\Entities\Master\Unit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UnitExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'name', 'abbreviation', 'description', 'status'
    ];

    public function query()
    {
        return Unit::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->column;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->abbreviation,
            $row->description,
            $row->status()
        ];
    }
}
