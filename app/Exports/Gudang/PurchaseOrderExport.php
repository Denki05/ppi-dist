<?php

namespace App\Exports\Gudang;

use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class PurchaseOrderExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $model = PurchaseOrder::leftJoin('purchase_order_detail', 'purchase_order_detail.po_id', '=', 'purchase_order.id')
                    ->leftJoin('master_warehouses', 'master_warehouses.id', '=', 'purchase_order.warehouse_id')
                    ->leftJoin('master_products_packaging', 'master_products_packaging.id', '=', 'purchase_order_detail.product_packaging_id')
                    ->leftJoin('master_packaging', 'master_packaging.id', '=', 'master_products_packaging.packaging_id')
                    ->select(
                        'purchase_order.code AS po_code',
                        'master_warehouses.name AS warehouse_name', 
                        'purchase_order.created_at AS tanggal_pembuatan', 
                        'master_products_packaging.code AS product_code', 
                        'master_products_packaging.name AS product_name', 
                        'purchase_order_detail.quantity AS po_quantity', 
                        'master_packaging.pack_name AS packaging', 
                        'purchase_order_detail.note_repack AS customer',
                        DB::raw('(
                            CASE 
                                WHEN purchase_order.status = 1 THEN "ACTIVE" 
                                WHEN purchase_order.status = 2 THEN "ACC" 
                                WHEN purchase_order.status = 3 THEN "DRAFT" 
                                ELSE "Null" END) AS po_status
                        '),
                    )
                    ->get();

        return $model;
    }

    public function map($model): array
    {
        return [
            [
                $model->po_code,
                $model->warehouse_name,
                $model->tanggal_pembuatan,
                $model->product_code. ' - ' . $model->product_name,
                $model->po_quantity,
                $model->packaging,
                $model->customer,
                $model->po_status,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'PO Code', 
            'Warehouse',
            'Tgl Pembuatan',
            'Nama Varian', 
            'Qty',
            'Packaging', 
            'Customer', 
            'Status', 
        ];
    }
}