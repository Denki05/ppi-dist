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
        $start = $request->all()['period_from'];
        $end = $request->all()['period_to'];
        $vendor = $request->all()['vendor_name'];
        $date = date("Y-m-d_H-i-s");

        if ( $start == null && $end == null && $vendor == null ) {
            return response()->json(['error' => 'Input tanggal dan vendor tidak boleh kosong.'], 400);
        }

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
            // Refactored line to support older PHP versions
            $crapp = null; // Initialize to null
            try {
                $crapp = new COM($COM_Object);
            } catch (\com_exception $e) { // Catch COM specific exceptions
                throw new \Exception("Unable to Create Crystal Reports Object: " . $e->getMessage());
            }
            
            if (!$crapp) { // Fallback check
                 throw new \Exception("Unable to Create Crystal Reports Object (COM object is null).");
            }

            $creport = $crapp->OpenReport($my_report,1);

            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

            $creport->EnableParameterPrompting = FALSE;

            $creport->ParameterFields(2)->SetCurrentValue ("$new_date_start");
            $creport->ParameterFields(3)->SetCurrentValue ("$new_date_end");

            $creport->RecordSelectionFormula = "{Command.tanggal_so} >= #$start# AND {Command.tanggal_so} <= #$end# AND {Command.nama_vendor} = '$vendor'";

            $creport->ExportOptions->DiskFileName=$my_pdf;
            $creport->ExportOptions->PDFExportAllPages=true;
            $creport->ExportOptions->DestinationType=1;
            $creport->ExportOptions->FormatType=31;
            $creport->Export(false);
     
            $creport = null;
            $crapp = null;
            $ObjectFactory = null;

            return response()->json(['success' => true, 'pdf_url' => $pdf_url]);

        } catch (\Exception $e) {
            \Log::error('Crystal Reports Export Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat laporan: ' . $e->getMessage()], 500);
        }
    }
}