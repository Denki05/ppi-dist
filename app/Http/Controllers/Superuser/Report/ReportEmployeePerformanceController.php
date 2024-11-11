<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\DB;
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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view($this->view."index");
    }

    public function print_pic(Request $request)
    {
        $start = $request->all()['start'];
        $end = $request->all()['end'];
        $nominal = $request->input('nominal');
        $date = date("Y-m");

        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\pic_report_non_nominal.rpt";
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\export\\pic_report_non_nominal'.$date.'.pdf';
        
        $my_server = "LOCAL"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";

        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;

        $creport->ParameterFields(2)->SetCurrentValue ("$new_date_start"); 
        $creport->ParameterFields(3)->SetCurrentValue ("$new_date_end"); 

        // pass parameter record selection formula
        $creport->RecordSelectionFormula = "{report_customer_type_brand.invoice_date}>=#$start#AND{report_customer_type_brand.invoice_date}<=#$end#";

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
 
        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\export\\pic_report_non_nominal'.$date.'.pdf';
 
        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function print_officer(Request $request)
    {
        $start = $request->all()['start'];
        $end = $request->all()['end'];
        $nominal = $request->input('nominal');
        $date = date("Y-m");

        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        if($nominal == 1){
            $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\officer_report_nominal.rpt";
            $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\export\\officer_report_nominal-'.$date.'.pdf';
            
            $my_server = "LOCAL"; 
            $my_user = "root"; 
            $my_password = ""; 
            $my_database = "ppi-dist";
            $COM_Object = "CrystalDesignRunTime.Application";
    
            //-Create new COM object-depends on your Crystal Report version
            $crapp= New COM($COM_Object) or die("Unable to Create Object");
            $creport = $crapp->OpenReport($my_report,1); // call rpt report
    
            //- Set database logon info - must have
            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);
    
            //- field prompt or else report will hang - to get through
            $creport->EnableParameterPrompting = FALSE;
    
            $creport->ParameterFields(2)->SetCurrentValue ("$new_date_start"); 
            $creport->ParameterFields(3)->SetCurrentValue ("$new_date_end"); 
    
            // pass parameter record selection formula
            $creport->RecordSelectionFormula = "{penjualan_so.so_date}>=#$start#AND{penjualan_so.so_date}<=#$end#";
    
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
     
            $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\export\\officer_report_nominal-'.$date.'.pdf';
     
            header("Content-Description: File Transfer"); 
            header("Content-Type: application/octet-stream"); 
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
            ob_clean();
            flush();
            readfile ($file);
            exit();
        }elseif($nominal == 2){
            $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\officer_report_non_nominal.rpt";
            $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\export\\officer_report_non_nominal-'.$date.'.pdf';
            
            $my_server = "LOCAL"; 
            $my_user = "root"; 
            $my_password = ""; 
            $my_database = "ppi-dist";
            $COM_Object = "CrystalDesignRunTime.Application";
    
            //-Create new COM object-depends on your Crystal Report version
            $crapp= New COM($COM_Object) or die("Unable to Create Object");
            $creport = $crapp->OpenReport($my_report,1); // call rpt report
    
            //- Set database logon info - must have
            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);
    
            //- field prompt or else report will hang - to get through
            $creport->EnableParameterPrompting = FALSE;
    
            $creport->ParameterFields(2)->SetCurrentValue ("$new_date_start"); 
            $creport->ParameterFields(3)->SetCurrentValue ("$new_date_end"); 
    
            // pass parameter record selection formula
            $creport->RecordSelectionFormula = "{penjualan_so.so_date}>=#$start#AND{penjualan_so.so_date}<=#$end#";
    
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
     
            $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\export\\officer_report_non_nominal-'.$date.'.pdf';
     
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
}
