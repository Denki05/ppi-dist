<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;
use Auth;
use COM;

class ReportEmployeePerformanceProductController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.employee_performance_product.";
        $this->route = "superuser.report.employee_performance_product";
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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['sales'] = DB::table('penjualan_do')->select('officer')->distinct()->get();

        return view($this->view."index", $data);
    }

    // public function print_report(Request $request)
    // {
    //     $request->validate([
    //         'period_from' => 'required|date',
    //         'period_to' => 'required|date',
    //         'sales' => 'required|array',
    //     ]);

    //     $start = $request->input('period_from');
    //     $end = $request->input('period_to');
    //     $sales = $request->input('sales');
    //     $date = date("Y-m");
    //     $status_do = 6;

    //     $new_date_start = Carbon::parse($start)->format('d-m-Y');
    //     $new_date_end = Carbon::parse($end)->format('d-m-Y');

    //     $officerSearch = '';
    //     if (!in_array('all', $sales)) {
    //         $officerSearch = collect($sales)->map(function ($value) {
    //             return "{penjualan_do.officer}='$value'";
    //         })->implode(' OR ');
    //     }

    //     $reportPath = public_path('cr/report/management/report_employee_performance/sales_performence.rpt');
    //     $exportPath = public_path("cr/report/management/report_employee_performance/export/kinerja-sales-{$date}.pdf");

    //     $server = env('DB_SERVER', 'LOCAL_3');
    //     $user = env('DB_USERNAME', 'root');
    //     $password = env('DB_PASSWORD', '');
    //     $database = env('DB_DATABASE', 'ppi_araya');
    //     $COM_Object = "CrystalDesignRunTime.Application";

    //     try {

    //         if (!class_exists('COM')) {
    //             throw new \Exception("COM class is not available on this server.");
    //         }

    //         $crapp = new COM($COM_Object) or die("Unable to create Crystal Reports Object");
    //         $creport = $crapp->OpenReport($reportPath, 1);

    //         $creport->Database->Tables(1)->SetLogOnInfo($server, $database, $user, $password);

    //         $creport->EnableParameterPrompting = false;
    //         // $creport->ParameterFields(2)->SetCurrentValue($new_date_start);
    //         // $creport->ParameterFields(3)->SetCurrentValue($new_date_end);

    //         $recordSelectionFormula = [];
    //         if ($officerSearch) {
    //             $recordSelectionFormula[] = "($officerSearch)";
    //         }

    //         $recordSelectionFormula[] = "{penjualan_so.so_date}>=#$start#";
    //         $recordSelectionFormula[] = "{penjualan_so.so_date}<=#$end#";
    //         $recordSelectionFormula[] = "{penjualan_do.status}=$status_do";

    //         $creport->RecordSelectionFormula = implode(' AND ', $recordSelectionFormula);

    //         $creport->ExportOptions->DiskFileName = $exportPath;
    //         $creport->ExportOptions->PDFExportAllPages = true;
    //         $creport->ExportOptions->DestinationType = 1;
    //         $creport->ExportOptions->FormatType = 31;
    //         $creport->Export(false);

    //         $creport = null;
    //         $crapp = null;

    //     }catch (\Exception $e) {
    //         dd($e);
    //         return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
    //     }

    //     if (file_exists($exportPath)) {
    //         return response()->file($exportPath, [
    //             'Content-Type' => 'application/pdf',
    //             'Content-Disposition' => 'attachment; filename="' . basename($exportPath) . '"'
    //         ]);
    //     } else {
    //         return response()->json(['error' => 'Report file not found'], 404);
    //     }
    // }

    public function print_report(Request $request)
    {
        $validatedData = $request->validate([
            'period_from' => 'required|date',
            'period_to' => 'required|date',
            'sales' => 'required|array',
        ]);

        $start = $validatedData['period_from'];
        $end = $validatedData['period_to'];
        $sales = $validatedData['sales'];
        $status_do = 6;

        // Menggunakan variabel lokal date
        $date = date("Y-m");

        // dd($salesman);

        // Format tanggal
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        $officerSearch = empty($sales) || in_array('all', $sales)
                ? '1=1'
                : collect($sales)->map(function($value) {
                    return "{penjualan_do1.officer}='$value'";
                })->implode(' OR ');


        $basePath = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\";

        $my_report = "{$basePath}sales_performence.rpt";
        $my_pdf = "{$basePath}export\\kinerja_sales_{$date}.pdf";

        // dd($nominal);

        // Kirim ke fungsi generateReport
        return $this->generateReport($my_report, $my_pdf, $new_date_start, $new_date_end, $start, $end, $officerSearch);
    }

    private function generateReport($reportPath, $pdfPath, $newDateStart, $newDateEnd, $start, $end, $officerSearch)
    {
        $dbConfig = [
            'server' => 'LOCAL_3',
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
            $creport->ParameterFields(3)->SetCurrentValue($newDateStart);
            $creport->ParameterFields(4)->SetCurrentValue($newDateEnd);

            $creport->RecordSelectionFormula = "($officerSearch)AND{penjualan_so1.so_date}>=#$start#AND{penjualan_so1.so_date}<=#$end#AND{penjualan_do1.status}=6";

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
            dd($e);
            return response()->json(['error' => 'Gagal membuat laporan: ' . $e->getMessage()], 500);
        }
    }
}