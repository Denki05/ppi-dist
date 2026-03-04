<?php

namespace App\Exports\Gudang;

use App\Entities\Master\ProductPack;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'master_products_packaging.id',
        'master_products_packaging.code',
        'master_products_packaging.name',
        'master_products.brand_name',
        'master_packaging.pack_name',
        'master_product_min_stocks.quantity',
    ];

    private $headings = [
        'ID',
        'Code',
        'Name',
        'Brand',
        'Packaging',
        'Quantity',
    ];

    public function query()
    {
        // return Product::query()->select($this->column);
        return ProductPack::query()
            ->join('master_product_min_stocks', 'master_products_packaging.id', '=', 'master_product_min_stocks.product_packaging_id')
            ->join('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->join('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->where('master_product_min_stocks.warehouse_id', 2)
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
            $row->code,
            $row->name,
            $row->brand_name,
            $row->pack_name,
            $row->quantity,
        ];
    }
}
