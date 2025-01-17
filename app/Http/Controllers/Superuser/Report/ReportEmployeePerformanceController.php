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

    public function print_report(Request $request)
    {
        $validatedData = $request->validate([
            'period_from' => 'required|date', // Adjusted field names
            'period_to' => 'required|date',
            'nominal' => 'required|integer|in:1,2',
            'type' => 'required|integer|in:1,2',
        ]);

        // Extract validated data
        $start = $validatedData['period_from'];
        $end = $validatedData['period_to'];
        $nominal = $validatedData['nominal'];
        $type = $validatedData['type'];
        $date = date("Y-m");

        // Format dates
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        // Set report paths dynamically (consider moving paths to a config file)
        $basePath = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_employee_performance\\";
        if ($type == 1) {
            if ($nominal == 1) {
                $my_report = "{$basePath}pic_report_nominal.rpt";
                $my_pdf = "{$basePath}export\\pic_report_nominal{$date}.pdf";
            } else {
                $my_report = "{$basePath}pic_report_non_nominal.rpt";
                $my_pdf = "{$basePath}export\\pic_report_non_nominal{$date}.pdf";
            }
        } elseif ($type == 2) {
            if ($nominal == 1) {
                $my_report = "{$basePath}officer_report_nominal_3.rpt";
                $my_pdf = "{$basePath}export\\officer_report_nominal-{$date}.pdf";
            } else {
                $my_report = "{$basePath}officer_report_non_nominal.rpt";
                $my_pdf = "{$basePath}export\\officer_report_non_nominal-{$date}.pdf";
            }
        }

        // Generate the report
        return $this->generateReport($my_report, $my_pdf, $new_date_start, $new_date_end, $start, $end, $type);
    }

    private function generateReport($reportPath, $pdfPath, $newDateStart, $newDateEnd, $start, $end, $type)
    {
        $my_server = "LOCAL"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi_araya";
        $COM_Object = "CrystalDesignRunTime.Application";

        try {
            // Create new COM object
            $crapp = new COM($COM_Object) or die("Unable to Create Object");
            $creport = $crapp->OpenReport($reportPath, 1); // call rpt report

            // Set database logon info
            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

            // Disable parameter prompting
            $creport->EnableParameterPrompting = false;

            // Set parameter values
            $creport->ParameterFields(2)->SetCurrentValue($newDateStart); 
            $creport->ParameterFields(3)->SetCurrentValue($newDateEnd); 

            // Set record selection formula
            if($type == 1){
                $creport->RecordSelectionFormula = "{report_customer_type_brand.invoice_date}>=#$start#AND{report_customer_type_brand.invoice_date}<=#$end#";
            } else {
                $creport->RecordSelectionFormula = "{Command.invoice_date}>=#$start#AND{Command.invoice_date}<=#$end#AND{Command.status}=6";
            }
        
            // Export to PDF
            $creport->ExportOptions->DiskFileName = $pdfPath; // Export to pdf
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1; // Export to file
            $creport->ExportOptions->FormatType = 31; // PDF type
            $creport->Export(false);

            // Release the COM objects
            $creport = null;
            $crapp = null;

            // Prepare the file for download
            header("Content-Description: File Transfer"); 
            header("Content-Type: application/octet-stream"); 
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-Disposition: attachment; filename=\"" . basename($pdfPath) . "\""); 
            ob_clean();
            flush();
            readfile($pdfPath);
            exit();
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }
}
