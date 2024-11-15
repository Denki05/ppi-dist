<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\Report\CustomerOrderVariantTable;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Product;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\ProductPack;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;
use Auth;
use COM;

class ReportCustmerOrderVariantController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.customer_order_variant.";
        $this->route = "superuser.report.customer_order_variant";
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

    public function json(Request $request, CustomerOrderVariantTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['customer'] = CustomerOtherAddress::where('situation', 1)->orWhere('status_key', 1)->get();
        $data['product'] = ProductPack::get();
        $data['brand'] = BrandLokal::get();

        return view($this->view."index", $data);
    }

    public function print_report(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'start' => 'required|date',
            'end' => 'required|date',
            'customer' => 'required|array',
            'brand_name' => 'required|array',
            'product' => 'nullable|array',
            'nominal' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $start = $request->input('start');
        $end = $request->input('end');
        $customers = $request->input('customer');
        $brands = $request->input('brand_name');
        $product = $request->input('product', []);
        $nominal = $request->input('nominal', 0);
        $date = date("Y-m");

        // Convert dates
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        // Build customer search string
        $customerSearch = collect($customers)->map(function($value) {
            if (is_array($value)) {
                return '(' . collect($value)->map(function($second_level) {
                    return "{master_customer_other_addresses.id}='$second_level'";
                })->implode(' AND ') . ')';
            } else {
                return "{master_customer_other_addresses.id}='$value'";
            }
        })->implode(' OR ');

        // Build brand search string
        $brandSearch = collect($brands)->map(function($value) {
            if (is_array($value)) {
                return '(' . collect($value)->map(function($second_level) {
                    return "{penjualan_so.brand_name}='$second_level'";
                })->implode(' AND ') . ')';
            } else {
                return "{penjualan_so.brand_name}='$value'";
            }
        })->implode(' OR ');

        // Build product search string
        $productSearch = collect($product)->map(function($value) {
            if (is_array($value)) {
                return '(' . collect($value)->map(function($second_level) {
                    return "{master_products_packaging.id}='$second_level'";
                })->implode(' AND ') . ')';
            } else {
                return "{master_products_packaging.id}='$value'";
            }
        })->implode(' OR ');

        // File paths
        $basePath = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\customer_order_variant\\";
        $my_report = $nominal == 1 
                    ? $basePath . "customer_order_variant_nominal.rpt"
                    : $basePath . "customer_order_variant.rpt";

        $my_pdf = $basePath . "export\\customer-order-variant-" . ($nominal == 1 ? "nominal-" : "") . $date . ".pdf";
        
        $my_server = "LOCAL_3";
        $my_user = "root";
        $my_password = "";
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";

        try {
            $crapp = new COM($COM_Object) or die("Unable to Create Object");
            $creport = $crapp->OpenReport($my_report, 1); // call rpt report

            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);
            $creport->EnableParameterPrompting = false;
            $creport->ParameterFields(3)->SetCurrentValue($new_date_start);
            $creport->ParameterFields(4)->SetCurrentValue($new_date_end);

            $sqlString1 = $customerSearch;
            $sqlString2 = $brandSearch;
            $sqlString3 = !empty($productSearch) ? "AND($productSearch)" : "";

            $creport->RecordSelectionFormula = "($sqlString1)AND{penjualan_so.so_date}>=#$start#AND{penjualan_so.so_date}<=#$end#AND($sqlString2)$sqlString3";

            $creport->ExportOptions->DiskFileName = $my_pdf; // export to pdf
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1; // export to file
            $creport->ExportOptions->FormatType = 31; // PDF type
            $creport->Export(false);

            // Release the variables
            $creport = null;
            $crapp = null;
            $ObjectFactory = null;

            $file = $my_pdf;

            return response()->download($file);

        } catch (Exception $e) {
            dd($e);
            // Detailed error logging can be added here
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}