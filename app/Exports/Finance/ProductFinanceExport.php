<?php

namespace App\Exports\Finance;

use App\Entities\Master\ProductFinance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductFinanceExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $mitraId;

    public function __construct($mitraId = null)
    {
        $this->mitraId = $mitraId ? (int) $mitraId : null;
    }

    public function query()
    {
        // Join mitra sekali di SQL agar map() tidak N+1 (tanpa $row->mitra->name).
        return ProductFinance::query()
            ->leftJoin('master_mitra', 'master_mitra.id', '=', 'master_product_finance.mitra_id')
            ->when($this->mitraId, function ($q) {
                $q->where('master_product_finance.mitra_id', $this->mitraId);
            })
            ->select(
                'master_product_finance.id',
                'master_mitra.name as mitra_name',
                'master_product_finance.brand_name',
                'master_product_finance.code_product',
                'master_product_finance.name_product',
                'master_product_finance.buying_price_usd_unit',
                'master_product_finance.selling_price_usd_unit'
            )
            ->orderBy('master_product_finance.code_product');
    }

    public function headings(): array
    {
        return [
            'id',
            'mitra',
            'brand_name',
            'code_product',
            'name_product',
            'buying_price_usd_unit',
            'selling_price_usd_unit',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->mitra_name,
            $row->brand_name,
            $row->code_product,
            $row->name_product,
            $row->buying_price_usd_unit,
            $row->selling_price_usd_unit,
        ];
    }
}
