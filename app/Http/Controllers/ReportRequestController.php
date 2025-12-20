<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

            // Load konfigurasi report sesuai type + sub
            $config = $this->loadReportConfig($type, $sub);

            $rptPath = public_path($this->baseReportPath . $config['file']);

            // Output PDF
            $pdfFileName = "{$sub}_" . time() . ".pdf";
            $pdfOutputPath = $this->exportBasePath . '\\' . $pdfFileName;
            if (!file_exists($this->exportBasePath)) {
                mkdir($this->exportBasePath, 0777, true);
            }

            // Jalankan Crystal Report
            $this->runCrystalCom(
                $rptPath,
                $pdfOutputPath,
                $r,
                $config
            );

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
            // GROUP: PENJUALAN (baru)
            // ================================
            'target' => [ // Menggunakan 'target' untuk konsistensi JS

                'customer_market_brand' => [ // BARU: Penjualan -> Customer -> Market Brand
                    'file' => 'officer_report_nominal_3.rpt',
                    'date_field' => '{Command.invoice_date}',
                    // ⬇️ UBAH BARIS INI (Asumsi officer_report_nominal_3.rpt menggunakan field 'officer' yang berasal dari view Anda)
                    'officer_field' => '{Command.officer}', 
                    // ⬆️ UBAH BARIS INI
                    'uses_command' => false,
                    'needs_officer' => true,
                ],
                
                'customer_pic_customer_varian' => [ // BARU: Penjualan -> Customer -> PIC
                 'file' => 'sales_performence_v3.rpt',
                 'date_field' => '{penjualan_so1.so_date}',
                 'officer_field' => '{master_customer_other_addresses1.officer}',
                 'uses_command' => false,
                 'needs_officer' => true,
                 ],
                
                'varian_pic_varian_customer' => [ // BARU: Penjualan -> Varian -> PIC: Varian - Customer
                 'file' => 'sales_performence_v2.rpt',
                 'date_field' => '{penjualan_so1.so_date}',
                 'officer_field' => '{master_customer_other_addresses1.officer}',
                 'uses_command' => false,
                 'needs_officer' => true,
                ],
                
                'varian_pic_varian_pic' => [ // BARU: Penjualan -> Varian -> PIC: Varian - PIC
                 'file'  => 'sales_performence_v4.rpt',
                 'date_field' => '{penjualan_so1.so_date}',
                 'officer_field' => '{master_customer_other_addresses1.officer}',
                 'uses_command' => false,
                 'needs_officer' => true,
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
        if (!class_exists('COM')) {
            throw new \Exception("PHP COM tidak aktif.");
        }

        $start = $r->start;
        $end   = $r->end;
        $ao    = $r->ao;

        $startDt = date('Y-m-d', strtotime($start));
        $endDt   = date('Y-m-d', strtotime($end));

        try {
            $crapp = new \COM("CrystalDesignRunTime.Application");
            $creport = $crapp->OpenReport($rptPath, 1);

            // -----------------------------
            // 1. LOGIN DATABASE
            // -----------------------------
            if (!$config['uses_command']) {
                $creport->Database->Tables(1)->SetLogOnInfo("LOCAL_3", "ppi_araya", "root", "");
            }

            // -----------------------------
            // 2. SET PARAMETER DINAMIS
            // -----------------------------
            foreach ($creport->ParameterFields as $param) {
                $paramName = strtolower($param->Name);
                switch ($paramName) {
                    case 'start':
                        $param->SetCurrentValue(new \DateTime($start));
                        break;
                    case 'end':
                        $param->SetCurrentValue(new \DateTime($end));
                        break;
                    case 'periode_start':  // jika formula menggunakan nama ini
                        $param->SetCurrentValue(date('d-m-Y', strtotime($start)));
                        break;
                    case 'periode_end':
                        $param->SetCurrentValue(date('d-m-Y', strtotime($end)));
                        break;
                    case 'officer':
                        if (!empty($ao) && $ao !== 'all' && $ao !== 'pilih_officer') {
                            $param->SetCurrentValue($ao);
                        }
                        break;
                }
            }

            $creport->EnableParameterPrompting = false;

            // -----------------------------
            // 3. BUILD RECORD SELECTION FORMULA
            // -----------------------------
            $formula = "({$config['date_field']} >= #$startDt# AND {$config['date_field']} <= #$endDt#)";
            if ($config['needs_officer'] && !empty($ao) && $ao !== 'all' && $ao !== 'pilih_officer') {
                $formula .= " AND {$config['officer_field']} = '$ao'";
            }
            $creport->RecordSelectionFormula = $formula;

            // -----------------------------
            // 4. EXPORT PDF
            // -----------------------------
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

}