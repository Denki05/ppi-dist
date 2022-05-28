<?php

namespace App\Exports\Master;

use App\Entities\Master\Contact;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContactExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'name', 'phone', 'email', 'position', 'dob', 'npwp', 'ktp', 'address', 'status'
    ];

    public function query()
    {
        return Contact::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->column;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->phone,
            $row->email,
            $row->position,
            $row->dob,
            $row->npwp,
            $row->ktp,
            $row->address,
            $row->status()
        ];
    }
}
