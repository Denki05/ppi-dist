<?php

namespace App\Exports\Gudang;

use App\Entities\Gudang\Receiving;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class ReceivingExport implements FromCollection, WithHeadings, WithMapping
{
    protected $start_date;
    protected $end_date;

    public function __construct($start_date = null, $end_date = null)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $model = Receiving::leftJoin('master_warehouses', 'master_warehouses.id', '=', 'receiving.warehouse_id')
                    ->leftJoin('receiving_detail', 'receiving_detail.receiving_id', '=', 'receiving.id')
                    ->leftJoin('master_products_packaging', 'master_products_packaging.id', '=', 'receiving_detail.product_packaging_id')
                    ->when(!empty($this->start_date), function ($query) {
                        $query->whereDate(DB::raw('COALESCE(receiving.pbm_date, receiving.created_at)'), '>=', $this->start_date);
                    })
                    ->when(!empty($this->end_date), function ($query) {
                        $query->whereDate(DB::raw('COALESCE(receiving.pbm_date, receiving.created_at)'), '<=', $this->end_date);
                    })
                    ->select(
                        'receiving.code AS receiving_code',
                        'receiving.pbm_date AS receiving_date',
                        'receiving.created_at AS created_date',
                        'master_warehouses.name AS warehouse_name',
                        'master_products_packaging.code AS product_code',
                        'master_products_packaging.name AS product_name',
                        'receiving_detail.no_batch AS batch_no',
                        'receiving_detail.quantity_po AS qty_po',
                        'receiving_detail.quantity_ri AS qty_ri',
                        'receiving.status AS receiving_status'
                    )
                    ->orderBy('receiving.created_at', 'DESC')
                    ->orderBy('master_products_packaging.name', 'ASC')
                    ->get();

        return $model;
    }

    public function map($model): array
    {
        $varian = trim(($model->product_code ? $model->product_code . ' - ' : '') . ($model->product_name ?? ''));
        $status = array_search($model->receiving_status, \App\Entities\Gudang\Receiving::STATUS);

        return [
            [
                $model->receiving_code,
                $model->receiving_date ?: $model->created_date,
                $model->warehouse_name,
                $varian,
                $model->batch_no,
                $model->qty_po,
                $model->qty_ri,
                $status !== false ? $status : $model->receiving_status,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Kode Receiving',
            'Tanggal',
            'Warehouse',
            'Produk',
            'Batch',
            'Qty PO',
            'Qty Diterima',
            'Status',
        ];
    }
}
