<?php

namespace App\Exports\Master;

use App\Entities\Master\ProductCategory;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductCategoryExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'code', 'name', 'description', 'status'
    ];

    public function query()
    {
        return ProductCategory::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->column;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->code,
            $row->name,
            $row->description,
            $row->status()
        ];
    }
}
