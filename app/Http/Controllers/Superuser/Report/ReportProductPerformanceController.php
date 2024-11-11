<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\BrandReference;
use App\Entities\Master\Product;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\ProductPack;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Customer;
use App\Entities\Master\Company;
use App\Entities\Master\Warehouse;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\DeliveryOrderMutationItem;
use App\DataTables\Report\ProductPerformanceTable;
use App\Entities\Penjualan\CanvasingItem;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Gudang\StockMove;
use App\Entities\Setting\UserMenu;
use DB;
use Auth;
use PDF;
use COM;

class ReportProductPerformanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.report.product_performance.";
        $this->route = "superuser.report.product_performance";
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

    public function json(Request $request, ProductPerformanceTable $datatable)
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

        $product = ProductPack::get();
        $brand = BrandLokal::get();

        $data = [
            'product' => $product,
            'brand' => $brand,
        ];
        return view($this->view."index",$data);
    }

    public function print_report(Request $request)
    {
        $start = $request->all()['start'];
        $end = $request->all()['end'];
        $product = $request->all()['product'];
        $date = date("Y-m");
        $status_do = 6;

        // dd($customer);

        // Convert date
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        // dd($customer);

        $sqlStyle = "";
        $i = 1;
        foreach ($product as $key => $value) {
            if ($i > 1) {
                $sqlStyle .= " OR ";
            }

            if (is_array($value)) {
                $sec = array();
                foreach ($value as $second_level) {
                    $sec[] = "{master_products_packaging.id}='$second_level'";
                }
                $sqlStyle .= "(" . implode(' AND ', $sec) . ")";
            } else {
                $sqlStyle .= "{master_products_packaging.id}='$value'";
            }
            $i++;
        }

        // dd($sqlStyle);

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\product_order\\product_order.rpt";
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\product_order\\export\\product-order-'.$date.'.pdf';

        $my_server = "SERVER 2"; 
        $my_user = "dev_denki"; 
        $my_password = "Denki@05121996"; 
        $my_database = "ppi-araya";
        $COM_Object = "CrystalDesignRunTime.Application";

        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        $creport->EnableParameterPrompting = FALSE;
        $creport->ParameterFields(3)->SetCurrentValue ("$new_date_start"); // <-- param 1
        $creport->ParameterFields(4)->SetCurrentValue ("$new_date_end"); // <-- param 2

        $sqlString = $sqlStyle;
        $creport->RecordSelectionFormula = "($sqlString)AND{penjualan_so.so_date}>=#$start#AND{penjualan_so.so_date}<=#$end#AND{penjualan_do.status}=6";

        $creport->ExportOptions->DiskFileName=$my_pdf; //export to pdf
        $creport->ExportOptions->PDFExportAllPages=true;
        $creport->ExportOptions->DestinationType=1; // export to file
        $creport->ExportOptions->FormatType=31; // PDF type
        $creport->Export(false);

            //------ Release the variables ------
        $creport = null;
        $crapp = null;
        $ObjectFactory = null;

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\product_order\\export\\product-order-'.$date.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
