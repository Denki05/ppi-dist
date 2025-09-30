<?php

namespace App\Exports\Master;

use App\Entities\Master\CustomerOtherAddress;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerOtherAddressExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $columns = [
        'master_customer_other_addresses.id as id', 
        'master_customer_other_addresses.name', 
        'master_customer_other_addresses.member_default', 
        'master_customer_other_addresses.text_kota', 
        'master_customer_other_addresses.account_representative', 
        'master_customer_other_addresses.account_representative_optional_1', 
        'master_customer_other_addresses.account_representative_optional_2', 
        'master_customer_other_addresses.situation',
        'master_customers.pic as pic', // Adding a column from the joined 'customers' table
        'master_customer_other_addresses.officer as officer',
        'master_customer_other_addresses.account_representative as ar1',
        'master_customer_other_addresses.account_representative_optional_1 as ar2',
        'master_customer_other_addresses.account_representative_optional_2 as ar3',
        'master_customer_categories.name as category_name', // Including category name
        'master_customer_other_addresses.address', 
        'master_customer_other_addresses.text_provinsi', 
        'master_customer_other_addresses.text_kecamatan', 
        'master_customer_other_addresses.text_kelurahan', 
        'master_customer_other_addresses.zone',
        'master_customer_other_addresses.phone',
        'master_customer_other_addresses.contact_person',
        'master_customer_other_addresses.setting_income_target',
        'master_customers.has_tempo',
    ];

    private $headings = [
        'Id', 'Member Default', 'Nama', 'Owner', 'Category', 'Alamat', 'Pic', 'Office', 'AR1', 'AR2', 'AR3', 'Provinsi',
        'Kota', 'Kecamatan', 'Kelurahan', 'Zona', 'Nomer Telfon', 'Tipe Pembayaran', 'Target Omset'
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
            ->select($this->columns)
            ->join('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id') // Join with customers table
            ->join('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
            ->where('master_customers.status', $this->status)
            ->where('master_customers.existence', $this->existence);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        $paymentType = $row->has_tempo == 0 ? 'CASH' : 'TEMPO';
        $memberDefault = $row->member_default == 0 ? 'NO' : 'YES';

        return [
            $row->id, // ID member
            $memberDefault,
            $row->name . ' ' . $row->text_kota, // Combine name and city
            $row->contact_person ?? 'N/A', // Default to 'N/A' if null
            $row->category_name ?? 'Uncategorized', // Use category name
            $row->address ?? 'N/A', // Address handling
            $row->pic ?? 'N/A', // Address handling
            $row->officer ?? 'N/A', // Address handling
            $row->ar1 ?? 'N/A', // Address handling
            $row->ar2 ?? 'N/A', // Address handling
            $row->ar3 ?? 'N/A', // Address handling
            $row->text_provinsi ?? 'N/A',
            $row->text_kota ?? 'N/A',
            $row->text_kecamatan ?? 'N/A',
            $row->text_kelurahan ?? 'N/A',
            $row->zone ?? 'N/A',
            $row->phone ?? 'N/A', // Phone number
            $paymentType, // Payment type
            $row->setting_income_target,
        ];
    }
}
