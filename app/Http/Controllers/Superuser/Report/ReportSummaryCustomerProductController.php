<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\ProductPack;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use PDF;
use COM;

class ReportSummaryCustomerProductController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.summary_customer_product.";
        $this->route = "superuser.report.summary_customer_product";
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

        $data['customers'] = CustomerOtherAddress::get();
        $data['product'] = ProductPack::get();
        $data['brand'] = BrandLokal::get();

        // return view($this->view."index",$data);
        return view('superuser.report.summary_customer_product.index', $data);
    }

    public function print_report(Request $request)
    {
        // Validasi data yang masuk
        $validator = Validator::make($request->all(), [
            'start' => 'required|date',
            'end' => 'required|date',
            'customer' => 'required|array',
            'product' => 'nullable|array',
            'brand' => 'nullable|array',
            'non_bulan' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $start = $request->input('start');
        $end = $request->input('end');
        $customers = $request->input('customer', []);
        $products = $request->input('product', []);
        $brands = $request->input('brand', []);
        $non_bulan = $request->input('non_bulan', 0);
        

        $date = date("Y-m");

        // Ubah format tanggal
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        // Bangun string pencarian
        $customerSearch = $this->buildSearchString($customers, 'master_customer_other_addresses.id');
        $productSearch = $this->buildSearchString($products, 'master_products_packaging.id');
        $brandSearch = $this->buildSearchString($brands, 'master_brand_lokal.brand_name');

        // Tentukan file laporan berdasarkan checkbox
        $basePath = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\customer_forcasting\\";
        $my_report = $non_bulan == 1 
                    ? $basePath . "customer_forcasting_without_bulan.rpt"
                    : $basePath . "customer_forcasting_bulan.rpt";
        // $my_report = $basePath . "customer_forcasting_bulan.rpt";

        $my_pdf = $basePath . "export/Summary-Customer-Produk-{$date}.pdf";

        try {
            $crapp = new COM("CrystalDesignRunTime.Application");
            if (!$crapp) {
                throw new Exception("Unable to create COM object");
            }
            $creport = $crapp->OpenReport($my_report, 1);
            $creport->Database->Tables(1)->SetLogOnInfo("LOCAL", "ppi_araya", "root", "");
            $creport->EnableParameterPrompting = false;
            $creport->ParameterFields(4)->SetCurrentValue($new_date_start);
            $creport->ParameterFields(5)->SetCurrentValue($new_date_end);

            // Gabungkan semua string pencarian dalam formula seleksi
            $creport->RecordSelectionFormula = "($customerSearch) AND {penjualan_so.so_date}>=#$start# AND {penjualan_so.so_date}<=#$end#"
                . ($productSearch !== '1=1' ? " AND ($productSearch)" : "")
                . ($brandSearch !== '1=1' ? " AND ($brandSearch)" : "");

            $creport->ExportOptions->DiskFileName = $my_pdf;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = 31;
            $creport->Export(false);

            if (!file_exists($my_pdf)) {
                throw new Exception("Exported file not found");
            }

            return response()->download($my_pdf);

        } catch (Exception $e) {
            Log::error('Failed to generate report: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate report. Please try again later.'], 500);
        }
    }

    private function buildSearchString(array $values, string $field): string
    {
        if (empty($values) || in_array('all', $values)) {
            return '1=1';
        }

        return collect($values)->map(function ($value) use ($field) {
            return "{{$field}}='$value'";
        })->implode(' OR ');
    }


}
