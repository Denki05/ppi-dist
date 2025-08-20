<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Vendor;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\File;
use Auth;
use COM;
use DB;

class ReportForecastSupplierController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.forecast_supplier.";
        $this->route = "superuser.report.forecast_supplier";
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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['vendor'] = Vendor::where('type', 2)->get();
        
        return view($this->view."index", $data);
    }

    public function printReport(Request $request)
    {
        $start = $request->input('period_from');
        $end = $request->input('period_to');
        $vendor = $request->input('vendor_name');
        $semesterCount = $request->input('semester_count'); // Sekarang Anda menerima semester_count

        // Validasi input
        if (empty($vendor)) {
            return response()->json(['error' => 'Vendor tidak boleh kosong.'], 400);
        }

        if (empty($start) || empty($end)) {
            // Ini akan mencakup kasus di mana semester dipilih dan tanggal dihitung di frontend
            // atau tanggal manual tidak diisi
            return response()->json(['error' => 'Periode laporan (Dari Bulan & Sampai Bulan) tidak boleh kosong.'], 400);
        }

        $date = date("Y-m-d_H-i-s");

        // Format tanggal untuk Crystal Reports jika diperlukan dalam format d-m-Y
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        $reportBaseDir = public_path('cr/report/forecasting_principal/');
        $exportDir = $reportBaseDir . 'export/';
        $my_report = $reportBaseDir . 'forecasting_principal_v2.rpt';
        $fileName = 'forecasting-principal-' . $date . '.pdf';
        $my_pdf = $exportDir . $fileName;
        $pdf_url = asset('cr/report/forecasting_principal/export/' . $fileName);

        if (!File::isDirectory($exportDir)) {
            File::makeDirectory($exportDir, 0777, true, true);
        }

        $my_server = "LOCAL";
        $my_user = "root";
        $my_password = "";
        $my_database = "ppi_araya";
        $COM_Object = "CrystalDesignRunTime.Application";

        try {
            $crapp = null;
            try {
                $crapp = new COM($COM_Object);
            } catch (\com_exception $e) {
                // Log the COM exception for debugging
                Log::error('COM Exception: ' . $e->getMessage());
                throw new \Exception("Unable to Create Crystal Reports Object: " . $e->getMessage());
            }
            
            if (!$crapp) {
                throw new \Exception("Unable to Create Crystal Reports Object (COM object is null).");
            }

            $creport = $crapp->OpenReport($my_report,1);

            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

            $creport->EnableParameterPrompting = FALSE;

            // Pastikan ParameterFields 2 dan 3 sesuai dengan urutan parameter di laporan Crystal Anda
            // Gunakan $new_date_start dan $new_date_end yang sudah diformat d-m-Y
            $creport->ParameterFields(2)->SetCurrentValue("$new_date_start");
            $creport->ParameterFields(3)->SetCurrentValue("$new_date_end");
            $creport->ParameterFields(5)->SetCurrentValue("$semesterCount");
            // Jika Anda memiliki parameter lain di Crystal Report yang ingin diisi (misal untuk semesterCount), tambahkan di sini
            // Contoh: $creport->ParameterFields(4)->SetCurrentValue($semesterCount);

            // Record Selection Formula menggunakan $start dan $end dalam format Y-m-d yang diterima dari frontend
            $creport->RecordSelectionFormula = "{Command.tanggal_so} >= #{$start}# AND {Command.tanggal_so} <= #{$end}# AND {Command.nama_vendor} = '$vendor'";

            $creport->ExportOptions->DiskFileName=$my_pdf;
            $creport->ExportOptions->PDFExportAllPages=true;
            $creport->ExportOptions->DestinationType=1;
            $creport->ExportOptions->FormatType=31; // crEFTPDF
            $creport->Export(false);
        
            // Penting: Kosongkan objek COM setelah selesai
            $creport = null;
            $crapp = null;
            // $ObjectFactory = null; // Ini tidak diperlukan jika $crapp dibuat langsung

            return response()->json(['success' => true, 'pdf_url' => $pdf_url]);

        } catch (\Exception $e) {
            // Log error secara lebih detail untuk debugging
            Log::error('Crystal Reports Export Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal membuat laporan: ' . $e->getMessage()], 500);
        }
    }

    public function printReportSummary(Request $request)
    {
        $start = $request->input('period_from');
        $end = $request->input('period_to');
        $vendor = $request->input('vendor_name');

        // Validasi input
        if (empty($vendor)) {
            return response()->json(['error' => 'Vendor tidak boleh kosong.'], 400);
        }

        if (empty($start) || empty($end)) {
            // Ini akan mencakup kasus di mana semester dipilih dan tanggal dihitung di frontend
            // atau tanggal manual tidak diisi
            return response()->json(['error' => 'Periode laporan (Dari Bulan & Sampai Bulan) tidak boleh kosong.'], 400);
        }

        $date = date("Y-m-d_H-i-s");

        // Format tanggal untuk Crystal Reports jika diperlukan dalam format d-m-Y
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        $reportBaseDir = public_path('cr/report/forecasting_principal/');
        $exportDir = $reportBaseDir . 'export/';
        $my_report = $reportBaseDir . 'forecasting_principal_summary.rpt';
        $fileName = 'forecasting-principal-summary-' . $date . '.pdf';
        $my_pdf = $exportDir . $fileName;
        $pdf_url = asset('cr/report/forecasting_principal/export/' . $fileName);

        if (!File::isDirectory($exportDir)) {
            File::makeDirectory($exportDir, 0777, true, true);
        }

        $my_server = "LOCAL";
        $my_user = "root";
        $my_password = "";
        $my_database = "ppi_araya";
        $COM_Object = "CrystalDesignRunTime.Application";

        try {
            $crapp = null;
            try {
                $crapp = new COM($COM_Object);
            } catch (\com_exception $e) {
                // Log the COM exception for debugging
                Log::error('COM Exception: ' . $e->getMessage());
                throw new \Exception("Unable to Create Crystal Reports Object: " . $e->getMessage());
            }
            
            if (!$crapp) {
                throw new \Exception("Unable to Create Crystal Reports Object (COM object is null).");
            }

            $creport = $crapp->OpenReport($my_report,1);

            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

            $creport->EnableParameterPrompting = FALSE;

            // Pastikan ParameterFields 2 dan 3 sesuai dengan urutan parameter di laporan Crystal Anda
            // Gunakan $new_date_start dan $new_date_end yang sudah diformat d-m-Y
            $creport->ParameterFields(2)->SetCurrentValue("$new_date_start");
            $creport->ParameterFields(3)->SetCurrentValue("$new_date_end");
            // Jika Anda memiliki parameter lain di Crystal Report yang ingin diisi (misal untuk semesterCount), tambahkan di sini
            // Contoh: $creport->ParameterFields(4)->SetCurrentValue($semesterCount);

            // Record Selection Formula menggunakan $start dan $end dalam format Y-m-d yang diterima dari frontend
            $creport->RecordSelectionFormula = "{Command.tanggal_so} >= #{$start}# AND {Command.tanggal_so} <= #{$end}# AND {Command.nama_vendor} = '$vendor'";

            $creport->ExportOptions->DiskFileName=$my_pdf;
            $creport->ExportOptions->PDFExportAllPages=true;
            $creport->ExportOptions->DestinationType=1;
            $creport->ExportOptions->FormatType=31; // crEFTPDF
            $creport->Export(false);
        
            // Penting: Kosongkan objek COM setelah selesai
            $creport = null;
            $crapp = null;
            // $ObjectFactory = null; // Ini tidak diperlukan jika $crapp dibuat langsung

            return response()->json(['success' => true, 'pdf_url' => $pdf_url]);

        } catch (\Exception $e) {
            // Log error secara lebih detail untuk debugging
            Log::error('Crystal Reports Export Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal membuat laporan: ' . $e->getMessage()], 500);
        }
    }
}