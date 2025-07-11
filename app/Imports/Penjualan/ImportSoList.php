<?php

namespace App\Imports\Penjualan;

use App\Entities\Penjualan\MigrasiSoList;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportSoList implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new MigrasiSoList([
            'so_id' => $row['so_id'],
            'brand' => $row['brand'],
            'product_code' => $row['product_code'] ?? null,
            'product_name' => $row['product_name'] ?? null,
            'packaging' => $row['packaging'] ?? null,
            'qty' => $row['qty'] ?? 0,
            'item_price' => $row['item_price'] ?? 0,
            'item_disc_amount' => $row['item_disc_amount'] ?? 0,
            'item_disc_amount_2' => $row['item_disc_amount_2'] ?? 0,
            'item_disc_percent' => $row['item_disc_percent'] ?? 0,
            'item_disc_percent_2' => $row['item_disc_percent_2'] ?? 0,
            'item_total_row' => $row['item_total_row'] ?? 0,
        ]);
    }
}