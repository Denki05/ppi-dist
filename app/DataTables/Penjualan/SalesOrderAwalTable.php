<?php

namespace App\DataTables\Penjualan;

use App\DataTables\Table;
use App\Entities\Penjualan\SalesOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;

class SalesOrderAwalTable extends Table
{
    public function query(Request $request)
    {
        $isSuperuser = Auth::user()->is_superuser;
        $statusSoFilter = $request->status_so;
        $customerSoFilter = $request->customer_name;

        $model = SalesOrder::where('penjualan_so.type_so', 'nonppn')
            ->where('penjualan_so.so_indent', SalesOrder::INDENT['NO'])
            ->whereIn('penjualan_so.status', [1, 2, 3, 4])
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
                        WHEN penjualan_so.status = 1 THEN "AWAL"
                        WHEN penjualan_so.status = 2 THEN "LANJUTAN"
                        WHEN penjualan_so.status = 3 THEN "REVISI"
                        WHEN penjualan_so.status = 4 THEN "TUTUP"
                        ELSE "NONE"
                    END AS status_so
                '),
                DB::raw('
                    CASE 
                        WHEN penjualan_so.sales_id = 1 THEN "Lindy"
                        WHEN penjualan_so.sales_id = 2 THEN "Alivi"
                        WHEN penjualan_so.sales_id = 3 THEN "S.A"
                        WHEN penjualan_so.sales_id = 4 THEN "Santi"
                        WHEN penjualan_so.sales_id = 5 THEN "Eric"
                        ELSE "-"
                    END AS sales
                '),
                DB::raw('
                    CASE 
                        WHEN penjualan_so.created_by = 26 THEN "Lindy"
                        WHEN penjualan_so.created_by = 27 THEN "Alivi"
                        WHEN penjualan_so.created_by = 32 THEN "Nia"
                        WHEN penjualan_so.created_by = 33 THEN "Putri"
                        WHEN penjualan_so.created_by = 34 THEN "Santi"
                        WHEN penjualan_so.created_by = 35 THEN "Eric"
                        WHEN penjualan_so.created_by = 1 THEN "Dev"
                        ELSE "-"
                    END AS so_created_by
                ')
            );

        // Apply filters based on user permissions
        if (!$isSuperuser) {
            // Non-superuser: filter by created_by
            $model->where('penjualan_so.created_by', Auth::id());
        }

        // Apply date filter if no statusSoFilter is provided
        if(!$statusSoFilter) {
            $model->whereDate('penjualan_so.created_at', Carbon::now());
        } else {
            $model->where(DB::raw('
                CASE 
                    WHEN penjualan_so.status = 1 THEN "AWAL"
                    WHEN penjualan_so.status = 2 THEN "LANJUTAN"
                    WHEN penjualan_so.status = 3 THEN "REVISI"
                    WHEN penjualan_so.status = 4 THEN "TUTUP"
                    ELSE "NONE"
                END
            '), $statusSoFilter);
        }

        if ($customerSoFilter) {
            $model->where('penjualan_so.customer_other_address_id', $customerSoFilter);
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
            // Define the route for revising the sales order, setting the step to 1
            $revisi = route('superuser.penjualan.sales_order.edit', [$model->id, $step = 1]);
            $lanjutkan = route('superuser.penjualan.sales_order.lanjutkan', $model->id);
            $delete = route('superuser.penjualan.sales_order.destroy', $model->id);
            $print_so = route('superuser.penjualan.sales_order.print_so', $model->id);

            switch ($model->status_so) {
                case $model->status_so == "AWAL" OR $model->status_so == "REVISI":
                    return "
                        <a href=\"{$revisi}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Revisi\">
                                <i class=\"fa fa-pencil\"></i>
                            </button>
                        </a>

                        <a href=\"javascript:saveConfirmation('{$lanjutkan}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"Lanjutkan\">
                                <i class=\"fa fa-check\"></i>
                            </button>
                        </a>

                        <a href=\"javascript:saveConfirmation('{$delete}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                             <i class=\"fa fa-trash\"></i>
                            </button>
                        </a>

                        <a href=\"{$print_so}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"Print SO\">
                                <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                    ";

                case $model->status_so == "TUTUP" OR $model->status_so == "LANJUTAN":
                    return "
                        <a href=\"{$print_so}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"Print SO\">
                                <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                    ";
            }
        });

        return $table->make(true);
    }
}
