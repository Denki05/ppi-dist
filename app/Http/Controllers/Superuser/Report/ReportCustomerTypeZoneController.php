<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Reports\CustomerZoneReport;
Use App\Entities\Penjualan\SalesOrder;
use App\Entities\Setting\UserMenu;
use Auth;
use COM;
use DB;


class ReportCustomerTypeZoneController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.customer_type_zone.";
        $this->route = "superuser.report.customer_type_zone";
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

        // $data['data'] = CustomerTypeBrandReports::first();

        return view($this->view."index");
    }

    public function postData(Request $request)
    {
        $start_date = '2024-01-01';
        $end_date = '2024-04-30';

        $new_date_start = date('Y-m-d', strtotime($start_date));
        $new_date_end = date('Y-m-d', strtotime($end_date));

        $results = DB::table('master_customer_other_addresses')
            // ->whereBetween('penjualan_so.so_date', [$new_date_start, $new_date_end])
            // ->whereNotIn('master_customer_categories.name', ['Industri kosmetik (PPN)', 'Industri pkrt (PPN)'])
            ->where('master_customer_other_addresses.situation', 1)
            ->orWhere('master_customer_other_addresses.status_key', 1)
            ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
            ->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
            ->leftJoin('penjualan_so', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('penjualan_do', 'penjualan_do.so_id', '=', 'penjualan_so.id')
            ->leftJoin('penjualan_do_details', 'penjualan_do_details.do_id', '=', 'penjualan_do.id')
            ->leftJoin('penjualan_do_item', 'penjualan_do_item.do_id', '=', 'penjualan_do.id')
            ->selectRaw('
                master_customer_other_addresses.id AS id,
                master_customer_other_addresses.name AS customer_name,
                master_customer_other_addresses.text_kota AS customer_kota, 
                master_customer_other_addresses.text_provinsi AS customer_provinsi,
                master_customer_other_addresses.zone AS customer_zone, 
                master_customer_categories.name AS customer_type,
                (
                    CASE 
                        WHEN penjualan_so.brand_name = "GCF" THEN IFNULL(SUM(penjualan_do_item.qty), 0)
                        WHEN penjualan_so.brand_name = "Senses" THEN IFNULL(SUM(penjualan_do_item.qty), 0)
                        WHEN penjualan_so.brand_name = "PPI FF" THEN IFNULL(SUM(penjualan_do_item.qty), 0)
                        WHEN penjualan_so.brand_name = "PPI NON FF" THEN IFNULL(SUM(penjualan_do_item.qty), 0)
                        WHEN penjualan_so.brand_name = "PPI X" THEN IFNULL(SUM(penjualan_do_item.qty), 0)
                    END
                ) AS invoice_qty,
                penjualan_so.code AS invoice_code,
                penjualan_so.so_date AS invoice_date,
                penjualan_so.brand_name AS invoice_brand,
                penjualan_so.type_so AS invoice_type,
                penjualan_do_details.purchase_total_idr AS invoice_purchase,
                penjualan_do_details.delivery_cost_idr AS invoice_delivery_order_cost
            ')
            ->groupBy('master_customer_other_addresses.name', 'penjualan_so.code') // Remove or adjust grouping according to your requirements
            ->get();
        
        // dd($results);
        
        foreach($results AS $row){
            $generate_data = CustomerZoneReport::firstOrNew([
                'invoice_code' => $row->invoice_code,
                'customer_name' => $row->customer_name,
            ]);

            $generate_data->customer_other_address_id = $row->id;
            $generate_data->customer_name = $row->customer_name;
            $generate_data->customer_type = $row->customer_type;
            $generate_data->customer_kota = $row->customer_kota;
            $generate_data->customer_provinsi = $row->customer_provinsi;
            $generate_data->customer_zone = $row->customer_zone;
            $generate_data->invoice_code = $row->invoice_code;
            $generate_data->invoice_date = $row->invoice_date;
            $generate_data->invoice_brand = $row->invoice_brand;
            $generate_data->invoice_type = $row->invoice_type;
            $generate_data->invoice_qty = $row->invoice_qty;
            $generate_data->invoice_purchase = $row->invoice_purchase;
            $generate_data->invoice_delivery_order_cost = $row->invoice_delivery_order_cost;
            $generate_data->save();
        }

        return redirect()->back()->with('message', 'Berhasil Sync data!');
        // return redirect()->route('success')->with('success', 'Berhasil Sync data!');
    }

    public function removeDt(Request $request)
    {
        $results = DB::table('report_customer_zone')->truncate();

        return redirect()->back()->with('message', 'Berhasil remove data!');
        // return redirect()->route('success')->with('success', 'Berhasil remove data!');
    }

    public function print_report(Request $request)
    {
        // range date
        $start = $request->all()['period_from'];
        $end = $request->all()['period_to'];
        $date = date("Y-m");

        // Convert date
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));
        
        $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\customer_zone\\report_zone_customer_v3.rpt";
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\customer_zone\\export\\customer-type-zone-'.$date.'.pdf';

        //- Variables - Server Information 
        $my_server = "LOCAL_3"; 
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

        // $creport->ParameterFields(1)->SetCurrentValue ("$new_date_start"); // <-- param 2
        // $creport->ParameterFields(2)->SetCurrentValue ("$new_date_end"); // <-- param 2
        $creport->ParameterFields(2)->SetCurrentValue($new_date_start);
        $creport->ParameterFields(3)->SetCurrentValue($new_date_end);

        // pass parameter record selection formula
        $creport->RecordSelectionFormula = "ISNULL({report_customer_zone.invoice_date})OR{report_customer_zone.invoice_date}>=#$start#AND{report_customer_zone.invoice_date}<=#$end#";

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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\customer_zone\\export\\customer-type-zone-'.$date.'.pdf';

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