<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Reports\CustomerTypeBrandReports;
Use App\Entities\Penjualan\SalesOrder;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Auth;
use COM;
use DB;


class ReportCustomerTypeBrandController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.customer_type_brand.";
        $this->route = "superuser.report.customer_type_brand";
        $this->user_menu = new UserMenu;
        $this->access = null;
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $access = $this->user_menu;
            $access = $access->where('user_id',$user->id)
                             ->whereHas('menu',function($query2){
                                $query2->where('route_name',$this->route);
                             })
                             ->first();
            $this->access = $access;
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        // Check if the user is a superuser and has access
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access)) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Retrieve the data
        $data['data'] = CustomerTypeBrandReports::first();

        // Get the current year
        $currentYear = Carbon::now()->year;

        // Array with month names
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Prepare month options array
        $monthOptions = [];
        foreach ($months as $index => $month) {
            $monthOptions[] = [
                'value' => $index + 1, // month index (1 to 12)
                'label' => $month // Month name with year
            ];
        }

        // Pass month options to the view along with other data
        $data['monthOptions'] = $monthOptions;

        // Return the view with the data
        return view($this->view . "index", $data);
    }


    public function postData(Request $request)
    {
        try {
            $start = $request->query('period_from');
            $end   = $request->query('period_to');

            if (!$start || !$end) {
                return redirect()->back()->with('error', 'Error: Rentang tanggal harus diisi.');
            }

            $startDate = Carbon::parse($start)->startOfDay();
            $endDate   = Carbon::parse($end)->endOfDay();

            // ==========================================================
            // Sub-query: hitung ulang subtotal_item per DO dari item asli
            // subtotal_item = SUM((price - usd_disc) * qty) * idr_rate
            // ==========================================================
            $subtotalSub = DB::table('penjualan_do_item')
                ->join('penjualan_do', 'penjualan_do.id', '=', 'penjualan_do_item.do_id')
                ->select(
                    'penjualan_do_item.do_id',
                    DB::raw('SUM((penjualan_do_item.price - penjualan_do_item.usd_disc) * penjualan_do_item.qty) * MAX(penjualan_do.idr_rate) AS subtotal_item')
                )
                ->groupBy('penjualan_do_item.do_id');

            $logSummary = [
                'total'     => 0,
                'mismatch'  => 0,
                'matched'   => 0,
            ];

            DB::table('penjualan_do')
                ->leftJoin('penjualan_do_details', 'penjualan_do_details.do_id', '=', 'penjualan_do.id')
                ->leftJoin('penjualan_do_item', 'penjualan_do_item.do_id', '=', 'penjualan_do.id')
                ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
                ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
                ->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
                ->joinSub($subtotalSub, 'calc', function ($join) {
                    $join->on('calc.do_id', '=', 'penjualan_do.id');
                })
                ->select(
                    DB::raw('SUM(penjualan_do_item.qty) AS invoice_qty'),
                    'master_customers.id AS customerID',
                    'master_customer_other_addresses.id AS otherAddressID',
                    'master_customer_other_addresses.name AS customer_name',
                    'master_customer_categories.name AS customer_type',
                    'master_customer_other_addresses.text_kota AS customer_kota',
                    'master_customer_other_addresses.text_provinsi AS customer_provinsi',
                    'master_customer_other_addresses.zone AS customer_zone',
                    'penjualan_do.do_code AS invoice_code',
                    'penjualan_so.so_date AS invoice_date',
                    'penjualan_so.brand_name AS invoice_brand',
                    'penjualan_so.type_so AS invoice_type',
                    'penjualan_do_details.delivery_cost_idr AS invoice_delivery_order_cost',
                    'penjualan_do_details.discount_1_idr AS discount_1_idr',
                    'penjualan_do_details.discount_2_idr AS discount_2_idr',
                    'penjualan_do_details.discount_idr AS discount_idr',
                    'penjualan_do_details.voucher_idr AS voucher_idr',
                    'penjualan_do_details.ppn_idr AS ppn_idr',
                    'penjualan_do_details.purchase_total_idr AS old_purchase_total_idr',
                    'penjualan_do_details.grand_total_idr AS old_grand_total_idr',
                    DB::raw('MAX(calc.subtotal_item) AS subtotal_item')
                )
                ->where('penjualan_do.status', 6)
                ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
                ->where(function ($query) {
                    $query->where('master_customers.status', 1)
                        ->orWhere('master_customers.existence', 1);
                })
                ->groupBy('penjualan_do.do_code')
                ->orderBy('penjualan_do.do_code')
                ->chunk(100, function ($results) use (&$logSummary) {
                    foreach ($results as $row) {

                        // ===== Hitung ulang persis seperti pembuatan invoice =====
                        $subtotal_item = $row->subtotal_item ?? 0;
                        $discount_1    = $row->discount_1_idr ?? 0;
                        $discount_2    = $row->discount_2_idr ?? 0;
                        $discount_tbh  = $row->discount_idr ?? 0;
                        $voucher       = $row->voucher_idr ?? 0;
                        $ppn           = $row->ppn_idr ?? 0;       // hanya untuk info/log, TIDAK ikut dihitung
                        $delivery      = $row->invoice_delivery_order_cost ?? 0;

                        // purchase_total: TANPA PPN sama sekali
                        $purchase_total = $subtotal_item
                            - $discount_1
                            - $discount_2
                            - $discount_tbh
                            - $voucher;

                        // grand_total: hanya untuk log pembanding, TIDAK disimpan ke report
                        $grand_total = $purchase_total + $ppn + $delivery;

                        // ===== Bandingkan dengan nilai lama yang tersimpan =====
                        $old_purchase = $row->old_purchase_total_idr ?? 0;
                        $old_grand    = $row->old_grand_total_idr ?? 0;

                        $selisih_purchase = round($purchase_total - $old_purchase, 2);
                        $selisih_grand    = round($grand_total - $old_grand, 2);

                        $isMismatch = (abs($selisih_purchase) > 1 || abs($selisih_grand) > 1);

                        $logSummary['total']++;
                        $isMismatch ? $logSummary['mismatch']++ : $logSummary['matched']++;

                        // ===== Log detail per nota =====
                        Log::channel('single')->info('[POST DATA] ' . $row->invoice_code, [
                            'subtotal_item'      => $subtotal_item,
                            'discount_1_idr'     => $discount_1,
                            'discount_2_idr'     => $discount_2,
                            'discount_idr'       => $discount_tbh,
                            'voucher_idr'        => $voucher,
                            'ppn_idr'            => $ppn,
                            'delivery_cost_idr'  => $delivery,
                            'purchase_total_NEW' => round($purchase_total, 2),
                            'purchase_total_OLD' => $old_purchase,
                            'selisih_purchase'   => $selisih_purchase,
                            'grand_total_NEW'    => round($grand_total, 2),
                            'grand_total_OLD'    => $old_grand,
                            'selisih_grand'      => $selisih_grand,
                            'status'             => $isMismatch ? 'MISMATCH' : 'OK',
                        ]);

                        // ===== Simpan ke report (hanya invoice_purchase, TANPA PPN) =====
                        $attributes = ['invoice_code' => $row->invoice_code];

                        $values = [
                            'customer_id'                   => $row->customerID,
                            'other_address_id'              => $row->otherAddressID,
                            'customer_name'                 => $row->customer_name,
                            'customer_type'                 => $row->customer_type,
                            'customer_kota'                 => $row->customer_kota,
                            'customer_provinsi'             => $row->customer_provinsi,
                            'customer_zone'                 => $row->customer_zone,
                            'invoice_date'                  => $row->invoice_date,
                            'invoice_brand'                 => $row->invoice_brand,
                            'invoice_type'                  => $row->invoice_type,
                            'invoice_qty'                   => $row->invoice_qty ?? 0,
                            'invoice_purchase'               => round($purchase_total, 2),
                            'invoice_delivery_order_cost'   => $delivery,
                            'created_at'                    => now(),
                            'updated_at'                    => now(),
                        ];

                        $report = CustomerTypeBrandReports::firstOrNew($attributes);
                        $report->fill($values);
                        $report->save();
                    }
                });

            // ===== Ringkasan akhir =====
            Log::channel('single')->info('[POST DATA] SUMMARY', $logSummary);

            return redirect()->back()->with(
                'message',
                "Berhasil Sync data! Total: {$logSummary['total']}, Cocok: {$logSummary['matched']}, Selisih: {$logSummary['mismatch']}. Cek storage/logs/laravel.log untuk detail."
            );
        } catch (\Exception $e) {
            Log::error('Sync data failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // public function postData(Request $request)
    // {
    //     try {
    //         $currentYear = 2024;

    //         DB::table('penjualan_do')
    //             ->leftJoin('penjualan_do_details', 'penjualan_do_details.do_id', '=', 'penjualan_do.id')
    //             ->leftJoin('penjualan_do_item', 'penjualan_do_item.do_id', '=', 'penjualan_do.id')
    //             ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
    //             ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
    //             ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
    //             ->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
    //             ->select(
    //                 DB::raw('SUM(penjualan_do_item.qty) AS invoice_qty'),
    //                 'master_customers.id AS customerID',
    //                 'master_customer_other_addresses.id AS otherAddressID',
    //                 'master_customer_other_addresses.name AS customer_name',
    //                 'master_customer_categories.name AS customer_type',
    //                 'master_customer_other_addresses.text_kota AS customer_kota',
    //                 'master_customer_other_addresses.text_provinsi AS customer_provinsi',
    //                 'master_customer_other_addresses.zone AS customer_zone',
    //                 'penjualan_do.do_code AS invoice_code',
    //                 'penjualan_so.so_date AS invoice_date',
    //                 'penjualan_so.brand_name AS invoice_brand',
    //                 'penjualan_so.type_so AS invoice_type',
    //                 'penjualan_do.id AS do_id',
    //                 'penjualan_do.idr_rate AS do_rate'
    //             )
    //             ->where('penjualan_do.status', 6)
    //             ->whereYear('penjualan_so.so_date', $currentYear)
    //             ->where(function ($query) {
    //                 $query->where('master_customers.status', 1)
    //                     ->orWhere('master_customers.existence', 1);
    //             })
    //             ->groupBy('penjualan_do.do_code')
    //             ->orderBy('penjualan_do.do_code')
    //             ->chunk(100, function ($results) {
    //                 foreach ($results as $row) {
    //                     // ambil detail DO & item
    //                     $penjualan_do_details = DB::table('penjualan_do_details')
    //                         ->where('do_id', $row->do_id)
    //                         ->first();

    //                     $penjualan_do_items = DB::table('penjualan_do_item')
    //                         ->where('do_id', $row->do_id)
    //                         ->get();

    //                     $purchase_total = 0;

    //                     if ($penjualan_do_details && $penjualan_do_items->isNotEmpty()) {
    //                         $subtotal_item = $penjualan_do_items->sum(function ($item) use ($row) {
    //                             return (($item->price - $item->usd_disc) * $item->qty) * $row->do_rate;
    //                         });

    //                         // purchase_total_idr = subtotal - semua diskon - voucher (tanpa ppn)
    //                         $purchase_total = $subtotal_item
    //                             - ($penjualan_do_details->discount_1_idr ?? 0)
    //                             - ($penjualan_do_details->discount_2_idr ?? 0)
    //                             - ($penjualan_do_details->discount_idr ?? 0)
    //                             - ($penjualan_do_details->voucher_idr ?? 0);
    //                     }

    //                     // siapkan data untuk disimpan
    //                     $attributes = [
    //                         'invoice_code' => $row->invoice_code,
    //                     ];

    //                     $values = [
    //                         'customer_id'                 => $row->customerID,
    //                         'other_address_id'            => $row->otherAddressID,
    //                         'customer_name'               => $row->customer_name,
    //                         'customer_type'               => $row->customer_type,
    //                         'customer_kota'               => $row->customer_kota,
    //                         'customer_provinsi'           => $row->customer_provinsi,
    //                         'customer_zone'               => $row->customer_zone,
    //                         'invoice_date'                => $row->invoice_date,
    //                         'invoice_brand'               => $row->invoice_brand,
    //                         'invoice_type'                => $row->invoice_type,
    //                         'invoice_qty'                 => $row->invoice_qty ?? 0,
    //                         'invoice_purchase'            => $purchase_total, // selalu pakai nilai betul
    //                         'invoice_delivery_order_cost' => $penjualan_do_details->delivery_cost_idr ?? 0,
    //                         'updated_at'                  => now()
    //                     ];

    //                     // simpan ke CustomerTypeBrandReports
    //                     $report = CustomerTypeBrandReports::firstOrNew($attributes);
    //                     $report->fill($values);
    //                     $report->save();
    //                 }
    //             });

    //         return redirect()->back()->with('message', 'Berhasil Sync data!');
    //     } catch (\Exception $e) {
    //         Log::error('Sync data failed: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    //     }
    // }

    public function print_report(Request $request)
    {
        $validatedData = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'type' => 'required|integer|in:1,2',
            'nominal' => 'required|integer|in:1,2',
        ]);

        $start = $validatedData['start'];
        $end = $validatedData['end'];
        $type = $validatedData['type'];
        $nominal = $validatedData['nominal'];
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        $reportPath = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_customer_register\\";
        $exportPath = $reportPath . "export\\";
        
        // Select report based on type and nominal
        if ($type == 1) {
            $reportName = $nominal == 1 ? "customer_type_brand.rpt" : "customer_type_brand_non_nominal.rpt";
            $pdfName = $nominal == 1 ? "customer-type-brand-" : "customer-type-brand-non-nominal-";
        } elseif ($type == 2) {
            $reportName = $nominal == 1 ? "report_zone_customer.rpt" : "report_zone_customer_non_nominal.rpt";
            $pdfName = $nominal == 1 ? "customer-zone-" : "customer-zone-non-nominal-";
        }

        $my_report = $reportPath . $reportName;
        $my_pdf = $exportPath . $pdfName . date("Y-m") . ".pdf";

        try {
            if (!file_exists($my_report)) {
                return response()->json(['error' => 'Report file not found'], 404);
            }

            $crapp = new COM("CrystalDesignRunTime.Application");
            $creport = $crapp->OpenReport($my_report, 1);
            $creport->Database->Tables(1)->SetLogOnInfo("LOCAL", "ppi_araya", "root", "");

            $creport->EnableParameterPrompting = false;
            $creport->ParameterFields(2)->SetCurrentValue($new_date_start);
            $creport->ParameterFields(3)->SetCurrentValue($new_date_end);
            $creport->RecordSelectionFormula = "{report_customer_type_brand.invoice_date}>=#$start# AND {report_customer_type_brand.invoice_date}<=#$end#";

            $creport->ExportOptions->DiskFileName = $my_pdf;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = 31;
            // $creport->ExportOptions->FormatType = 36; // Change to 36 for Excel (97-2003)
            // $creport->ExportOptions->FormatType = 33; //Excel (data only)
            $creport->Export(false);

            $creport = null;
            $crapp = null;

            if (file_exists($my_pdf)) {
                return response()->download($my_pdf, basename($my_pdf));
            } else {
                return response()->json(['error' => 'PDF not generated'], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Report generation failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while generating the report'], 500);
        }
    }

    public function removeDt(Request $request)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // $currentYear = 2024;     

        DB::table('report_customer_type_brand')
            ->whereMonth('invoice_date', $currentMonth)
            ->whereYear('invoice_date', $currentYear)
            ->delete();

        return redirect()->back()->with('message', 'Berhasil remove data!');
    }

    public function exportReport(Request $request)
    {
        $validatedData = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'type' => 'required|integer|in:1,2',
            'nominal' => 'required|integer|in:1,2',
            'action' => 'required|string|in:print,excel',
        ]);

        $start = $validatedData['start'];
        $end = $validatedData['end'];
        $type = $validatedData['type'];
        $nominal = $validatedData['nominal'];
        $action = $validatedData['action'];

        $baseReportPath = public_path('cr/report/management/report_customer_register/');
        $exportPath = $baseReportPath . "export\\";

        if ($type == 1) {
            $reportName = $nominal == 1 ? "customer_type_brand.rpt" : "customer_type_brand_non_nominal.rpt";
            $fileName = $nominal == 1 ? "customer-type-brand-" : "customer-type-brand-non-nominal-";
        } else {
            $reportName = $nominal == 1 ? "report_zone_customer.rpt" : "report_zone_customer_non_nominal.rpt";
            $fileName = $nominal == 1 ? "customer-zone-" : "customer-zone-non-nominal-";
        }

        $my_report = $baseReportPath . $reportName;
        $outputFile = $exportPath . $fileName . date("Y-m-d_H-i-s") . ($action === 'print' ? ".pdf" : ".xls");

        try {
            if (!file_exists($my_report)) {
                return response()->json(['error' => 'Report file not found'], 404);
            }

            // Create export directory if it doesn't exist
            if (!file_exists($exportPath)) {
                mkdir($exportPath, 0777, true);
            }

            $crapp = new COM("CrystalDesignRunTime.Application");
            $creport = $crapp->OpenReport($my_report, 1);

            $creport->Database->Tables(1)->SetLogOnInfo("LOCAL", "ppi_araya", "root", "");
            $creport->EnableParameterPrompting = false;
            $creport->ParameterFields(2)->SetCurrentValue(date('d-m-Y', strtotime($start)));
            $creport->ParameterFields(3)->SetCurrentValue(date('d-m-Y', strtotime($end)));
            $creport->RecordSelectionFormula = "{report_customer_type_brand.invoice_date}>=#$start# AND {report_customer_type_brand.invoice_date}<=#$end#";

            $creport->ExportOptions->DiskFileName = $outputFile;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = $action === 'print' ? 31 : 29; // 31 untuk PDF, 29 untuk Excel

            $creport->Export(false);
            $creport = null;
            $crapp = null;

            if (file_exists($outputFile)) {
                Log::info('File generated successfully: ' . $outputFile);
                if ($action === 'print') {
                    // Return URL for PDF to be displayed in iframe
                    $pdfUrl = asset('cr/report/management/report_customer_register/export/' . basename($outputFile));
                    return response()->json(['success' => true, 'pdf_url' => $pdfUrl]);
                } else {
                    // For Excel, still download directly
                    return response()->download($outputFile, basename($outputFile))->deleteFileAfterSend(true);
                }
            } else {
                Log::error('File not generated: ' . $outputFile);
                return response()->json(['error' => 'File not generated'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while generating the report: ' . $e->getMessage()], 500);
        }
    }

    public function export_officer(Request $request)
    {
        $validatedData = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'officer_name' => 'nullable|string', // Menerima "All" atau nama officer
            'action' => 'required|string|in:print,excel',
        ]);

        $start = $validatedData['start'];
        $end = $validatedData['end'];
        // Default ke string kosong jika null, tapi umumnya dari form akan berisi "All" atau nama officer
        $officer = $validatedData['officer_name'] ?? ''; 
        $action = $validatedData['action'];

        $baseReportPath = public_path('cr/report/management/report_customer_register/');
        $exportPath = $baseReportPath . "export\\";

        $reportName = "officer_report_nominal_4.rpt";
        $fileName = "report-officer-";

        $my_report = $baseReportPath . $reportName;
        $outputFile = $exportPath . $fileName . date("Y-m-d_H-i-s") . ($action === 'print' ? ".pdf" : ".xls");

        try {
            if (!file_exists($my_report)) {
                return response()->json(['error' => 'Report file not found'], 404);
            }

            if (!file_exists($exportPath)) {
                mkdir($exportPath, 0777, true);
            }

            // Inisialisasi COM Object
            $crapp = new COM("CrystalDesignRunTime.Application");
            $creport = $crapp->OpenReport($my_report, 1);

            $creport->Database->Tables(1)->SetLogOnInfo("LOCAL", "ppi_araya", "root", "");
            $creport->EnableParameterPrompting = false;

            // ✅ Sesuaikan parameter index sesuai rpt (2=start, 3=end, 4=officer)
            $creport->ParameterFields(2)->SetCurrentValue(date('d-m-Y', strtotime($start)));
            $creport->ParameterFields(3)->SetCurrentValue(date('d-m-Y', strtotime($end)));
            
            // Mengirimkan nilai officer (walaupun tidak digunakan untuk filtering, ini mungkin dibutuhkan di header report)
            $creport->ParameterFields(4)->SetCurrentValue($officer); 

            // --- Logika Utama Perbaikan Filter Officer ---
            $officerFilter = "";
            if (!empty($officer) && $officer !== 'All' && $officer !== 'pilih_officer') {
                // Terapkan filter hanya jika nilai BUKAN 'All' atau 'pilih_officer' (atau string kosong)
                $officerFilter = " AND {Command.officer}='$officer'";
            }

            // Konstruksi Record Selection Formula
            $creport->RecordSelectionFormula =
                "({Command.invoice_date}>=#$start# AND " .
                "{Command.invoice_date}<=#$end#)" .
                $officerFilter; 
            // ---------------------------------------------

            $creport->ExportOptions->DiskFileName = $outputFile;
            $creport->ExportOptions->DestinationType = 1;
            // PDF=31, Excel=29
            $creport->ExportOptions->FormatType = $action === 'print' ? 31 : 29;

            $creport->Export(false);
            $creport = null;
            $crapp = null;

            if (file_exists($outputFile)) {

                if ($action === 'print') {
                    $pdfUrl = asset('cr/report/management/report_customer_register/export/' . basename($outputFile));
                    return response()->json(['success' => true, 'pdf_url' => $pdfUrl]);
                }

                // Download Excel
                return response()->download($outputFile, basename($outputFile))->deleteFileAfterSend(true);
            }

            return response()->json(['error' => 'File not generated'], 500);

        } catch (\Exception $e) {
           dd($e);
            Log::error('export_officer FAILED: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}