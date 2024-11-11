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
        'master_customer_other_addresses.id', 
        'master_customer_other_addresses.name', 
        'master_customer_other_addresses.member_default', 
        'master_customer_other_addresses.text_kota', 
        'master_customer_other_addresses.officer',
        'master_customer_other_addresses.account_representative', 
        'master_customer_other_addresses.account_representative_optional_1', 
        'master_customer_other_addresses.account_representative_optional_2', 
        'master_customer_other_addresses.situation',
        'master_customers.pic' // Adding a column from the joined 'customers' table
    ];

    private $headings = [
        'ID', 'Name', 'Member Default', 'PIC', 'Officer',
        'AR1', 'AR2', 'AR3'
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
            ->where('master_customers.status', $this->status)
            ->where('master_customers.existence', $this->existence);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return [
            $row->id,
            implode(' ', [$row->name, $row->text_kota]),
            $row->default(),
            $row->pic,
            $row->officer,
            $row->account_representative,
            $row->account_representative_optional_1,
            $row->account_representative_optional_2,
        ];
    }
}
