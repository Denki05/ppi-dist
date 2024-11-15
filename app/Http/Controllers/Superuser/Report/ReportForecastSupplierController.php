<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Vendor;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Setting\UserMenu;
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
        $date = date("Y-m");

        // dd($vendor);

        if ( $start == null && $end == null && $vendor == null ) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        // dd($vendor);

        // Convert date
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\forecasting_principal\\forecasting_principal_v2.rpt";
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\forecasting_principal\\export\\forcasting-principal'.$date.'.pdf';

        $my_server = "LOCAL"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi_araya";
        $COM_Object = "CrystalDesignRunTime.Application";

        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;

        $creport->ParameterFields(2)->SetCurrentValue ("$new_date_start"); // <-- param 1
        $creport->ParameterFields(3)->SetCurrentValue ("$new_date_end"); // <-- param 2

        // pass parameter record selection formula

        $creport->RecordSelectionFormula = "{Command.tanggal_so} >= #$start# AND {Command.tanggal_so} <= #$end# AND {Command.nama_vendor} = '$vendor'";

         //export to PDF process
        $creport->ExportOptions->DiskFileName=$my_pdf; //export to pdf
        $creport->ExportOptions->PDFExportAllPages=true;
        $creport->ExportOptions->DestinationType=1; // export to file
        $creport->ExportOptions->FormatType=31; // PDF type
        $creport->Export(false);
 
         //------ Release the variables ------
        $creport = null;
        $crapp = null;
        $ObjectFactory = null;
 
        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\forecasting_principal\\export\\forcasting-principal'.$date.'.pdf';
 
        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }
}