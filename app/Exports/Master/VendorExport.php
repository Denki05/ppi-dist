<?php

namespace App\Exports\Master;

use App\Entities\Master\Vendor;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VendorExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'code', 'name', 'address', 'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan', 'zipcode', 'email', 'phone', 'website', 'owner_name', 'description', 'status'
    ];

    private $headings = [
        'id', 'code', 'name', 'address', 'provinsi', 'kota', 'kecamatan', 'kelurahan', 'zipcode', 'email', 'phone', 'website', 'owner_name', 'description', 'status'
    ];

    public function query()
    {
        return Vendor::query()->select($this->column);
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
            $row->address,
            $row->text_provinsi,
            $row->text_kota,
            $row->text_kecamatan,
            $row->text_kelurahan,
            $row->zipcode,
            $row->email,
            $row->phone,
            $row->website,
            $row->owner_name,
            $row->description,
            $row->status()
        ];
    }
}
