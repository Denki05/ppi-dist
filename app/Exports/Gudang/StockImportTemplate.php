<?php

namespace App\Exports\Gudang;

use App\Entities\Master\ProductMinStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockImportTemplate implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function collection()
    {
        return ProductMinStock::with(['product_pack.product', 'product_pack.packaging'])
            ->get()
            ->filter(function ($item) {
                return $item->product_pack !== null;
            })
            ->map(function ($item) {
                return [
                    'id'        => $item->product_pack->id,
                    'code'      => $item->product_pack->code,
                    'name'      => $item->product_pack->name,
                    'brand'     => optional($item->product_pack->product)->brand_name ?? '-',
                    'packaging' => optional($item->product_pack->packaging)->pack_name ?? '-',
                    'quantity'  => '',
                ];
            })
            ->values();
    }

    public function headings(): array
    {
        return [
            'id',
            'code',
            'name',
            'brand',
            'packaging',
            'quantity',
        ];
    }
}
