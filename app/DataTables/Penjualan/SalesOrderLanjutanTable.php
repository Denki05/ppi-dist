<?php

namespace App\DataTables\Penjualan;

use App\DataTables\Table;
use App\Entities\Penjualan\SalesOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;

class SalesOrderLanjutanTable extends Table
{
    public function query(Request $request)
    {
        $statusSoFilter = $request->status_so;

        $model = SalesOrder::where('penjualan_so.type_so', 'nonppn')
            ->where('penjualan_so.so_indent', SalesOrder::INDENT['NO'])
            ->where('penjualan_so.is_archived', 0)
            ->whereIn('penjualan_so.status', [2, 4])
            // ->whereDate('penjualan_so.created_at', Carbon::now())
            ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->select(
                'penjualan_so.id AS id', 
                'penjualan_so.so_code AS so_code', 
                'penjualan_so.code AS code', 
                'penjualan_so.brand_name AS nota_brand', 
                'master_customer_other_addresses.name AS customer_name', 
                'master_customer_other_addresses.text_kota AS customer_kota', 
                'penjualan_so.customer_other_address_id AS customer_id', 
                'penjualan_so.created_at AS so_created_at', 
                DB::raw('
                    CASE 
                        WHEN penjualan_so.status = 2 THEN "LANJUTAN"
                        WHEN penjualan_so.status = 4 THEN "TUTUP"
                        ELSE "NONE"
                    END AS status_so
                '),
                DB::raw('
                    CASE 
                        WHEN penjualan_so.created_by = 26 THEN "Lindy"
                        WHEN penjualan_so.created_by = 27 THEN "Rita"
                        WHEN penjualan_so.created_by = 32 THEN "Nia"
                        WHEN penjualan_so.created_by = 33 THEN "Putri"
                        WHEN penjualan_so.created_by = 34 THEN "Santi"
                        WHEN penjualan_so.created_by = 35 THEN "Erick"
                        WHEN penjualan_so.created_by = 38 THEN "Kumala"
                        WHEN penjualan_so.created_by = 1 THEN "Dev"
                        ELSE "-"
                    END AS so_created_by
                '),
                'penjualan_so.type_transaction AS so_transaction',
            );

        if(!$statusSoFilter) {
            $model->whereDate('penjualan_so.created_at', Carbon::now());
        } else {
            $model->where(DB::raw('
                CASE 
                    WHEN penjualan_so.status = 2 THEN "LANJUTAN"
                    WHEN penjualan_so.status = 4 THEN "TUTUP"
                    ELSE "NONE"
                END
            '), $statusSoFilter);
        }

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->editColumn('so_created_at', function (SalesOrder $model) {
            return [
              'display' => Carbon::parse($model->so_created_at)->format('d/m/Y'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('customer', function (SalesOrder $model) {
            return $model->customer_name . ' ' . $model->customer_kota;
        });

        $table->addColumn('action', function (SalesOrder $model) {
            $kerjakan = route('superuser.penjualan.sales_order.edit', [$model->id, $step = 2]);
            $destroy = route('superuser.penjualan.sales_order.destroy_lanjutan', $model->id);
            $revisi = route('superuser.penjualan.sales_order.kembali', $model->id);
            $detail = route('superuser.penjualan.sales_order.detail', $model->id);

            switch ($model->status_so) {
                case $model->status_so == "LANJUTAN":
                    return "
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-primary btn-view\" data-toggle=\"modal\" data-target=\"#modalViewSo\" data-id=\"{$model->id}\" title=\"Show SO\">
                            <i class=\"fa fa-eye\"></i>
                        </button>

                        <a href=\"{$kerjakan}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"Lanjutkan\">
                                <i class=\"fa fa-check\"></i>
                            </button>
                        </a>

                        <a href=\"javascript:saveConfirmation('{$revisi}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Revisi\">
                                <i class=\"fa fa-times\"></i>
                            </button>
                        </a>

                        <a href=\"javascript:saveConfirmation('{$destroy}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                                <i class=\"fa fa-trash\"></i>
                            </button>
                        </a>


                    ";

                case $model->status_so == "TUTUP":
                    return "
                        <a href=\"{$detail}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"Detail\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                    ";
            }
        });

        return $table->make(true);
    }
}