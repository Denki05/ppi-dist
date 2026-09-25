<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Master\ProductPack;
use Illuminate\Support\Facades\Log;
use DB;

class ReportRequestController extends Controller
{
    private $baseReportPath  = 'cr/MIS/';
    private $exportBasePath = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\MIS\\export';

    /**
     * ============================================================
     * 1. Entry Point
     * ============================================================
     */
    public function handle(Request $r)
    {
        try {
            $type = $r->type;
            $sub  = $r->sub;
    
            // ⬅️ TAMBAHAN DEBUG: lihat RAW payload yang diterima dari frontend
            \Log::info('[DEBUG REPORT] Raw request payload', [
                'type'      => $r->type,
                'sub'       => $r->sub,
                'officer'   => $r->officer,
                'officer_type' => gettype($r->officer),
                'brands'    => $r->brands,
                'varians'   => $r->varians,
                'customers' => $r->customers,
                'start'     => $r->start,
                'end'       => $r->end,
            ]);
    
            $config = $this->loadReportConfig($type, $sub);
            $rptPath = public_path($this->baseReportPath . $config['file']);

            // Nama file unik per request: time() saja bisa tabrakan bila 2 user
            // cetak sub yang sama di detik yang sama (terbaca PDF orang lain).
            $pdfFileName = "{$sub}_" . date('YmdHis') . '_' . uniqid() . ".pdf";
            $pdfOutputPath = $this->exportBasePath . '\\' . $pdfFileName;
            if (!file_exists($this->exportBasePath)) {
                mkdir($this->exportBasePath, 0777, true);
            }
    
            $this->runCrystalCom($rptPath, $pdfOutputPath, $r, $config);
    
            if (!file_exists($pdfOutputPath)) {
                throw new \Exception("PDF gagal dibuat. COM error.");
            }
    
            $pdfBinary = file_get_contents($pdfOutputPath);
            $pdfBase64 = base64_encode($pdfBinary);
            @unlink($pdfOutputPath);
    
            return response()->json([
                'status' => true,
                'pdf_base64' => $pdfBase64
            ]);
    
        } catch (\Exception $e) {
            \Log::error('[DEBUG REPORT] Exception: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * 2. KONFIGURASI TIAP REPORT (semua proses dipisah di sini)
     * ============================================================
     */
    private function loadReportConfig($type, $sub)
    {
        $map = [
            // ================================
            // GROUP: PENJUALAN
            // ================================
            'target' => [
                // ============================================
                // V1: Report PIC - Varian - Customer
                // ============================================
                'v1' => [
                    'file'           => 'sales_performence_v2.rpt',
                    'date_field'     => '{penjualan_so1.so_date}',
                    'officer_field'  => '{master_customer_other_addresses1.officer}',
                    // Pengaman ganda: selain filter nama officer, saring juga
                    // via id alamat (exact match, kebal beda huruf besar/kecil
                    // atau spasi) agar AO lain tidak bocor masuk laporan.
                    'officer_address_field' => '{master_customer_other_addresses1.id}',
                    'officer_id_dual' => true,
                    'brand_field'    => '{master_products.brand_name}',
                    'varian_field'   => '{master_products.id}',
                    'customer_field' => '{master_customer_other_addresses1.id}',
                    'uses_command'   => false,
                    'needs_officer'  => true,
                ],

                // ============================================
                // V2: Report PIC - Customer - Varian
                // ============================================
                'v2' => [
                    'file'           => 'sales_performence_v3.rpt',
                    // ⚠️ UBAH NAMA-NAMA DI BAWAH INI SESUAI ISI FILE v3.rpt
                    // Contoh jika aliasnya berbeda:
                    'date_field'     => '{penjualan_so1.so_date}', 
                    'officer_field'  => '{master_customer_other_addresses1.officer}',
                    'officer_address_field' => '{master_customer_other_addresses1.id}',
                    'officer_id_dual' => true,
                    'brand_field'    => '{master_products.brand_name}',
                    'varian_field'   => '{master_products.id}',
                    'customer_field' => '{master_customer_other_addresses1.id}',
                    'uses_command'   => false, // Ubah jadi true jika RPT pakai SQL Command
                    'needs_officer'  => true,
                ],

                // ============================================
                // V3: Report PIC - Varian - PIC
                // ============================================
                'v3' => [
                    'file'           => 'sales_performence_v4.rpt',
                    // ⚠️ UBAH NAMA-NAMA DI BAWAH INI SESUAI ISI FILE v4.rpt
                    'date_field'     => '{penjualan_so1.so_date}',
                    'officer_field'  => '{master_customer_other_addresses1.officer}',
                    'officer_address_field' => '{master_customer_other_addresses1.id}',
                    'officer_id_dual' => true,
                    'brand_field'    => '{master_products.brand_name}',
                    'varian_field'   => '{master_products.id}',
                    // Jika di V3 tidak ada filter customer, kosongkan stringnya agar tidak dilempar ke Crystal Report
                    'customer_field' => '', 
                    'uses_command'   => false,
                    'needs_officer'  => true,
                ],
            ],

            // ================================
            // GROUP: OMSET (baru)
            // ================================
            'omset' => [

                'pembayaran' => [
                    'file'       => 'laporan_pembayaran.rpt',   // sama seperti target
                    'date_field' => '{finance_payable1.pay_date}',
                    'officer_field' => '{master_customer_other_addresses1.officer}',
                    'officer_address_field' => '{master_customer_other_addresses1.id}',
                    'officer_id_dual' => true,
                    'uses_command' => false,
                    'needs_officer' => true,
                ],

                'penjualan' => [
                    'file'       => 'laporan_penjualan2.rpt',   // sama seperti target
                    'date_field' => '{penjualan_so1.so_date}',
                    'officer_field' => '{master_customer_other_addresses1.officer}',
                    'officer_address_field' => '{master_customer_other_addresses1.id}',
                    'officer_id_dual' => true,
                    'uses_command' => false,
                    'needs_officer' => true,
                ],
            ],

            // ================================
            // GROUP: MANAGEMENT (baru)
            // ================================
            'management' => [

                'management_by_brand' => [
                    'file'       => 'customer_type_brand.rpt',
                    'date_field' => '{report_customer_type_brand.invoice_date}',
                    // Officer tetap difilter walau RPT market tidak punya UI AO khusus:
                    // diambil dari AO global yang dikirim frontend (mis. "Lindy").
                    // Field ini sama seperti yang dipakai export_officer().
                    'officer_field' => '{report_customer_type_brand.officer}',
                    // Fallback TANPA tambah kolom: saring via other_address_id
                    // (id dari master_customer_other_addresses) yang sudah ada
                    // di tabel report. Jadi tidak wajib ALTER TABLE / Verify RPT.
                    'officer_address_field' => '{report_customer_type_brand.other_address_id}',
                    'uses_command' => false,
                    'needs_officer' => true,
                    'force_date_formula' => false,

                    // ⬇️ TAMBAHAN PENTING
                    'display_date_param' => true,
                ],


                'management_by_zone' => [
                    'file'       => 'report_zone_customer.rpt',   // sama seperti target
                    'date_field' => '{report_customer_type_brand.invoice_date}',
                    'officer_field' => '{report_customer_type_brand.officer}',
                    'officer_address_field' => '{report_customer_type_brand.other_address_id}',
                    'uses_command' => false,
                    'needs_officer' => true,
                    'force_date_formula' => false,

                    // ⬇️ TAMBAHAN PENTING
                    'display_date_param' => true,
                ],
            ],


            // ================================
            // GROUP: AKTIVITAS (opsional bila dibutuhkan)
            // ================================
            'aktivitas' => [
                'visit' => [
                    'file'       => 'laporan_visit.rpt',
                    'date_field' => '{activity.date}',
                    'officer_field' => '{activity.officer}',
                    'uses_command' => false,
                    'needs_officer' => true,
                ],

                'followup' => [
                    'file'       => 'laporan_followup.rpt',
                    'date_field' => '{activity.date}',
                    'officer_field' => '{activity.officer}',
                    'uses_command' => false,
                    'needs_officer' => true,
                ],

                'sampling' => [
                    'file'       => 'laporan_sampling.rpt',
                    'date_field' => '{activity.date}',
                    'officer_field' => '{activity.officer}',
                    'uses_command' => false,
                    'needs_officer' => true,
                ],
            ],
        ];

        if (!isset($map[$type][$sub])) {
            throw new \Exception("Report type/sub tidak ditemukan: {$type} / {$sub}");
        }

        return $map[$type][$sub];
    }

    /**
     * ============================================================
     * 3. FUNGSI EKSEKUSI CRYSTAL REPORT
     * ============================================================
     */
    private function runCrystalCom($rptPath, $outputPath, Request $r, $config)
    {
        // 1. Cek COM sedini mungkin
        if (!class_exists('COM')) {
            \Log::error('[DEBUG REPORT] PHP COM tidak aktif atau tidak terinstall.');
            throw new \Exception("PHP COM tidak aktif.");
        }
    
        $start = $r->start;
        $end   = $r->end;
        $ao    = $r->officer ?? null;

        // Validasi tanggal sedini mungkin: tanpa ini strtotime(null) = false
        // lalu date() diam-diam jadi 1970-01-01 dan laporan tercetak ngawur.
        if (empty($start) || empty($end)) {
            throw new \Exception("Gagal Memproses: Rentang tanggal (start/end) belum diisi.");
        }
        if (strtotime($start) === false || strtotime($end) === false) {
            throw new \Exception("Gagal Memproses: Format tanggal tidak valid.");
        }

        // Normalisasi officer: frontend MIS kadang kirim string ("Lindy"),
        // kadang array (["Lindy"]), kadang "all" / "pilih_officer" / comma-separated.
        if (is_string($ao) && strpos($ao, ',') !== false) {
            $ao = array_map('trim', explode(',', $ao));
        }

        // Normalisasi brands/varians/customers: bisa string tunggal ("Senses")
        // atau array. Tanpa ini, payload string membuat filter diam-diam
        // dilewati (is_array gagal) dan laporan tercetak tanpa filter.
        $normList = function ($v) {
            if ($v === null) return [];
            $arr = is_array($v) ? $v : [$v];
            return array_values(array_filter(array_map(function ($x) {
                return trim((string) $x);
            }, $arr), function ($x) {
                return $x !== '' && strtolower($x) !== 'null';
            }));
        };
        // Aturan yang sama dengan officer: 'all' gugur bila tercampur pilihan
        // asli (['GCF','all'] -> saring GCF, bukan tampil semua brand).
        // Kosong = semua (tanpa filter).
        $effectiveList = function (array $arr) {
            $w = array_values(array_filter($arr, function ($x) {
                return strtolower($x) !== 'all';
            }));
            return $w;
        };
        $brandsList    = $normList($r->brands);
        $variansList   = $normList($r->varians);
        $customersList = $normList($r->customers);
        $brandsEff     = $effectiveList($brandsList);
        $variansEff    = $effectiveList($variansList);
        $customersEff  = $effectiveList($customersList);

        \Log::info('[DEBUG REPORT] Masuk ke runCrystalCom. Path: ' . $rptPath);

        // TAMBAHKAN VALIDASI INI:
        // 'all' (huruf kecil/besar) dianggap valid = tampilkan semua AO.
        // Yang ditolak hanya kosong / 'null' / 'pilih_officer'.
        $aoForCheck = is_array($ao) ? array_filter($ao) : [$ao];
        $aoForCheckLower = array_map(function ($v) { return strtolower(trim((string) $v)); }, $aoForCheck);
        $aoIsEmpty = empty(array_filter($aoForCheckLower, function ($v) {
            return $v !== '' && $v !== 'null' && $v !== 'pilih_officer';
        }));
        if (!empty($config['needs_officer']) && $aoIsEmpty) {
            throw new \Exception("Gagal Memproses: Account Officer (AO) belum terpilih atau tidak terbaca oleh sistem.");
        }
    
        // ⬅️ TAMBAHAN DEBUG: cek nilai $ao tepat setelah diambil dari request
        \Log::info('[DEBUG REPORT] Officer value in runCrystalCom', [
            'ao_raw'     => $ao,
            'ao_type'    => gettype($ao),
            'ao_is_array'=> is_array($ao),
            'needs_officer_config' => $config['needs_officer'] ?? null,
            'officer_field_config' => $config['officer_field'] ?? null,
        ]);
    
        $startDt = date('Y-m-d', strtotime($start));
        $endDt   = date('Y-m-d', strtotime($end));
        // Format display untuk header laporan (RPT market pakai d-m-Y).
        $dispStart = date('d-m-Y', strtotime($start));
        $dispEnd   = date('d-m-Y', strtotime($end));

        // Mapping alias officer -> nilai asli di DB (sinkron dengan formula rename di .rpt).
        // Contoh: RPT menampilkan "Nia" sebagai "Kantor", jadi pilih "Kantor"
        // harus menyaring officer "Kantor" + "Nia".
        $officerAliasMap = [
            'Kantor' => ['Kantor', 'Nia'],
        ];
    
        try {
            $crapp = new \COM("CrystalDesignRunTime.Application");
            $creport = $crapp->OpenReport($rptPath, 1);
    
            \Log::info('[DEBUG REPORT] Report berhasil dibuka.');

            // Database setup
            if (empty($config['uses_command'])) {
                foreach ($creport->Database->Tables as $table) {
                    $table->SetLogOnInfo("LOCAL_3", "ppi_araya", "root", "");
                }
            }
    
            // ============================================================
            // INJECT PARAMETER (PEMBERSIHAN NAMA & LOOP TUNGGAL)
            // customer_type_brand.rpt menampilkan periode via ParameterFields(2)
            // dan (3) dengan format d-m-Y (lihat print_report lama), jadi selain
            // match by-name, selalu fallback set by-index agar header tanggal
            // tidak kosong ("parameter range date tidak muncul").
            // ============================================================
            $paramCount = $creport->ParameterFields->Count;
            \Log::info("[DEBUG REPORT] Jumlah Parameter terdeteksi: " . $paramCount);

            // Normalisasi daftar officer sekali untuk dipakai param + formula.
            $officersNorm = is_array($ao) ? $ao : [$ao];
            $officersNorm = array_values(array_filter(array_map(function ($v) {
                return trim((string) $v);
            }, $officersNorm), function ($v) {
                return $v !== '' && strtolower($v) !== 'null' && strtolower($v) !== 'pilih_officer';
            }));
            // 'all' yang tercampur nama asli (mis. ['all','Lindy']) harus gugur:
            // sebelumnya campuran ini dianggap "semua" sehingga filter hilang
            // dan AO lain bocor masuk laporan.
            $withoutAll = array_values(array_filter($officersNorm, function ($v) {
                return strtolower($v) !== 'all';
            }));
            if (!empty($withoutAll)) {
                $officersNorm = $withoutAll;
            }
            $isAllOfficer = empty($withoutAll);
            // Perluas alias (Kantor -> Kantor+Nia) agar konsisten dengan RPT.
            $expandedOfficers = [];
            foreach ($officersNorm as $off) {
                if (isset($officerAliasMap[$off])) {
                    $expandedOfficers = array_merge($expandedOfficers, $officerAliasMap[$off]);
                } else {
                    // cocokkan case-insensitive untuk alias key juga
                    $matched = false;
                    foreach ($officerAliasMap as $aliasKey => $aliasVals) {
                        if (strcasecmp($aliasKey, $off) === 0) {
                            $expandedOfficers = array_merge($expandedOfficers, $aliasVals);
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) $expandedOfficers[] = $off;
                }
            }
            $expandedOfficers = array_values(array_unique($expandedOfficers));

            for ($i = 1; $i <= $paramCount; $i++) {
                $param = $creport->ParameterFields->Item($i);

                // 1. BERSIHKAN NAMA: Hilangkan {? } supaya tinggal 'start_date' atau 'start'
                $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $param->Name));

                \Log::info("[DEBUG REPORT] Memproses Parameter #{$i}: '{$param->Name}' (dibersihkan jadi: '{$cleanName}')");

                switch ($cleanName) {

                    // ── PARAMETER RANGE (Crystal range value: awal–akhir) ──
                    // RPT market punya {?range_date} selain {?start_date}/{?end_date}.
                    case 'range_date':
                    case 'rangedate':
                    case 'date_range':
                    case 'daterange':
                    case 'periode':
                        try {
                            $param->ClearCurrentValueAndRange();
                            $param->AddCurrentRange($startDt, $endDt, 3);
                            \Log::info("[DEBUG REPORT] ---> Berhasil Set Param Range: {$startDt} s/d {$endDt}");
                        } catch (\Exception $ex) {
                            \Log::info("[DEBUG REPORT] Set Param Range gagal: " . $ex->getMessage());
                        }
                        break;

                    // ── PARAMETER TANGGAL (SEMUA VARIANT) ──
                    // RPT lama (management) pakai display d-m-Y, bukan datetime.
                    case 'start':
                    case 'start_date':
                    case 'tgl_awal':
                    case 'tglawal':
                    case 'datefrom':
                    case 'fromdate':
                    case 'periodestart':
                    case 'periodestartdate':
                    case 'periode_start':
                    case 'periodeawal':
                    case 'periode_awal':
                        $param->SetCurrentValue($dispStart);
                        \Log::info("[DEBUG REPORT] ---> Berhasil Set Param Start: {$dispStart}");
                        break;

                    case 'end':
                    case 'end_date':
                    case 'tgl_akhir':
                    case 'tglakhir':
                    case 'dateto':
                    case 'todate':
                    case 'periodeend':
                    case 'periodeenddate':
                    case 'periode_end':
                    case 'periodeakhir':
                    case 'periode_akhir':
                        $param->SetCurrentValue($dispEnd);
                        \Log::info("[DEBUG REPORT] ---> Berhasil Set Param End: {$dispEnd}");
                        break;

                    // ── PARAMETER OFFICER ──
                    case 'officer':
                    case 'officersearch':
                    case 'officer_search':
                    case 'ao':
                        $officerParamVal = count($expandedOfficers) > 0 ? $expandedOfficers[0] : '';
                        if (!empty($officerParamVal) && strtolower($officerParamVal) !== 'all') {
                            try {
                                $param->SetCurrentValue($officerParamVal);
                                \Log::info("[DEBUG REPORT] ---> Berhasil Set Param Officer: {$officerParamVal}");
                            } catch (\Exception $ex) {
                                \Log::info("[DEBUG REPORT] Skip Param Officer (mungkin multi-value): " . $ex->getMessage());
                            }
                        }
                        break;
                }
            }

            $creport->DiscardSavedData();
            $creport->VerifyOnEveryPrint = false;
            $creport->EnableParameterPrompting = false;

            // Fallback index-based untuk RPT market yang nama parameternya tidak
            // standar: ParameterFields(2)=periode awal, (3)=periode akhir (d-m-Y).
            if (!empty($config['display_date_param']) && $paramCount >= 3) {
                try {
                    $creport->ParameterFields(2)->SetCurrentValue($dispStart);
                    $creport->ParameterFields(3)->SetCurrentValue($dispEnd);
                    \Log::info("[DEBUG REPORT] ---> Fallback Set Param idx2/idx3: {$dispStart} / {$dispEnd}");
                } catch (\Exception $ex) {
                    \Log::info("[DEBUG REPORT] Fallback idx2/idx3 gagal: " . $ex->getMessage());
                }
            }
    
            // Cek defensif: filter officer via kolom officer hanya dipakai bila
            // kolom report_customer_type_brand.officer benar-benar ada di DB.
            // Kalau belum ada, dipakai fallback via other_address_id (tanpa
            // ALTER TABLE / Verify RPT) — lihat helper di bawah.
            $officerColumnExists = true;
            try {
                $officerColumnExists = \Schema::hasColumn('report_customer_type_brand', 'officer');
            } catch (\Exception $ex) {
                $officerColumnExists = true;
            }

            // Helper: ubah daftar nama officer -> daftar other_address_id
            // via master_customer_other_addresses. Mengembalikan array id string.
            $resolveOfficerAddressIds = function (array $officerNames) {
                try {
                    return DB::table('master_customer_other_addresses')
                        ->whereIn('officer', $officerNames)
                        ->pluck('id')
                        ->map(function ($v) { return trim((string) $v); })
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                } catch (\Exception $ex) {
                    \Log::warning('[DEBUG REPORT] Gagal resolve officer->address: ' . $ex->getMessage());
                    return [];
                }
            };

            // Helper: tambahkan filter officer ke $formula (by reference).
            // - Kolom officer (nama): dipakai bila ada (untuk market: hanya bila
            //   kolomnya benar-benar ada di DB, agar tidak error di Crystal).
            // - Dual ID (target penjualan): bila config punya officer_id_dual,
            //   tambahkan juga saringan other_address_id IN [...] (exact match
            //   dari DB, kebal beda case/spasi) agar AO lain tidak bocor.
            // - Fallback market tanpa kolom officer: hanya saringan ID.
            $applyOfficerFilter = function (&$formula) use ($config, $expandedOfficers, $officersNorm, $officerColumnExists, $resolveOfficerAddressIds) {
                \Log::info('[DEBUG REPORT] Officer setelah expand alias', [
                    'original' => $officersNorm,
                    'expanded' => $expandedOfficers,
                ]);
                $isMarketOfficerField = isset($config['officer_field'])
                    && strpos($config['officer_field'], 'report_customer_type_brand') !== false;
                $nameApplied = false;
                if (!empty($config['officer_field']) && (!$isMarketOfficerField || $officerColumnExists)) {
                    $officerList = implode("', '", $expandedOfficers);
                    $formula .= " AND {$config['officer_field']} IN ['$officerList']";
                    $nameApplied = true;
                }
                if (!empty($config['officer_address_field'])) {
                    $needIds = !$nameApplied || !empty($config['officer_id_dual']);
                    if ($needIds) {
                        $addressIds = $resolveOfficerAddressIds($expandedOfficers);
                        \Log::info('[DEBUG REPORT] Officer filter via other_address_id', [
                            'count' => count($addressIds),
                            'sample' => array_slice($addressIds, 0, 10),
                        ]);
                        if (!empty($addressIds)) {
                            $idList = implode("', '", $addressIds);
                            $formula .= " AND {$config['officer_address_field']} IN ['$idList']";
                        } else {
                            // Officer dipilih tapi tidak punya alamat: paksa kosong
                            // agar tidak jatuh kembali ke "semua AO".
                            $formula .= " AND 1 = 0";
                        }
                    }
                }
            };

            // Selection formula: tanggal selalu difilter; officer/brand/varian/
            // customer ditambahkan bila config menyediakannya dan user tidak pilih 'all'.
            // (Cabang force_date_formula dipertahankan untuk kompatibilitas, tapi
            //  sekarang ikut menerapkan filter officer — versi lama mengabaikannya
            //  sehingga "pilih Lindy tapi tercetak semua AO".)
            if (!empty($config['force_date_formula'])) {

                $formula = "({$config['date_field']}>=#$startDt# AND {$config['date_field']}<=#$endDt#)";

                if (!$isAllOfficer && !empty($expandedOfficers)) {
                    $applyOfficerFilter($formula);
                }

                \Log::info('[DEBUG REPORT] FINAL FORMULA (force_date) sebelum dikirim ke Crystal Report', [
                    'formula' => $formula,
                ]);

                $creport->RecordSelectionFormula = $formula;

            } elseif (!empty($config['date_field'])) {

                $formula = "({$config['date_field']} >= #$startDt# AND {$config['date_field']} <= #$endDt#)";

                if (!empty($config['needs_officer']) && !$isAllOfficer && !empty($expandedOfficers)) {
                    $applyOfficerFilter($formula);
                }
    
                if (!empty($brandsEff) && !empty($config['brand_field'])) {
                    $brandList = implode("', '", $brandsEff);
                    $formula .= " AND {$config['brand_field']} IN ['$brandList']";
                }
    
                if (!empty($variansEff) && !empty($config['varian_field'])) {
                    $varianList = implode("', '", $variansEff);
                    $formula .= " AND {$config['varian_field']} IN ['$varianList']";
                }
    
                if (!empty($customersEff) && !empty($config['customer_field'])) {
                    $customerList = implode("', '", $customersEff);
                    $formula .= " AND {$config['customer_field']} IN ['$customerList']";
                }
    
                // ⬅️ TAMBAHAN DEBUG: INI YANG PALING PENTING — formula final yang dieksekusi
                \Log::info('[DEBUG REPORT] FINAL FORMULA sebelum dikirim ke Crystal Report', [
                    'formula' => $formula,
                ]);
    
                $creport->RecordSelectionFormula = $formula;
            }
    
            $creport->ExportOptions->DiskFileName = $outputPath;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = 31;
            $creport->Export(false);
    
            $creport = null;
            $crapp = null;
    
        } catch (\Exception $e) {
            if (isset($creport)) $creport = null;
            if (isset($crapp)) $crapp = null;
            throw $e;
        }
    }

    public function getProductPack()
    {
        $results = DB::table('master_products')
            ->select(
                'id',
                'code AS product_code',
                'name AS product_name',
                'brand_name'   // ⬅️ TAMBAHAN: dibutuhkan untuk filter dependency Brand -> Varian di frontend
            )
            ->get();
        return response()->json($results);
    }
}