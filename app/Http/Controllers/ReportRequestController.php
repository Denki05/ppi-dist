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
    
            $pdfFileName = "{$sub}_" . time() . ".pdf";
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
            dd($e->getMessage());
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
                    'uses_command' => false,
                    'needs_officer' => true,
                ],

                'penjualan' => [
                    'file'       => 'laporan_penjualan2.rpt',   // sama seperti target
                    'date_field' => '{penjualan_so1.so_date}',
                    'officer_field' => '{master_customer_other_addresses1.officer}',
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
                    'uses_command' => false,
                    'needs_officer' => false,
                    'force_date_formula' => true,
                
                    // ⬇️ TAMBAHAN PENTING
                    'display_date_param' => true,
                ],
                

                'management_by_zone' => [
                    'file'       => 'report_zone_customer.rpt',   // sama seperti target
                    'date_field' => '{report_customer_type_brand.invoice_date}',
                    'uses_command' => false,
                    'needs_officer' => false,
                    'force_date_formula' => true,
                
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

        \Log::info('[DEBUG REPORT] Masuk ke runCrystalCom. Path: ' . $rptPath);

        // TAMBAHKAN VALIDASI INI:
        if (!empty($config['needs_officer']) && (empty($ao) || $ao === 'null')) {
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
            // ============================================================
            $paramCount = $creport->ParameterFields->Count;
            \Log::info("[DEBUG REPORT] Jumlah Parameter terdeteksi: " . $paramCount);

            for ($i = 1; $i <= $paramCount; $i++) {
                $param = $creport->ParameterFields->Item($i); 
                
                // 1. BERSIHKAN NAMA: Hilangkan {? } supaya tinggal 'start_date' atau 'start'
                $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $param->Name));
                
                \Log::info("[DEBUG REPORT] Memproses Parameter #{$i}: '{$param->Name}' (dibersihkan jadi: '{$cleanName}')");

                switch ($cleanName) {
                    
                    // ── PARAMETER TANGGAL (SEMUA VARIANT) ──
                    case 'start':
                    case 'start_date':
                    case 'tgl_awal':
                        $valStart = date('Y-m-d 00:00:00', strtotime($start));
                        $param->SetCurrentValue($valStart);
                        \Log::info("[DEBUG REPORT] ---> Berhasil Set Param Start: {$valStart}");
                        break;
                    
                    case 'end':
                    case 'end_date':
                    case 'tgl_akhir':
                        $valEnd = date('Y-m-d 23:59:59', strtotime($end));
                        $param->SetCurrentValue($valEnd);
                        \Log::info("[DEBUG REPORT] ---> Berhasil Set Param End: {$valEnd}");
                        break;

                    // ── PARAMETER PERIODE (UNTUK TAMPILAN HEADER) ──
                    // Menangani parameter 2 dan 3 yang tadi Anda sebutkan
                    case 'periodestart': // Sesuaikan jika di RPT namanya 'periode_start'
                    case 'periode_start':
                        $param->SetCurrentValue(date('d-m-Y', strtotime($start)));
                        break;
                    case 'periodeend':
                    case 'periode_end':
                        $param->SetCurrentValue(date('d-m-Y', strtotime($end)));
                        break;

                    // ── PARAMETER OFFICER ──
                    case 'officer':
                    case 'officersearch':
                        $officerParamVal = is_array($ao) ? (count($ao) > 0 ? $ao[0] : '') : $ao;
                        if (!empty($officerParamVal) && $officerParamVal !== 'all') {
                            $param->SetCurrentValue($officerParamVal);
                            \Log::info("[DEBUG REPORT] ---> Berhasil Set Param Officer: {$officerParamVal}");
                        }
                        break;
                }
            }
    
            $creport->EnableParameterPrompting = false;
    
            // if (!empty($config['display_date_param'])) {
            //     $creport->ParameterFields(2)->SetCurrentValue(date('d-m-Y', strtotime($start)));
            //     $creport->ParameterFields(3)->SetCurrentValue(date('d-m-Y', strtotime($end)));
            // }
    
            if (!empty($config['force_date_formula'])) {
    
                $creport->RecordSelectionFormula =
                    "{$config['date_field']}>=#$start# AND {$config['date_field']}<=#$end#";
    
            } elseif (!empty($config['date_field'])) {
    
                $formula = "({$config['date_field']} >= #$startDt# AND {$config['date_field']} <= #$endDt#)";
    
                if (!empty($config['needs_officer']) && !empty($config['officer_field']) && !empty($ao)) {
                    $officers = is_array($ao) ? $ao : [$ao];
                    if (!in_array('all', $officers) && !in_array('pilih_officer', $officers)) {
                        $officerList = implode("', '", $officers);
                        $formula .= " AND {$config['officer_field']} IN ['$officerList']";
                    }
                }
    
                if (!empty($r->brands) && is_array($r->brands) && !in_array('all', $r->brands) && !empty($config['brand_field'])) {
                    $brandList = implode("', '", $r->brands);
                    $formula .= " AND {$config['brand_field']} IN ['$brandList']";
                }
    
                if (!empty($r->varians) && is_array($r->varians) && !in_array('all', $r->varians) && !empty($config['varian_field'])) {
                    $varianList = implode("', '", $r->varians);
                    $formula .= " AND {$config['varian_field']} IN ['$varianList']";
                }
    
                if (!empty($r->customers) && is_array($r->customers) && !in_array('all', $r->customers) && !empty($config['customer_field'])) {
                    $customerList = implode("', '", $r->customers);
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