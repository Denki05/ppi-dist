<?php

namespace App\Exports\Penjualan;

use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesOrderIndentExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // $indent = SalesOrder::with('so_detail')
        //     ->where('indent_status', SalesOrder::INDENT_STATUS['INDENT'])
        //     ->orWhere('indent_status', SalesOrder::INDENT_STATUS['FULL'])
        //     ->get();

        // dd($indent);
        $indent = SalesOrder::leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
            ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->where('penjualan_so.indent_status', SalesOrder::INDENT_STATUS['INDENT'])
            ->orWhere('penjualan_so.indent_status', SalesOrder::INDENT_STATUS['FULL'])
            ->select(
                'penjualan_so.so_code as indentCode', 
                'penjualan_so.created_at as indentDate', 
                'penjualan_so_item.qty as indetQty', 
                'master_customer_other_addresses.name as indentCustomer',
                'master_customer_other_addresses.text_kota as indentCustomerKota',
                'master_products_packaging.code as indentProductCode',
                'master_products_packaging.name as indentProductName',
                'master_packaging.pack_name as indentKemasan',
            )
            ->get();
        // dd($indent);

        return $indent;
    }

    public function map($indent): array
    {
        return [
            [
                $indent->indentDate,
                $indent->indentCode,
                $indent->indentCustomer. ' ' . $indent->indentCustomerKota,
                $indent->indentProductCode,
                $indent->indentProductName,
                $indent->indentKemasan,
                $indent->indetQty,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal Indent', 
            'Code Indent',
            'Customer', 
            'Product Code', 
            'Product Name', 
            'Kemasan', 
            'Quantity',
        ];
    }
}