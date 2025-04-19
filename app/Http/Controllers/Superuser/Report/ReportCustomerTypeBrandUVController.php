<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Reports\CustomerTypeBrandReports;
use App\Exports\Reports\CustomerTypeBrandReportExport;
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
            $reportName = $nominal == 1 ? "customer_type_brand_uv_2.rpt" : "customer_type_brand_uv_2.rpt";
            $pdfName = $nominal == 1 ? "customer-type-brand-uv" : "customer-type-brand-uv";
        } elseif ($type == 2) {
            $reportName = $nominal == 1 ? "register_by_zone_uv_2.rpt" : "register_by_zone_uv_2.rpt";
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
            // dd($e);
            \Log::error('Report generation failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while generating the report'], 500);
        }
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

        // Ambil daftar unik brand
        $brands = DB::table('report_customer_type_brand')
            ->distinct()
            ->pluck('invoice_brand')
            ->toArray();

        // Ambil data grouping berdasarkan kategori dan customer
        $data = DB::table('report_customer_type_brand')
            ->select(
                'customer_type', // Category
                'customer_name',
                'customer_kota',
                'invoice_brand',
                DB::raw('SUM(invoice_qty) as total_qty'),
                DB::raw('SUM(invoice_purchase) as total_purchase') // Menambahkan total_purchase
            )
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('customer_type', 'customer_name', 'invoice_brand')
            ->get();

        // Grouping data berdasarkan kategori dan customer
        $groupedData = [];
        foreach ($data as $row) {
            $category = $row->customer_type;
            $customerName = $row->customer_name;
            $customerKota = $row->customer_kota;
            $brand = $row->invoice_brand;
            $totalQty = $row->total_qty;
            $totalPurchase = $row->total_purchase; // Mengambil total_purchase

            if (!isset($groupedData[$category])) {
                $groupedData[$category] = [];
            }

            if (!isset($groupedData[$category][$customerName])) {
                $groupedData[$category][$customerName] = [
                    'name' => $customerName,
                    'kota' => $customerKota,
                    'brand_data' => array_fill_keys($brands, ['qty' => 0, 'purchase' => 0]), // Inisialisasi qty dan purchase semua brand ke 0
                ];
            }

            // Masukkan qty dan purchase ke dalam brand yang sesuai
            $groupedData[$category][$customerName]['brand_data'][$brand] = [
                'qty' => $totalQty,
                'purchase' => $totalPurchase
            ];
        }

        // Ekspor data ke Excel
        return Excel::download(new CustomerTypeBrandReportExport($groupedData, $brands), 'register_customer_type_brand.xlsx');   
    }
}