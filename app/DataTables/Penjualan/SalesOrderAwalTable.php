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
        $tab = $request->tab ?: 'default';
        $statusSoFilter = $request->status_so;
        $customerSoFilter = $request->customer_name;
        $brandFilter = $request->brand_name;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $statusMap = [
            'AWAL' => 1,
            'LANJUTAN' => 2,
            'REVISI' => 3,
            'TUTUP' => 4,
        ];

        $model = SalesOrder::where('penjualan_so.type_so', 'nonppn')
            ->where('penjualan_so.so_indent', SalesOrder::INDENT['NO'])
            ->where('penjualan_so.is_archived', 0)
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
                'penjualan_so.is_proforma AS is_proforma',
                'penjualan_so.is_estimate AS is_estimate',
                'penjualan_so.status_proforma AS status_proforma',
                'penjualan_so.idr_rate AS idr_rate',
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
                        WHEN penjualan_so.sales_id = 2 THEN "Kumala"
                        WHEN penjualan_so.sales_id = 3 THEN "S.A"
                        WHEN penjualan_so.sales_id = 4 THEN "Santi"
                        WHEN penjualan_so.sales_id = 5 THEN "Erick"
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
                        WHEN penjualan_so.created_by = 35 THEN "Erick"
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
                // Tgl resi terakhir (DO status 6) — dipakai tab history.
                DB::raw('(SELECT MAX(po.updated_at) FROM penjualan_do AS po WHERE po.so_id = penjualan_so.id AND po.status = 6) AS resi_at'),
            );

        if (!$isSuperuser) {
            $model->where('penjualan_so.created_by', Auth::id());
        }

        // ============ TAB: default / proses / history ============
        if ($tab === 'history') {
            // SO yang DO-nya sudah update resi (status 6). Default: bulan berjalan.
            $model->whereExists(function ($q) use ($dateFrom, $dateTo, $bulan, $tahun) {
                $q->select(DB::raw(1))
                    ->from('penjualan_do AS po')
                    ->whereColumn('po.so_id', 'penjualan_so.id')
                    ->where('po.status', 6);
                if ($dateFrom && $dateTo) {
                    $q->whereDate('po.updated_at', '>=', $dateFrom)
                      ->whereDate('po.updated_at', '<=', $dateTo);
                } else {
                    $q->whereMonth('po.updated_at', $bulan ?: Carbon::now()->month)
                      ->whereYear('po.updated_at', $tahun ?: Carbon::now()->year);
                }
            });
            $model->orderByDesc('resi_at');
        } elseif ($tab === 'proses') {
            // (a) Draft/revisi lewat hari + (b) sudah dilanjutkan tapi belum update resi.
            $model->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereIn('penjualan_so.status', [1, 3])
                       ->whereDate('penjualan_so.created_at', '<', Carbon::today());
                })->orWhere(function ($q2) {
                    $q2->whereIn('penjualan_so.status', [2, 4])
                       ->whereNotExists(function ($q3) {
                           $q3->select(DB::raw(1))
                               ->from('penjualan_do AS po')
                               ->whereColumn('po.so_id', 'penjualan_so.id')
                               ->where('po.status', 6);
                       });
                });
            });
            if ($statusSoFilter && isset($statusMap[$statusSoFilter])) {
                $model->where('penjualan_so.status', $statusMap[$statusSoFilter]);
            }
            $this->applySoDateFilter($model, $dateFrom, $dateTo, $bulan, $tahun);
        } else {
            // Default: draft (AWAL + REVISI) yang belum terproses, default hari ini.
            $model->whereIn('penjualan_so.status', [1, 3]);
            if ($statusSoFilter && isset($statusMap[$statusSoFilter])) {
                $model->where('penjualan_so.status', $statusMap[$statusSoFilter]);
            }
            if ($dateFrom && $dateTo) {
                $model->whereDate('penjualan_so.created_at', '>=', $dateFrom)
                      ->whereDate('penjualan_so.created_at', '<=', $dateTo);
            } elseif (!$statusSoFilter) {
                $model->whereDate('penjualan_so.created_at', Carbon::today());
            }
        }

        if ($customerSoFilter) {
            $model->where('master_customer_other_addresses.name', 'like', '%' . $customerSoFilter . '%');
        }

        if ($brandFilter) {
            $model->where('penjualan_so.brand_name', 'like', '%' . $brandFilter . '%');
        }

        return $model;
    }

    /**
     * Filter tanggal (rentang prioritas, lalu bulan+tahun) pada created_at SO.
     * Dipakai tab proses agar bisa kombinasi bulan/tahun berjalan maupun beda.
     */
    protected function applySoDateFilter($model, $dateFrom, $dateTo, $bulan, $tahun)
    {
        if ($dateFrom && $dateTo) {
            $model->whereDate('penjualan_so.created_at', '>=', $dateFrom)
                  ->whereDate('penjualan_so.created_at', '<=', $dateTo);
        } elseif ($bulan || $tahun) {
            if ($bulan) {
                $model->whereMonth('penjualan_so.created_at', $bulan);
            }
            if ($tahun) {
                $model->whereYear('penjualan_so.created_at', $tahun);
            }
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

        $table->editColumn('resi_at', function ($model) {
            return $model->resi_at
                ? Carbon::parse($model->resi_at)->format('d/m/Y H:i')
                : '-';
        });

        $table->filterColumn('sales', function($query, $keyword) {
            $query->whereRaw("
                CASE 
                    WHEN penjualan_so.sales_id = 1 THEN 'Lindy'
                    WHEN penjualan_so.sales_id = 2 THEN 'Kumala'
                    WHEN penjualan_so.sales_id = 3 THEN 'S.A'
                    WHEN penjualan_so.sales_id = 4 THEN 'Santi'
                    WHEN penjualan_so.sales_id = 5 THEN 'Erick'
                    ELSE '-'
                END LIKE ?", ["%{$keyword}%"]);
        });

        $table->filterColumn('so_created_by', function($query, $keyword) {
            $query->whereRaw("
                CASE 
                    WHEN penjualan_so.created_by = 26 THEN 'Lindy'
                    WHEN penjualan_so.created_by = 38 THEN 'Kumala'
                    WHEN penjualan_so.created_by = 32 THEN 'Nia'
                    WHEN penjualan_so.created_by = 33 THEN 'Putri'
                    WHEN penjualan_so.created_by = 34 THEN 'Santi'
                    WHEN penjualan_so.created_by = 35 THEN 'Erick'
                    WHEN penjualan_so.created_by = 1 THEN 'Dev'
                    ELSE '-'
                END LIKE ?", ["%{$keyword}%"]);
        });

        $table->filterColumn('status_so', function($query, $keyword) {
            $query->whereRaw("
                CASE 
                    WHEN penjualan_so.status = 1 THEN 'AWAL'
                    WHEN penjualan_so.status = 2 THEN 'LANJUTAN'
                    WHEN penjualan_so.status = 3 THEN 'REVISI'
                    WHEN penjualan_so.status = 4 THEN 'TUTUP'
                    ELSE 'NONE'
                END LIKE ?", ["%{$keyword}%"]);
        });

        $table->filterColumn('approval_mou_status', function($query, $keyword) {
            $query->whereRaw("
                CASE 
                    WHEN penjualan_so.approval_mou_status = 0 THEN 'NOT APPROVED'
                    WHEN penjualan_so.approval_mou_status = 1 THEN 'APPROVED'
                    ELSE '-'
                END LIKE ?", ["%{$keyword}%"]);
        });

        $table->filterColumn('so_created_at', function($query, $keyword) {
            $query->where('penjualan_so.created_at','like',"%{$keyword}%");
        });

        $table->addColumn('customer', function ($model) {
            return $model->customer_name . ' ' . $model->customer_kota;
        });

        $table->addColumn('action', function ($model) {
            $revisi = route('superuser.penjualan.sales_order.edit', [$model->id, $step = 1]);
            $lanjutkan = route('superuser.penjualan.sales_order.lanjutkan', $model->id);
            $delete = route('superuser.penjualan.sales_order.destroy', $model->id);
            $print_so = route('superuser.penjualan.sales_order.print_so', $model->id);
            $estimate_pdf = route('superuser.penjualan.sales_order.sales_estimate_pdf', $model->id);
            $archive = route('superuser.penjualan.sales_order.archive_one_awal', $model->id);

            // Tombol arsip manual hanya untuk divisi yang boleh (sama seperti Riwayat Archive)
            $canArchive = in_array(Auth::user()->division, ['Admin', 'Developer', 'Management']);
            $btn_archive = '';
            if ($canArchive) {
                $btn_archive = "
                    <a href=\"javascript:saveConfirmation('{$archive}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Arsipkan\">
                            <i class=\"fa fa-archive\"></i>
                        </button>
                    </a>
                ";
            }

            $buttons = '';

            // -----------------------------------------------------------
            // LOGIKA WARNING KURS: Jika kosong atau <= 1 cegah cetak PDF
            // -----------------------------------------------------------
            $btn_estimate = "";
            // Tampilkan icon PDF HANYA jika is_estimate = 1
            if ($model->is_estimate == 1) {
                if (empty($model->idr_rate) || (float) $model->idr_rate <= 1) {
                    $btn_estimate = "
                        <a href=\"javascript:void(0);\" onclick=\"Swal.fire('Peringatan!', 'Kurs belum di setting silahkan edit SO anda', 'warning');\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-primary\" title=\"Print Sales Estimate (Cetak Sebelum Lanjut)\">
                                <i class=\"fa fa-file-pdf-o\"></i>
                            </button>
                        </a>
                    ";
                } else {
                    $btn_estimate = "
                        <a href=\"{$estimate_pdf}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-primary\" title=\"Print Sales Estimate (Cetak Sebelum Lanjut)\">
                                <i class=\"fa fa-file-pdf-o\"></i>
                            </button>
                        </a>
                    ";
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PRIORITY CHECK: SO AWAL + PROFORMA
            |--------------------------------------------------------------------------
            */

            if ($model->status_so === 'AWAL' && $model->is_proforma == 1) {
                // PROFORMA SUDAH DIBUAT = sudah dilanjutkan (CASH) - hanya Print SO, tanpa Arsip
                if (in_array($model->status_proforma, [1, 2, 3, 4])) {
                    $buttons .= "
                        <a href=\"{$print_so}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"Print SO\">
                                <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                    ";
                    return $buttons;
                }
            }
        
            if ($model->status_so === 'AWAL') {
                if ($model->approval_mou == "YES" && $model->approval_mou_status != "APPROVED") {

                    // JIKA BUTUH APPROVAL: belum dilanjutkan -> tanpa Print SO, hanya Estimate
                    $buttons .= $btn_estimate;
                    $buttons .= $btn_archive;

                } else {

                    // NORMAL / APPROVED: belum dilanjutkan -> Revisi, Lanjut, Delete + Print SO
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

                        <a href=\"{$print_so}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"Print SO\">
                                <i class=\"fa fa-print\"></i>
                            </button>
                        </a>

                        <a href=\"javascript:saveConfirmation('{$delete}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                                <i class=\"fa fa-trash\"></i>
                            </button>
                        </a>
                    ";

                    // Tombol Estimate ditaruh terakhir, lalu Arsip manual
                    $buttons .= $btn_estimate;
                    $buttons .= $btn_archive;
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

                    <a href=\"{$print_so}\" target=\"_blank\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"Print SO\">
                            <i class=\"fa fa-print\"></i>
                        </button>
                    </a>

                    <a href=\"javascript:saveConfirmation('{$delete}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                            <i class=\"fa fa-trash\"></i>
                        </button>
                    </a>
                ";

                $buttons .= $btn_estimate;

            } elseif (in_array($model->status_so, ['TUTUP', 'LANJUTAN'])) {
                $buttons .= "
                    <a href=\"{$print_so}\" target=\"_blank\">
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