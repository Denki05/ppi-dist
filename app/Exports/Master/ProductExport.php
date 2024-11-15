<?php

namespace App\Exports\Master;

use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'master_products_packaging.id',
        'master_products.brand_name',
        'master_products_packaging.code',
        'master_products_packaging.name',
        'master_packaging.pack_name',
        'master_products.status',
    ];

    private $headings = [
        'ID',
        'Brand Name',
        'Code',
        'Name',
        'Kemasan',
        'Status',
    ];

    public function query()
    {
        // return Product::query()->select($this->column);
        return Product::query()
            ->join('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->join('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->where('master_products.status', 1)
            ->select($this->column);
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
            $row->pack_name,
            $row->status()
        ];
    }
}
