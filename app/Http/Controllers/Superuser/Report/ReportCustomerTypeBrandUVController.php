<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Reports\CustomerTypeBrandReports;
use App\Entities\Reports\CustomerTypeBrandReportsLog;
use App\Exports\Reports\CustomerTypeBrandReportUvExport;
Use App\Entities\Penjualan\SalesOrder;
use Maatwebsite\Excel\Facades\Excel;
use App\Entities\Setting\UserMenu;
use Carbon\Carbon;
use Exception;
use PDF;
use Auth;
use COM;
use DB;


class ReportCustomerTypeBrandUVController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.customer_type_brand_uv.";
        $this->route = "superuser.report.customer_type_brand_uv";
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

        $reportPath = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\uv\\";
        $exportPath = $reportPath . "export\\";
        
        // Select report based on type and nominal
        if ($type == 1) {
            $reportName = $nominal == 1 ? "customer_type_brand_uv.rpt" : "customer_type_brand_uv.rpt";
            $pdfName = $nominal == 1 ? "customer-type-brand-uv" : "customer-type-brand-uv";
        } elseif ($type == 2) {
            $reportName = $nominal == 1 ? "register_by_zone_uv.rpt" : "register_by_zone_uv.rpt";
            $pdfName = $nominal == 1 ? "customer-zone-uv-" : "customer-zone-uv-";
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
            $creport->RecordSelectionFormula = "{report_customer_type_brand_history1.invoice_date}>=#$start# AND {report_customer_type_brand_history1.invoice_date}<=#$end#";

            $creport->ExportOptions->DiskFileName = $my_pdf;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = 31;
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

    public function print_report_2(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        // Parsing tanggal dengan format yang lebih aman
        $startDate = Carbon::createFromFormat('Y-m-d', $validated['start'])->format('Y-m-d');
        $endDate = Carbon::createFromFormat('Y-m-d', $validated['end'])->format('Y-m-d');

        // Query data dari database
        $query = CustomerTypeBrandReportsLog::selectRaw('
                customer_type, 
                customer_name, 
                customer_kota, 
                invoice_brand, 
                SUM(invoice_qty) as total_qty, 
                SUM(invoice_purchase) as total_purchase
            ')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('customer_type', 'customer_name', 'customer_kota', 'invoice_brand')
            ->orderBy('customer_type')
            ->orderBy('customer_name');

        // Eksekusi query utama
        $data = $query->get();

        // List brand yang digunakan untuk menghindari duplikasi kode
        $brands = ['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'];

        // Group data berdasarkan customer_type
        $groupedData = $data->groupBy('customer_type')->map(function ($items) use ($brands) {
            $totals = [];

            // Hitung total per kategori
            foreach ($brands as $brand) {
                $totals["total_{$brand}_qty"] = $items->where('invoice_brand', $brand)->sum('total_qty') ?: 0;
                $totals["total_{$brand}_purchase"] = $items->where('invoice_brand', $brand)->sum('total_purchase') ?: 0;
            }

            $totals['total_customer_qty'] = $items->sum('total_qty') ?: 0;
            $totals['total_customer_purchase'] = $items->sum('total_purchase') ?: 0;

            return ['items' => $items, 'totals' => $totals];
        });

        // Hitung total global dengan query yang sudah dikloning
        $globalTotals = [];
        foreach ($brands as $brand) {
            $globalTotals["total_{$brand}_qty"] = $data->where('invoice_brand', $brand)->sum('total_qty') ?: 0;
            $globalTotals["total_{$brand}_purchase"] = $data->where('invoice_brand', $brand)->sum('total_purchase') ?: 0;
        }

        $globalTotals['total_customer_qty'] = $data->sum('total_qty') ?: 0;
        $globalTotals['total_customer_purchase'] = $data->sum('total_purchase') ?: 0;

        // Data untuk dikirim ke PDF
        $pdfData = compact('groupedData', 'globalTotals', 'startDate', 'endDate');

        // Generate PDF
        $pdf = PDF::loadView('superuser.report.customer_type_brand_uv.pdf_export_uv', $pdfData)
                ->setPaper('a3', 'landscape');

        // Jika ingin di-download
        if ($request->has('download')) {
            return $pdf->download("Laporan-Customer-Type-Brand-UV.pdf");
        }

        return $pdf->stream("Laporan-Customer-Type-Brand-UV.pdf");
    }

    public function removeDt(Request $request)
    {
        DB::table('report_customer_type_brand_history')->truncate();
        DB::table('failed_invoices_log')->truncate();

        return redirect()->back()->with('message', 'Berhasil remove data!');
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->start;
        $endDate = $request->end;

        // Ambil daftar brand unik
        $brands = DB::table('report_customer_type_brand_history')
            ->distinct()
            ->pluck('invoice_brand')
            ->toArray();

        // Ambil data utama
        $data = DB::table('report_customer_type_brand_history')
            ->select(
                'customer_type',
                'customer_name',
                'customer_kota',
                'invoice_brand',
                DB::raw('SUM(invoice_qty) as total_qty'),
                DB::raw('SUM(invoice_purchase) as total_purchase')
            )
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('customer_type', 'customer_name', 'customer_kota', 'invoice_brand')
            ->get();

        // Inisialisasi struktur data
        $groupedData = [];
        $globalTotals = [];

        foreach ($brands as $brand) {
            $globalTotals["total_{$brand}_qty"] = 0;
            $globalTotals["total_{$brand}_purchase"] = 0;
        }
        $globalTotals['total_customer_qty'] = 0;
        $globalTotals['total_customer_purchase'] = 0;

        foreach ($data as $row) {
            $kategori = $row->customer_type;

            if (!isset($groupedData[$kategori])) {
                $groupedData[$kategori] = [
                    'items' => collect(),
                    'totals' => [],
                ];

                foreach ($brands as $brand) {
                    $groupedData[$kategori]['totals']["total_{$brand}_qty"] = 0;
                    $groupedData[$kategori]['totals']["total_{$brand}_purchase"] = 0;
                }
                $groupedData[$kategori]['totals']['total_customer_qty'] = 0;
                $groupedData[$kategori]['totals']['total_customer_purchase'] = 0;
            }

            // Tambahkan item ke kategori
            $groupedData[$kategori]['items']->push($row);

            $qty = $row->total_qty;
            $purchase = $row->total_purchase;
            $brand = $row->invoice_brand;

            // Tambahkan subtotal per kategori
            $groupedData[$kategori]['totals']["total_{$brand}_qty"] += $qty;
            $groupedData[$kategori]['totals']["total_{$brand}_purchase"] += $purchase;
            $groupedData[$kategori]['totals']['total_customer_qty'] += $qty;
            $groupedData[$kategori]['totals']['total_customer_purchase'] += $purchase;

            // Tambahkan ke total keseluruhan
            $globalTotals["total_{$brand}_qty"] += $qty;
            $globalTotals["total_{$brand}_purchase"] += $purchase;
            $globalTotals['total_customer_qty'] += $qty;
            $globalTotals['total_customer_purchase'] += $purchase;
        }

        return Excel::download(
            new CustomerTypeBrandReportUvExport($groupedData, $globalTotals, $brands),
            'laporan_customer_type_brand.xlsx'
        );
    }
}