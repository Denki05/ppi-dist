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

        // Jika user sedang mengetik di kotak search, batas tanggal default dilepas
        // supaya pencarian menelusuri semua tanggal di tab yang aktif.
        // Preset "Semua" (show_all=1) juga melepas batas agar SO lama tetap ketemu.
        $hasKeyword = trim((string) $request->input('search.value', '')) !== '';
        $showAll = (string) $request->input('show_all', '') === '1';

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
                'penjualan_so.submitted_at AS submitted_at',
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
                        WHEN penjualan_so.approval_mou = 0 THEN "-"
                        WHEN penjualan_so.approval_mou_status = 0 THEN "NOT APPROVED"
                        WHEN penjualan_so.approval_mou_status = 1 THEN "APPROVED"
                        ELSE "-"
                    END AS approval_mou_status
                ')
            );

        if (!$isSuperuser) {
            $model->where('penjualan_so.created_by', Auth::id());
        }

        // ============ TAB: draft / submit / selesai (resi) ============
        // Backward-compat: default=>draft, history=>selesai, proses=>submit.
        if ($tab === 'default') {
            $tab = 'draft';
        } elseif ($tab === 'history') {
            $tab = 'selesai';
        } elseif ($tab === 'proses') {
            $tab = 'submit';
        }
        if ($tab === 'selesai') {
            // Tgl resi terakhir (DO status 6) — hanya dihitung di tab ini.
            $model->addSelect(DB::raw('(SELECT MAX(po.updated_at) FROM penjualan_do AS po WHERE po.so_id = penjualan_so.id AND po.status = 6) AS resi_at'));

            // RESI: history SO yang DO-nya sudah update resi (status 6).
            // Prioritas: range tanggal resi -> bulan/tahun -> bulan berjalan.
            // show_all / keyword melepas batas agar SO lama tetap ketemu via search.
            $model->whereExists(function ($q) use ($dateFrom, $dateTo, $bulan, $tahun, $showAll, $hasKeyword) {
                $q->select(DB::raw(1))
                    ->from('penjualan_do AS po')
                    ->whereColumn('po.so_id', 'penjualan_so.id')
                    ->where('po.status', 6);
                if ($dateFrom && $dateTo) {
                    $q->whereDate('po.updated_at', '>=', $dateFrom)
                      ->whereDate('po.updated_at', '<=', $dateTo);
                } elseif (!$showAll && !$hasKeyword) {
                    $q->whereMonth('po.updated_at', $bulan ?: Carbon::now()->month)
                      ->whereYear('po.updated_at', $tahun ?: Carbon::now()->year);
                }
            });
            $model->orderByDesc('resi_at');
        } elseif ($tab === 'submit') {
            // SUBMIT: SO yang sudah dilanjutkan ke SO Lanjutan (2) ATAU sudah
            // tutup (4) tapi belum update resi. Default jalan per hari ini
            // (dibuat ATAU disubmit hari ini), beda tanggal via filter.
            // Jika ada kata kunci pencarian, default hari ini dilepas.
            $model->where(function ($q) {
                $q->where('penjualan_so.status', 2)
                  ->orWhere(function ($q2) {
                      $q2->where('penjualan_so.status', 4)
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
            if ($dateFrom && $dateTo) {
                // Range berlaku untuk tanggal buat ATAU tanggal submit.
                $model->where(function ($q) use ($dateFrom, $dateTo) {
                    $q->whereDate('penjualan_so.created_at', '>=', $dateFrom)
                      ->whereDate('penjualan_so.created_at', '<=', $dateTo)
                      ->orWhere(function ($q2) use ($dateFrom, $dateTo) {
                          $q2->whereDate('penjualan_so.submitted_at', '>=', $dateFrom)
                            ->whereDate('penjualan_so.submitted_at', '<=', $dateTo);
                      });
                });
            } elseif (!$statusSoFilter && !$bulan && !$tahun && !$hasKeyword && !$showAll) {
                $model->where(function ($q) {
                    $q->whereDate('penjualan_so.created_at', Carbon::today())
                      ->orWhereDate('penjualan_so.submitted_at', Carbon::today());
                });
            } else {
                $this->applySoDateFilter($model, $dateFrom, $dateTo, $bulan, $tahun);
            }
            $model->orderBy('penjualan_so.created_at', 'desc');
        } else {
            // DRAFT: AWAL (1) + REVISI (3). Semua draft yang belum disubmit
            // selalu tampil (tanpa batas hari ini); filter tanggal opsional.
            $tab = 'draft';
            $model->whereIn('penjualan_so.status', [1, 3]);
            if ($statusSoFilter && isset($statusMap[$statusSoFilter])) {
                $model->where('penjualan_so.status', $statusMap[$statusSoFilter]);
            }
            if ($dateFrom && $dateTo) {
                $model->whereDate('penjualan_so.created_at', '>=', $dateFrom)
                      ->whereDate('penjualan_so.created_at', '<=', $dateTo);
            }
            // Terbaru dulu: SO yang baru dibuat langsung ketemu di baris 1.
            $model->orderBy('penjualan_so.created_at', 'desc');
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

    protected function fmtTgl($value, $format = 'd/m/Y')
    {
        return $value ? Carbon::parse($value)->format($format) : '-';
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $reqTab = $request->tab;
        $isSelesai = in_array($reqTab, ['selesai', 'history']);
        $isSubmit = in_array($reqTab, ['submit', 'proses']);

        $table->filterColumn('customer', function ($query, $keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('master_customer_other_addresses.name', 'like', "%{$keyword}%")
                  ->orWhere('master_customer_other_addresses.text_kota', 'like', "%{$keyword}%");
            });
        });

        $table->addColumn('status_label', function ($model) use ($isSelesai) {
            if ($isSelesai) {
                return 'TERKIRIM';
            }
            $map = ['AWAL' => 'AWAL', 'REVISI' => 'REVISI', 'LANJUTAN' => 'SUBMIT', 'TUTUP' => 'DIPROSES'];
            return $map[$model->status_so] ?? '-';
        });

        $table->addColumn('tgl_info', function ($model) use ($isSelesai, $isSubmit) {
            if ($isSelesai) {
                return 'Resi ' . $this->fmtTgl($model->resi_at, 'd/m/Y H:i');
            }
            $txt = 'Dibuat ' . $this->fmtTgl($model->so_created_at);
            if ($isSubmit) {
                $txt .= ' · Submit ' . $this->fmtTgl($model->submitted_at);
                return $txt;
            }
            // Psikologi AO: umur draft ditampilkan eksplisit agar terasa "nyantol".
            try {
                $days = Carbon::parse($model->so_created_at)->startOfDay()
                    ->diffInDays(Carbon::now()->startOfDay());
            } catch (\Exception $e) {
                $days = 0;
            }
            if ($days <= 0) {
                $txt .= ' · hari ini';
            } elseif ($days == 1) {
                $txt .= ' · 1 hari belum lanjut';
            } else {
                $txt .= ' · ' . $days . ' hari belum lanjut';
            }
            return $txt;
        });

        $table->addColumn('umur_hari', function ($model) {
            try {
                return (int) Carbon::parse($model->so_created_at)->startOfDay()
                    ->diffInDays(Carbon::now()->startOfDay());
            } catch (\Exception $e) {
                return 0;
            }
        });

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
                    WHEN penjualan_so.approval_mou = 0 THEN '-'
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

        // Submit & Terkirim = riwayat read-only. Semua aksi hanya di Draft
        // agar AO fokus di satu tempat dan tidak terkecoh klik di tab lain.
        $table->addColumn('action', function ($model) use ($isSubmit, $isSelesai) {
            if ($isSubmit || $isSelesai) {
                return '<span class="soa-dash" title="Sudah dilanjutkan / tanpa aksi di sini">-</span>';
            }
            $revisi = route('superuser.penjualan.sales_order.edit', [$model->id, $step = 1]);
            $lanjutkan = route('superuser.penjualan.sales_order.lanjutkan', $model->id);
            $delete = route('superuser.penjualan.sales_order.destroy', $model->id);
            $print_so = route('superuser.penjualan.sales_order.print_so', $model->id);
            $estimate_pdf = route('superuser.penjualan.sales_order.sales_estimate_pdf', $model->id);
            $archive = route('superuser.penjualan.sales_order.archive_one_awal', $model->id);

            // Arsip manual hanya untuk divisi yang boleh. Masuk ke menu "..." agar tidak
            // menambah tombol di baris (psikologi: 1 baris = 1 aksi utama).
            $canArchive = in_array(Auth::user()->division, ['Admin', 'Developer', 'Management']);

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
                // PROFORMA SUDAH DIBUAT = sudah dilanjutkan (CASH) -> riwayat, tanpa aksi di sini.
                if (in_array($model->status_proforma, [1, 2, 3, 4])) {
                    return '<span class="soa-dash" title="Sudah dilanjutkan / tanpa aksi di sini">-</span>';
                }
            }
        
            // PSIKOLOGI AO: 1 baris = 1 aksi utama. Lanjutkan dibuat besar & jelas,
            // sisanya disembunyikan di "..." agar tidak bersaing secara visual.
            // Print draft sengaja dilabeli DRAFT agar tidak terasa "selesai" lalu di-share.
            $dropdown_items = '';
            $dropdown_items .= "<a class=\"dropdown-item soa-dd-item\" href=\"{$revisi}\"><i class=\"fa fa-pencil fa-fw text-warning mr-2\"></i>Revisi / Edit</a>";
            $dropdown_items .= "<a class=\"dropdown-item soa-dd-item\" href=\"{$print_so}\" target=\"_blank\"><i class=\"fa fa-print fa-fw text-info mr-2\"></i>Print Draft <span class=\"soa-dd-hint\">belum valid</span></a>";
            if (!empty($btn_estimate)) {
                $dropdown_items .= "<div class=\"dropdown-divider\"></div>";
                // $btn_estimate adalah HTML tombol lingkaran; sederhanakan jadi item teks.
                $dropdown_items .= "<a class=\"dropdown-item soa-dd-item\" href=\"" . e($estimate_pdf) . "\" target=\"_blank\"><i class=\"fa fa-file-pdf-o fa-fw text-primary mr-2\"></i>Sales Estimate</a>";
            }
            $dropdown_items .= "<div class=\"dropdown-divider\"></div>";
            $dropdown_items .= "<a class=\"dropdown-item soa-dd-item text-danger\" href=\"javascript:saveConfirmation('{$delete}')\"><i class=\"fa fa-trash fa-fw mr-2\"></i>Hapus</a>";
            if ($canArchive) {
                $dropdown_items .= "<a class=\"dropdown-item soa-dd-item\" href=\"javascript:saveConfirmation('{$archive}')\"><i class=\"fa fa-archive fa-fw text-secondary mr-2\"></i>Arsipkan</a>";
            }

            $btn_lanjut_utama = "
                <a href=\"javascript:saveConfirmation('{$lanjutkan}')\" class=\"btn-lanjut-utama\" title=\"Wajib: kirim ke admin agar diproses. Print saja belum cukup.\">
                    <i class=\"fa fa-check\"></i> Lanjutkan
                </a>
            ";
            $btn_lainnya = "
                <div class=\"btn-group\">
                    <button type=\"button\" class=\"btn-lainnya\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" title=\"Aksi lain: revisi, print draft, hapus\">
                        <i class=\"fa fa-ellipsis-h\"></i>
                    </button>
                    <div class=\"dropdown-menu dropdown-menu-right soa-dd shadow\">
                        {$dropdown_items}
                    </div>
                </div>
            ";

            if ($model->status_so === 'AWAL') {
                if ($model->approval_mou == "YES" && $model->approval_mou_status != "APPROVED") {

                    // JIKA BUTUH APPROVAL: belum bisa Lanjut/Print. Semua aksi di "...".
                    $approval_items = '';
                    if ($model->is_estimate == 1) {
                        $approval_items .= "<a class=\"dropdown-item soa-dd-item\" href=\"" . e($estimate_pdf) . "\" target=\"_blank\"><i class=\"fa fa-file-pdf-o fa-fw text-primary mr-2\"></i>Sales Estimate</a>";
                    }
                    if ($canArchive) {
                        if ($approval_items !== '') $approval_items .= "<div class=\"dropdown-divider\"></div>";
                        $approval_items .= "<a class=\"dropdown-item soa-dd-item\" href=\"javascript:saveConfirmation('{$archive}')\"><i class=\"fa fa-archive fa-fw text-secondary mr-2\"></i>Arsipkan</a>";
                    }
                    $buttons .= "<div class=\"soa-actionwrap\">"
                        . "<span class=\"soa-pill soa-pill-warn\" title=\"Menunggu approval SPV\"><span class=\"dot\"></span>Butuh Approval</span>"
                        . "<div class=\"btn-group\"><button type=\"button\" class=\"btn-lainnya\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" title=\"Aksi lain\"><i class=\"fa fa-ellipsis-h\"></i></button>"
                        . "<div class=\"dropdown-menu dropdown-menu-right soa-dd shadow\">{$approval_items}</div></div>"
                        . "</div>";

                } else {

                    // NORMAL / APPROVED: 1 CTA utama + menu lainnya (arsip sudah di dalam).
                    $buttons .= "<div class=\"soa-actionwrap\">{$btn_lanjut_utama}{$btn_lainnya}</div>";
                }
            } elseif ($model->status_so === 'REVISI') {
                $buttons .= "<div class=\"soa-actionwrap\">{$btn_lanjut_utama}{$btn_lainnya}</div>";

            } elseif (in_array($model->status_so, ['TUTUP', 'LANJUTAN'])) {
                // Sudah disubmit = tidak ada aksi di sini. Proses lanjut ada di SO Lanjutan.
                return '<span class="soa-dash" title="Sudah dilanjutkan / tanpa aksi di sini">-</span>';
            }
        
            return $buttons;
        });     

        return $table->make(true);
    }
}