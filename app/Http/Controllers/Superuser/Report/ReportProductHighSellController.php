<?php

namespace App\Http\Controllers\Superuser\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Setting\UserMenu;
use App\Entities\Master\BrandLokal;
use App\DataTables\Report\ProductHighSaleTable;
use DB;
use Auth;
use PDF;
use COM;

class ReportProductHighSellController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.product_high_sell.";
        $this->route = "superuser.report.product_high_sell";
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

    public function json(Request $request, ProductHighSaleTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['brand'] = BrandLokal::get();

        return view($this->view."index", $data);
    }

    public function print_report(Request $request)
    {
        $validatedData = $request->validate([
            'periode_from' => 'required|date',
            'periode_to' => 'required|date',
            'brand_name' => 'required|array',
            'brand_name.*' => 'string',
            'type' => 'required|integer|in:1,2',
        ]);

        $start = $validatedData['periode_from'];
        $end = $validatedData['periode_to'];
        $brands = $validatedData['brand_name'];
        $type = $validatedData['type'];
        $date = date("Y-m");

        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        $reportPath = public_path('cr/report/operasional/product_high_sell/');
        $exportPath = public_path('cr/report/operasional/product_high_sell/export/');

        $sqlStyle = $this->constructSqlStyle($brands);

        if ($type == 1) {
            $reportName = "report_high_sell_semester.rpt";
            $pdfName = "High-Sell-Semester-" . $date . ".pdf";
        } else {
            $reportName = "report_high_sell_zona.rpt";
            $pdfName = "High-Sell-Zona-" . $date . ".pdf";
        }

        $my_report = $reportPath . $reportName;
        $my_pdf = $exportPath . $pdfName;

        if (!file_exists($my_report)) {
            return response()->json(['error' => 'Report file not found'], 404);
        }

        try {
            $crapp = new COM("CrystalDesignRunTime.Application");
            $creport = $crapp->OpenReport($my_report, 1);

            $this->setDatabaseLogon($creport);
            $creport->EnableParameterPrompting = false;
            $creport->ParameterFields(2)->SetCurrentValue($new_date_start);
            $creport->ParameterFields(3)->SetCurrentValue($new_date_end);

            $sqlString = $sqlStyle;
            $creport->RecordSelectionFormula = "($sqlString) AND {penjualan_so.so_date} >= #$start# AND {penjualan_so.so_date} <= #$end# AND {penjualan_so.status} = 4";

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
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    private function constructSqlStyle($brands)
    {
        $sqlStyle = "";
        $i = 1;
        foreach ($brands as $value) {
            if ($i > 1) {
                $sqlStyle .= " OR ";
            }
            $sqlStyle .= "{master_products.brand_name}='" . addslashes($value) . "'";
            $i++;
        }
        return $sqlStyle;
    }

    private function setDatabaseLogon($creport)
    {
        $my_server = "LOCAL";
        $my_user = "root";
        $my_password = "";
        $my_database = "ppi_araya";

        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);
    }
}
