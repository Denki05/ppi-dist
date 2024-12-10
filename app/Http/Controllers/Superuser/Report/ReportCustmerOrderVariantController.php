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
        // $data['product'] = ProductPack::get();
        $data['brand'] = BrandLokal::get();

        return view($this->view."index", $data);
    }

    public function getProductsByBrand(Request $request)
    {
        if ($request->ajax()) {
            $products = ProductPack::leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
                ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                ->select(
                    'master_products_packaging.id as product_id', 
                    'master_products_packaging.code as product_code', 
                    'master_products_packaging.name as product_name', 
                    'master_packaging.pack_name as product_kemasan'
                )
                ->where('master_products.brand_name', $request->brand_name)
                ->get();
            
            return response()->json($products);
        }
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

        // Build customer search string with "All" handling
        $customerSearch = empty($customers) || in_array('all', $customers)
            ? '1=1' // Select all customers
            : collect($customers)->map(function($value) {
                return "{master_customer_other_addresses.id}='$value'";
            })->implode(' OR ');

        // Build brand search string with "All" handling
        $brandSearch = empty($brands) || in_array('all', $brands)
            ? '1=1' // Select all brands
            : collect($brands)->map(function($value) {
                return "{penjualan_so.brand_name}='$value'";
            })->implode(' OR ');

        // Build product search string with "All" handling
        $productSearch = empty($product) || in_array('all', $product)
            ? '1=1' // Select all products
            : collect($product)->map(function($value) {
                return "{master_products_packaging.id}='$value'";
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

            // Combine the search strings into the record selection formula
            $creport->RecordSelectionFormula = "($customerSearch) AND {penjualan_so.so_date}>=#$start# AND {penjualan_so.so_date}<=#$end# AND ($brandSearch)" 
                                            . (!empty($productSearch) && $productSearch !== '1=1' ? " AND ($productSearch)" : "");

            $creport->ExportOptions->DiskFileName = $my_pdf; // export to pdf
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1; // export to file
            $creport->ExportOptions->FormatType = 31; // PDF type
            $creport->Export(false);

            // Release the variables
            $creport = null;
            $crapp = null;

            $file = $my_pdf;

            return response()->download($file);

        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('Failed to generate report: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate report. Please try again later.'], 500);
        }
    }
}