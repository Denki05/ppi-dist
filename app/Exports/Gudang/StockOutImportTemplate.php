<?php

namespace App\Exports\Gudang;

use Maatwebsite\Excel\Concerns\FromArray;

class StockOutImportTemplate implements FromArray
{
    public function array(): array
    {
        return [
            [
                'doc_code',
                'doc_type',
                'doc_date (YYYY-MM-DD HH:MM:SS)',
                'product_packaging_id',
                'quantity',
                'warehouse_id',
            ],
        ];
    }
}