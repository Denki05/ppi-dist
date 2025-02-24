<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use COM;

class ReportEmployeePerformanceController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.employee_performance.";
        $this->route = "superuser.report.employee_performance";
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

    public function index() 
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access)) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $get_pic = Customer::select('pic')->distinct()->get(); // Menambahkan distinct()
        $get_officer = CustomerOtherAddress::select('officer')
            ->distinct()
            ->get();

        $data = [
            'pic' => $get_pic,
            'officer' => $get_officer,
        ];

        return view($this->view . "index", $data);
    }

    public function print_report(Request $request)
{
    $validatedData = $request->validate([
        'period_from' => 'required|date',
        'period_to' => 'required|date',
        'nominal' => 'required|integer|in:1,2',
        'type' => 'required|integer|in:1,2',
        'salesman_officer' => 'nullable|array',
    ]);

    $start = $validatedData['period_from'];
    $end = $validatedData['period_to'];
    $nominal = $validatedData['nominal'];
    $type = $validatedData['type'];
    $salesman = $request->input('salesman_officer', []);

    // Menggunakan variabel lokal date
    $date = date("Y-m");

    // dd($salesman);

    // Format tanggal
    $new_date_start = date('d-m-Y', strtotime($start));
    $new_date_end = date('d-m-Y', strtotime($end));

    $officerSearch = empty($salesman) || in_array('all', $salesman)
            ? '1=1'
            : collect($salesman)->map(function($value) {
                return "{Command.officer}='$value'";
            })->implode(' OR ');

    $officerSearch1 = empty($salesman) || in_array('all', $salesman)
            ? '1=1'
            : collect($salesman)->map(function($value) {
                return "{penjualan_do.officer}='$value'";
            })->implode(' OR ');


    $basePath = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\";

    if ($type == 1) {
        if ($nominal == 1) {
            $my_report = "{$basePath}pic_report_nominal.rpt";
            $my_pdf = "{$basePath}export\\pic_report_nominal{$date}.pdf";
        } elseif ($nominal == 2) { 
            $my_report = "{$basePath}pic_report_non_nominal.rpt";
            $my_pdf = "{$basePath}export\\pic_report_non_nominal{$date}.pdf";
        }
    } elseif ($type == 2) {
        if ($nominal == 1) {
            $my_report = "{$basePath}officer_report_nominal_3.rpt";
            $my_pdf = "{$basePath}export\\officer_report_nominal-{$date}.pdf";
        } elseif ($nominal == 2) { 
            $my_report = "{$basePath}officer_report_non_nominal.rpt";
            $my_pdf = "{$basePath}export\\officer_report_non_nominal-{$date}.pdf";
        }
    }

    // dd($nominal);

    // Kirim ke fungsi generateReport
    return $this->generateReport($my_report, $my_pdf, $new_date_start, $new_date_end, $start, $end, $type, $officerSearch, $officerSearch1, $nominal);
}

    private function generateReport($reportPath, $pdfPath, $newDateStart, $newDateEnd, $start, $end, $type, $officerSearch, $officerSearch1, $nominal)
    {
        $dbConfig = [
            'server' => 'LOCAL',
            'user' => 'root',
            'password' => '',
            'database' => 'ppi_araya',
        ];

        try {
            // Buat objek Crystal Reports
            $crapp = new COM("CrystalDesignRunTime.Application");
            $creport = $crapp->OpenReport($reportPath, 1);

            // Set database logon info
            $creport->Database->Tables(1)->SetLogOnInfo($dbConfig['server'], $dbConfig['database'], $dbConfig['user'], $dbConfig['password']);

            // Nonaktifkan prompt parameter
            $creport->EnableParameterPrompting = false;

            // Set parameter laporan
            $creport->ParameterFields(2)->SetCurrentValue($newDateStart);
            $creport->ParameterFields(3)->SetCurrentValue($newDateEnd);

            if ($type == 1) {
                $creport->RecordSelectionFormula = "{report_customer_type_brand.invoice_date}>=#$start#AND{report_customer_type_brand.invoice_date}<=#$end#";
            } elseif ($type == 2) {
                if ($nominal == 1) {
                    $creport->RecordSelectionFormula = "($officerSearch)AND{Command.invoice_date}>=#$start#AND{Command.invoice_date}<=#$end#AND{Command.status}=6";
                } elseif ($nominal == 2) {
                    $creport->RecordSelectionFormula = "($officerSearch1)AND{penjualan_so.so_date}>=#$start#AND{penjualan_so.so_date}<=#$end#AND{penjualan_do.status}=6";
                }
            }

            // Export ke PDF
            $creport->ExportOptions->DiskFileName = $pdfPath;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1; // Export ke file
            $creport->ExportOptions->FormatType = 31; // PDF type
            $creport->Export(false);

            // Bersihkan objek COM
            $creport = null;
            $crapp = null;

            // Unduh file PDF
            return response()->download($pdfPath)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal membuat laporan: ' . $e->getMessage()], 500);
        }
    }
}