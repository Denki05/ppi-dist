<?php

namespace App\Exports\Master;

use App\Entities\Master\CustomerOtherAddress;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerOtherAddressExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $headings = [
        'id',
        'kategori',
        'zona',
        'customer',
        'kota',
        'provinsi',
        'sm/am',
        'asm',
        'pic',
        'officer',
        'is_default_member',
    ];

    private $status;
    private $existence;

    public function __construct($status, $existence)
    {
        $this->status = $status;
        $this->existence = $existence;
    }

    public function query()
    {
        return CustomerOtherAddress::query()
            ->select([
                'master_customer_other_addresses.id as id',
                'master_customer_categories.name as kategori',
                'master_customer_other_addresses.zone as zona',
                'master_customer_other_addresses.name as customer',
                'master_customer_other_addresses.text_kota as kota',
                'master_customer_other_addresses.text_provinsi as provinsi',
                'master_customer_other_addresses.sm as sm_am',
                'master_customer_other_addresses.ams as asm',
                'master_customer_other_addresses.pic as pic',
                'master_customer_other_addresses.officer as officer',
                'master_customer_other_addresses.member_default as is_default_member',
            ])
            ->join('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
            ->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
            ->where('master_customers.status', $this->status)
            ->where('master_customers.existence', $this->existence)
            ->orderBy('master_customer_other_addresses.name');
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->kategori,
            $row->zona,
            $row->customer,
            $row->kota,
            $row->provinsi,
            $row->sm_am,
            $row->asm,
            $row->pic,
            $row->officer,
            (int) $row->is_default_member,
        ];
    }
}
