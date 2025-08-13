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

        $statusMap = [
            'AWAL' => 1,
            'LANJUTAN' => 2,
            'REVISI' => 3,
            'TUTUP' => 4,
        ];

        $model = SalesOrder::where('penjualan_so.type_so', 'nonppn')
            ->where('penjualan_so.so_indent', SalesOrder::INDENT['NO'])
            ->whereIn('penjualan_so.status', array_values($statusMap))
            ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->select(
                'penjualan_so.id AS id', 
                'penjualan_so.so_code AS so_code', 
                'penjualan_so.code AS code', 
                'penjualan_so.brand_name AS nota_brand', 
                'penjualan_so.approval_mou AS approval_mou', 
                'penjualan_so.approval_mou_status AS approval_mou_status', 
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
                        WHEN penjualan_so.created_by = 38 THEN "Kumala"
                        WHEN penjualan_so.created_by = 32 THEN "Nia"
                        WHEN penjualan_so.created_by = 33 THEN "Putri"
                        WHEN penjualan_so.created_by = 34 THEN "Santi"
                        WHEN penjualan_so.created_by = 35 THEN "Eric"
                        WHEN penjualan_so.created_by = 1 THEN "Dev"
                        ELSE "-"
                    END AS so_created_by
                '),
                DB::raw('
                    CASE 
                        WHEN penjualan_so.approval_mou = 0 THEN "NO"
                        WHEN penjualan_so.approval_mou = 1 THEN "YES"
                        ELSE "-"
                    END AS approval_mou
                '),
                DB::raw('
                    CASE 
                        WHEN penjualan_so.approval_mou_status = 0 THEN "NOT APPROVED"
                        WHEN penjualan_so.approval_mou_status = 1 THEN "APPROVED"
                        ELSE "-"
                    END AS approval_mou_status
                '),
            );

        if (!$isSuperuser) {
            $model->where('penjualan_so.created_by', Auth::id());
        }

        if ($statusSoFilter && isset($statusMap[$statusSoFilter])) {
            $model->where('penjualan_so.status', $statusMap[$statusSoFilter]);
        } elseif (!$statusSoFilter) {
            $model->whereDate('penjualan_so.created_at', Carbon::now());
        }

        if ($customerSoFilter) {
            $model->where('master_customer_other_addresses.name', 'like', '%' . $customerSoFilter . '%');
        }

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->editColumn('so_created_at', function ($model) {
            return [
                'display' => Carbon::parse($model->so_created_at)->format('d/m/Y'),
                'timestamp' => $model->so_created_at
            ];
        });

        $table->addColumn('customer', function ($model) {
            return $model->customer_name . ' ' . $model->customer_kota;
        });

        $table->addColumn('action', function ($model) {
            $revisi = route('superuser.penjualan.sales_order.edit', [$model->id, $step = 1]);
            $lanjutkan = route('superuser.penjualan.sales_order.lanjutkan', $model->id);
            $delete = route('superuser.penjualan.sales_order.destroy', $model->id);
            $print_so = route('superuser.penjualan.sales_order.print_so', $model->id);
        
            $buttons = '';
        
            if ($model->status_so === 'AWAL') {
                if ($model->approval_mou == "YES" && $model->approval_mou_status != "APPROVED") {
                    // jika ada permintaan approval
                    $buttons .= "
                        <a href=\"{$print_so}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"Print SO\">
                                <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                    ";
                } else {
                    // jka ada permintaan approval dan sudah di approve
                    $buttons .= "
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
                }
            } elseif ($model->status_so === 'REVISI') {
                $buttons .= "
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
            } elseif (in_array($model->status_so, ['TUTUP', 'LANJUTAN'])) {
                $buttons .= "
                    <a href=\"{$print_so}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"Print SO\">
                            <i class=\"fa fa-print\"></i>
                        </button>
                    </a>
                ";
            }
        
            return $buttons;
        });        

        return $table->make(true);
    }
}