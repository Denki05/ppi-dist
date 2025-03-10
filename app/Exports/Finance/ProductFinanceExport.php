<?php

namespace App\Exports\Finance;

use App\Entities\Master\ProductFinance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductFinanceExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'mitra_id', 'brand_name', 'code_product', 'name_product', 'buying_price_usd_unit', 'selling_price_usd_unit'
    ];

    public function query()
    {
        return ProductFinance::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->column;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->mitra->name,
            $row->brand_name,
            $row->code_product,
            $row->name_product,
            $row->buying_price_usd_unit,
            $row->selling_price_usd_unit,
        ];
    }
}