<?php

namespace App\Exports\Master;

use App\Entities\Master\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'category_id', /* 'type_id', */ 'code', 'name',
        'email', 'phone', 'npwp', 'address',
        'owner_name', 'website', 'plafon_piutang',
        'gps_latitude', 'gps_longitude', 'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'status'
    ];

    private $headings = [
        'id', 'category', 'type', 'code', 'name',
        'email', 'phone', 'npwp', 'address',
        'owner_name', 'website', 'plafon_piutang',
        'gps_latitude', 'gps_longitude', 'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'status'
    ];

    public function query()
    {
        return Customer::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->category->name,
            implode(', ', $row->types->pluck('name')->toArray()),
            $row->code,
            $row->name,
            $row->email,
            $row->phone,
            $row->npwp,
            $row->address,
            $row->owner_name,
            $row->website,
            $row->plafon_piutang,
            $row->gps_latitude,
            $row->gps_longitude,
            $row->text_provinsi,
            $row->text_kota,
            $row->text_kecamatan,
            $row->text_kelurahan,
            $row->status()
        ];
    }
}
