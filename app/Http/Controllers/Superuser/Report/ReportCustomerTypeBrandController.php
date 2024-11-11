<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Reports\CustomerTypeBrandReports;
Use App\Entities\Penjualan\SalesOrder;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\DB;
use Auth;
use COM;


class ReportCustomerTypeBrandController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.customer_type_brand.";
        $this->route = "superuser.report.customer_type_brand";
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

        $data['data'] = CustomerTypeBrandReports::first();

        return view($this->view."index", $data);
    }

    public function postData(Request $request)
    {
        try {
            DB::table('penjualan_do')
                ->leftJoin('penjualan_do_details', 'penjualan_do_details.do_id', '=', 'penjualan_do.id')
                ->leftJoin('penjualan_do_item', 'penjualan_do_item.do_id', '=', 'penjualan_do.id')
                ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
                ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
                ->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
                ->select(
                    DB::raw('SUM(penjualan_do_item.qty) AS invoice_qty'),
                    'master_customers.id AS customerID',
                    'master_customer_other_addresses.id AS otherAddressID',
                    'master_customer_other_addresses.name AS customer_name',
                    'master_customer_categories.name AS customer_type',
                    'master_customer_other_addresses.text_kota AS customer_kota',
                    'master_customer_other_addresses.text_provinsi AS customer_provinsi',
                    'master_customer_other_addresses.zone AS customer_zone',
                    'penjualan_do.do_code AS invoice_code',
                    'penjualan_so.so_date AS invoice_date',
                    'penjualan_so.brand_name AS invoice_brand',
                    'penjualan_so.type_so AS invoice_type',
                    'penjualan_do_details.purchase_total_idr AS invoice_purchase',
                    'penjualan_do_details.delivery_cost_idr AS invoice_delivery_order_cost',
                    'penjualan_do_details.discount_idr AS discount_idr'
                )
                ->where('penjualan_do.status', 6)
                ->where(function ($query) {
                    $query->where('master_customers.status', 1)
                        ->orWhere('master_customers.existence', 1);
                })
                ->groupBy('penjualan_do.do_code')
                ->orderBy('penjualan_do.do_code')
                ->chunk(100, function ($results) {
                    foreach ($results as $row) {
                        // Define the attributes to find or create
                        $attributes = [
                            // 'customer_id' => $row->customerID,
                            // 'other_address_id' => $row->otherAddressID,
                            'invoice_code' => $row->invoice_code,
                        ];

                        // Define the values to be set or updated
                        $values = [
                            'customer_name' => $row->customer_name,
                            'customer_type' => $row->customer_type,
                            'customer_kota' => $row->customer_kota,
                            'customer_provinsi' => $row->customer_provinsi,
                            'customer_zone' => $row->customer_zone,
                            'invoice_date' => $row->invoice_date,
                            'invoice_brand' => $row->invoice_brand,
                            'invoice_type' => $row->invoice_type,
                            'invoice_qty' => $row->invoice_qty ?? 0,
                            'invoice_purchase' => ($row->invoice_purchase ?? 0),
                            'invoice_delivery_order_cost' => $row->invoice_delivery_order_cost ?? 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        // Find the record or create a new one
                        $report = CustomerTypeBrandReports::firstOrNew($attributes);

                        // Set or update the additional values
                        $report->fill($values);

                        // Save the record to the database
                        $report->save();
                    }
                });

            return redirect()->back()->with('message', 'Berhasil Sync data!');
        } catch (\Exception $e) {
            Log::error('Sync data failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function print_report(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');
        $type = $request->input('type');
        $date = date("Y-m");
        $so_type = "nonppn";

        // Convert date
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        if ($type == 1) {
            $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_customer_register\\customer_type_brand.rpt";
            $my_excel = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_customer_register\\export\\customer-type-brand-'.$date.'.xlsx';

            // Server Information
            $my_server = "LOCAL";
            $my_user = "root";
            $my_password = "";
            $my_database = "ppi-dist";
            $COM_Object = "CrystalDesignRunTime.Application";

            $crapp = new COM($COM_Object) or die("Unable to Create Object");
            $creport = $crapp->OpenReport($my_report, 1);

            // Set database logon info
            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

            // Disable parameter prompting
            $creport->EnableParameterPrompting = FALSE;

            // Set parameters
            $creport->ParameterFields(2)->SetCurrentValue($new_date_start);
            $creport->ParameterFields(3)->SetCurrentValue($new_date_end);

            // Record selection formula
            $creport->RecordSelectionFormula = "{report_customer_type_brand.invoice_date} >= #$start# AND {report_customer_type_brand.invoice_date} <= #$end#";

            // Export to Excel process
            $creport->ExportOptions->DiskFileName = $my_excel; // Export to Excel
            $creport->ExportOptions->ExcelUseConstantColumnWidth = true;
            $creport->ExportOptions->DestinationType = 1; // Export to file
            $creport->ExportOptions->FormatType = 36; // Excel format (36 for Excel 97-2003, 51 for Excel 2007+)
            $creport->Export(false);

            // Release the variables
            $creport = null;
            $crapp = null;
            $ObjectFactory = null;

            $file = $my_excel;

            header("Content-Description: File Transfer");
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
            ob_clean();
            flush();
            readfile($file);
            exit();
        } elseif ($type == 2) {
            $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_customer_register\\report_zone_customer.rpt";
            $my_excel = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\management\\report_customer_register\\export\\customer-zone-'.$date.'.xlsx';

            $my_server = "LOCAL";
            $my_user = "root";
            $my_password = "";
            $my_database = "ppi-dist";
            $COM_Object = "CrystalDesignRunTime.Application";

            $crapp = new COM($COM_Object) or die("Unable to Create Object");
            $creport = $crapp->OpenReport($my_report, 1);

            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

            $creport->EnableParameterPrompting = FALSE;

            $creport->ParameterFields(2)->SetCurrentValue($new_date_start);
            $creport->ParameterFields(3)->SetCurrentValue($new_date_end);

            $creport->RecordSelectionFormula = "{report_customer_type_brand.invoice_type} = '$so_type' AND {report_customer_type_brand.invoice_date} >= #$start# AND {report_customer_type_brand.invoice_date} <= #$end#";

            $creport->ExportOptions->DiskFileName = $my_excel;
            $creport->ExportOptions->ExcelUseConstantColumnWidth = true;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = 36; // Excel format
            $creport->Export(false);

            $creport = null;
            $crapp = null;
            $ObjectFactory = null;

            $file = $my_excel;

            header("Content-Description: File Transfer");
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
            ob_clean();
            flush();
            readfile($file);
            exit();
        }
    }

    public function removeDt(Request $request)
    {
        DB::table('report_customer_type_brand')->truncate();

        return redirect()->back()->with('message', 'Berhasil remove data!');
    }
}