<?php

namespace App\Imports\Penjualan;

use App\Entities\Penjualan\MigrasiSoHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportSoHeader implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new MigrasiSoHeader([
            'id' => $row['id'],
            'customer' => $row['customer'],
            'bank' => $row['bank'] ?? null,
            'date' => Date::excelToDateTimeObject($row['date']),
            'code' => $row['code'],
            'subtotal' => $row['subtotal'] ?? 0.00,
            'disc_amount' => $row['disc_amount'] ?? 0.00,
            'disc_amount_2' => $row['disc_amount_2'] ?? 0.00,
            'disc_percent' => $row['disc_percent'] ?? 0.00,
            'disc_percent_2' => $row['disc_percent_2'] ?? 0.00,
            'ppn_idr' => $row['ppn_idr'] ?? 0.00,
            'ppn' => $row['ppn'] ?? 0.00,
            'grand_total' => $row['grand_total'] ?? 0.00,
            'brand' => $row['brand'] ?? null,
            'idr_rate' => $row['idr_rate'] ?? 0.00,
            'delivery_cost' => $row['delivery_cost'] ?? 0.00,
            'cost_resi' => $row['cost_resi'] ?? 0.00,
            'resi_note' => $row['resi_note'] ?? null,
            'created_at' => Date::excelToDateTimeObject($row['created_on']),
        ]);
    }
}