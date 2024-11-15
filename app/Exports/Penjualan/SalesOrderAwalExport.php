<?php

namespace App\Exports\Penjualan;

use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class SalesOrderAwalExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $indent = SalesOrder::leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
            ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->where('penjualan_so.condition', 1)
            ->where('penjualan_so.so_indent', 0)
            ->select(
                'penjualan_so.so_code as indentCode', 
                'penjualan_so.code as invoiceCode',
                'penjualan_so.created_at as indentDate', 
                'penjualan_so_item.qty as indetQty', 
                'master_customer_other_addresses.name as indentCustomer',
                'master_customer_other_addresses.text_kota as indentCustomerKota',
                'master_products_packaging.code as indentProductCode',
                'master_products_packaging.name as indentProductName',
                'master_packaging.pack_name as indentKemasan',
                DB::raw('(
                    CASE 
                        WHEN penjualan_so.status = 1 THEN "Awal" 
                        WHEN penjualan_so.status = 2 THEN "Lanjutan" 
                        WHEN penjualan_so.status = 3 THEN "Revisi" 
                        WHEN penjualan_so.status = 4 THEN "Tutup" 
                        ELSE "Null" END) AS soStatus
                '),
                DB::raw('(
                    CASE 
                        WHEN penjualan_so.sales_senior_id = 1 THEN "Ivan" 
                        WHEN penjualan_so.sales_senior_id = 2 THEN "Nia" 
                        WHEN penjualan_so.sales_senior_id = 3 THEN "Lindy" 
                        ELSE "Null" END) AS indentSalesSenior
                '),
                DB::raw('(
                    CASE 
                        WHEN penjualan_so.sales_id = 1 THEN "Lindy" 
                        WHEN penjualan_so.sales_id = 2 THEN "Rita" 
                        WHEN penjualan_so.sales_id = 3 THEN "SA" 
                        WHEN penjualan_so.sales_id = 4 THEN "Santi" 
                        WHEN penjualan_so.sales_id = 5 THEN "Rudy" 
                        ELSE "Null" END) AS indentSalesman
                '),
            )
            ->orderBy('penjualan_so.created_at', 'ASC')
            ->get();

        return $indent;
    }

    public function map($indent): array
    {
        return [
            [
                $indent->indentDate,
                $indent->indentCode,
                $indent->invoiceCode,
                $indent->indentSalesSenior,
                $indent->indentSalesman,
                $indent->indentCustomer. ' ' . $indent->indentCustomerKota,
                $indent->indentProductCode,
                $indent->indentProductName,
                $indent->indentKemasan,
                $indent->indetQty,
                $indent->soStatus,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal SO', 
            'SO Code',
            'invoice',
            'Sales Senior', 
            'Salesman',
            'Customer', 
            'Product Code', 
            'Product Name', 
            'Kemasan', 
            'Quantity',
            'Status',
        ];
    }
}