<?php

namespace App\Exports\Master;

use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductFinanceExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id',
        'brand_name',
        'code',
        'name',
        'status',
    ];

    private $headings = [
        'id',
        'brand_name',
        'code',
        'name',
        'status',
    ];

    public function query()
    {
        return Product::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->brand_name,
            $row->code,
            $row->name,
            $row->status()
        ];
    }
}
