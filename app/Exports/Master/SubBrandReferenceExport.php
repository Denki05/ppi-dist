<?php

namespace App\Exports\Master;

use App\Entities\Master\SubBrandReference;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubBrandReferenceExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'brand_reference_id', 'code', 'name', 'link', 'description', 'status'
    ];

    private $headings = [
        'id', 'brand_reference', 'code', 'name', 'link', 'description', 'status'
    ];

    public function query()
    {
        return SubBrandReference::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->brand_reference['name'],
            $row->code,
            $row->name,
            $row->link,
            $row->description,
            $row->status()
        ];
    }
}
