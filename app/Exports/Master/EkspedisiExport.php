<?php

namespace App\Exports\Master;

use App\Entities\Master\Ekspedisi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EkspedisiExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'code', 'name', 'address',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan', 'zipcode',
        'email', 'phone', 'fax',
        'website', 'owner_name',
        'status'
    ];

    public function query()
    {
        return Ekspedisi::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->column;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->code,
            $row->name,
            $row->address,
            $row->text_provinsi,
            $row->text_kota,
            $row->text_kecamatan,
            $row->text_kelurahan,
            $row->zipcode,
            $row->email,
            $row->phone,
            $row->fax,
            $row->website,
            $row->owner_name,
            $row->status()
        ];
    }
}
