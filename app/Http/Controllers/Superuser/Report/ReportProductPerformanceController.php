<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\BrandReference;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Product;
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
use Carbon\Carbon;
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
            // 'product' => $product,
            'brand' => $brand,
        ];
        return view($this->view."index",$data);
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
                ->where('master_products.brand_name', $request->brand_id)
                ->get();
            
            return response()->json($products);
        }
    }

    public function print_report(Request $request)
    {
        $request->validate([
            'periode_from' => 'required|date',
            'periode_to' => 'required|date',
            'product' => 'required|array',
            'brand' => 'required|array',
            'type' => 'required|in:1,2', // Validasi tambahan untuk type
        ]);

        $start = $request->input('periode_from');
        $end = $request->input('periode_to');
        $products = $request->input('product');
        $brands = $request->input('brand');
        $type = $request->input('type'); // Ambil nilai dari form
        $date = Carbon::now()->format('Y-m');
        $status_do = 6;

        $new_date_start = Carbon::parse($start)->format('d-m-Y');
        $new_date_end = Carbon::parse($end)->format('d-m-Y');

        $brandSearch = '';
        if (!in_array('all', $brands)) {
            $brandSearch = collect($brands)->map(function ($value) {
                return "{master_products.brand_name}='$value'";
            })->implode(' OR ');
        }

        $productSearch = '';
        if (!in_array('all', $products)) {
            $productSearch = collect($products)->map(function ($value) {
                return "{master_products_packaging.id}='$value'";
            })->implode(' OR ');
        }

        // 📝 Tentukan reportPath sesuai type
        if ($type == 1) {
            $reportPath = public_path('cr/report/operasional/product_order/product_order.rpt'); // Detail
        } else {
            $reportPath = public_path('cr/report/operasional/product_order/product_order_summary.rpt'); // Summary
        }

        $exportPath = public_path("cr/report/operasional/product_order/export/product-order-{$date}.pdf");

        $server = env('DB_SERVER', 'LOCAL_3');
        $user = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $database = env('DB_DATABASE', 'ppi_araya');
        $COM_Object = "CrystalDesignRunTime.Application";

        try {
            if (!class_exists('COM')) {
                throw new \Exception("COM class is not available on this server.");
            }

            $crapp = new COM($COM_Object) or die("Unable to create Crystal Reports Object");
            $creport = $crapp->OpenReport($reportPath, 1);

            $creport->Database->Tables(1)->SetLogOnInfo($server, $database, $user, $password);

            $creport->EnableParameterPrompting = false;
            $creport->ParameterFields(2)->SetCurrentValue($new_date_start);
            $creport->ParameterFields(3)->SetCurrentValue($new_date_end);

            $recordSelectionFormula = [];
            if ($productSearch) {
                $recordSelectionFormula[] = "($productSearch)";
            }
            if ($brandSearch) {
                $recordSelectionFormula[] = "($brandSearch)";
            }
            $recordSelectionFormula[] = "{penjualan_so.so_date}>=#$start#";
            $recordSelectionFormula[] = "{penjualan_so.so_date}<=#$end#";
            $recordSelectionFormula[] = "{penjualan_do.status}=$status_do";

            $creport->RecordSelectionFormula = implode(' AND ', $recordSelectionFormula);

            $creport->ExportOptions->DiskFileName = $exportPath;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = 31;
            $creport->Export(false);

            $creport = null;
            $crapp = null;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }

        if (file_exists($exportPath)) {
            return response()->file($exportPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . basename($exportPath) . '"'
            ]);
        } else {
            return response()->json(['error' => 'Report file not found'], 404);
        }
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
