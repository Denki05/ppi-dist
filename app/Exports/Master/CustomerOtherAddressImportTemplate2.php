<?php

namespace App\Exports\Master;

use App\Entities\Master\CustomerOtherAddress;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Template import member yang sudah terisi otomatis (kategori, zona,
 * customer, kota, provinsi, dan is_default_member). Kolom sm/am, asm,
 * pic, officer sengaja kosong agar user hanya mengisi yang perlu.
 */
class CustomerOtherAddressImportTemplate2 implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
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
                'master_customer_other_addresses.member_default as member_default',
            ])
            ->join('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
            ->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
            ->where('master_customers.status', '1')
            ->where('master_customers.existence', '1')
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
            '',
            '',
            '',
            '',
            (int) $row->member_default,
        ];
    }
}
