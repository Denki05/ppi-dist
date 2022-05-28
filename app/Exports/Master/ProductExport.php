<?php

namespace App\Exports\Master;

use App\Entities\Master\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id',
        'brand_reference_id',
        'sub_brand_reference_id',
        'category_id',
        'type_id',
        'code',
        'name',
        'material_code',
        'material_name',
        'description',
        'default_quantity',
        'default_unit_id',
        'default_warehouse_id',
        'buying_price',
        'selling_price',
        'status',
    ];

    private $headings = [
        'id',
        'brand_reference',
        'sub_brand_reference',
        'category',
        'type',
        'code',
        'name',
        'material_code',
        'material_name',
        'description',
        'default_quantity',
        'default_unit',
        'default_warehouse',
        'buying_price',
        'selling_price',
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
            $row->brand_reference->name,
            $row->sub_brand_reference->name,
            $row->category->name,
            $row->type->name,
            $row->code,
            $row->name,
            $row->material_code,
            $row->material_name,
            $row->description,
            $row->default_quantity,
            $row->default_unit->name,
            $row->default_warehouse->name,
            $row->buying_price,
            $row->selling_price,
            $row->status()
        ];
    }
}
