<?php

namespace App\Http\Controllers\Superuser\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Setting\UserMenu;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Packaging;
use App\Entities\Master\ProductPack;
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
        $data['kemasan'] = Packaging::orderBy('pack_name')->get();
        $data['product'] = ProductPack::with(['packaging', 'product'])
            ->leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->select('master_products_packaging.*', 'master_products.brand_name AS brand_name')
            ->orderBy('master_products.brand_name')
            ->orderBy('master_products_packaging.name')
            ->get();

        return view($this->view."index", $data);
    }

    public function print_report(Request $request)
    {
        // Form blade mengirim: periode_from, periode_to, brand_name[], kemasan[], product[], type
        // Tetap dukung penamaan lama (start, end, brand) agar backward compatible.
        $start = $request->input('start', $request->input('periode_from'));
        $end = $request->input('end', $request->input('periode_to'));
        $brands = $request->input('brand', $request->input('brand_name', []));
        $kemasan = $request->input('kemasan', []);
        $products = $request->input('product', []);
        $type = $request->input('type');

        $validated = validator(
            ['start' => $start, 'end' => $end, 'brand' => (array) $brands, 'type' => $type],
            [
                'start' => 'required|date',
                'end' => 'required|date',
                'brand' => 'required|array',
                'type' => 'required|integer|in:1,2',
            ]
        )->validate();

        $start = $validated['start'];
        $end = $validated['end'];
        $brands = $validated['brand'];
        $type = $validated['type'];
        // Normalisasi kemasan & produk ke array (boleh kosong / berisi 'all' = tanpa filter)
        $kemasan = is_array($kemasan) ? $kemasan : ($kemasan ? [$kemasan] : []);
        $products = is_array($products) ? $products : ($products ? [$products] : []);
        $date = date("Y-m");

        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        // $reportPath = config('paths.report_path') . "operasional\\product_high_sell\\";
        // $exportPath = $reportPath . "export\\";

        $reportPath = public_path('cr/report/operasional/product_high_sell/');
        $exportPath = public_path('cr/report/operasional/product_high_sell/export/');

        $brandFormula = $this->constructBrandFormula($brands);
        $kemasanFormula = $this->constructKemasanFormula($kemasan);
        $productFormula = $this->constructProductFormula($products);

        if ($type == 1) {
            $reportName = "report_high_sell_semester.rpt";
            $pdfName = "High-Sell-Semester-" . $date . ".pdf";
        } elseif ($type == 2) {
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

            // Samakan dengan preview web (ProductHighSaleTable):
            // status=4, periode so_date, filter brand + kemasan + produk.
            // Jika filter berisi 'all'/kosong maka tidak membatasi (agar sama dengan preview).
            $filters = array_filter([$brandFormula, $kemasanFormula, $productFormula]);
            $filterString = empty($filters) ? "TRUE" : "(" . implode(")AND(", $filters) . ")";
            $creport->RecordSelectionFormula = "($filterString)AND{penjualan_so.so_date}>=#$start#AND{penjualan_so.so_date}<=#$end#AND{penjualan_so.status}=4";

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
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bangun potongan RecordSelectionFormula untuk brand.
     * 'all'/kosong => return "" (tanpa filter) agar sama dengan preview web.
     */
    private function constructBrandFormula($brands)
    {
        $brands = array_filter((array) $brands, function ($v) { return $v !== '' && $v !== null; });
        if (empty($brands) || in_array('all', $brands)) {
            return "";
        }
        return $this->constructSqlStyle($brands, "{master_products.brand_name}");
    }

    private function constructKemasanFormula($kemasan)
    {
        $kemasan = array_filter((array) $kemasan, function ($v) { return $v !== '' && $v !== null; });
        if (empty($kemasan) || in_array('all', $kemasan)) {
            return "";
        }
        return $this->constructSqlStyle($kemasan, "{master_packaging.pack_name}");
    }

    private function constructProductFormula($products)
    {
        $products = array_filter((array) $products, function ($v) { return $v !== '' && $v !== null; });
        if (empty($products) || in_array('all', $products)) {
            return "";
        }
        return $this->constructSqlStyle($products, "{master_products_packaging.id}");
    }

    private function constructSqlStyle($values, $field)
    {
        $sqlStyle = "";
        $i = 1;
        foreach ((array) $values as $value) {
            if ($i > 1) {
                $sqlStyle .= " OR ";
            }

            if (is_array($value)) {
                $sec = array();
                foreach ($value as $second_level) {
                    $sec[] = $field . "='" . str_replace("'", "''", $second_level) . "'";
                }
                $sqlStyle .= "(" . implode(' AND ', $sec) . ")";
            } else {
                $sqlStyle .= $field . "='" . str_replace("'", "''", $value) . "'";
            }
            $i++;
        }

        return $sqlStyle === "" ? "" : "($sqlStyle)";
    }

    private function setDatabaseLogon($creport)
    {
        $my_server = "SERVER 2";
        $my_user = "dev_denki";
        $my_password = "Denki@05121996";
        $my_database = "ppi_araya";

        // Terapkan ke semua tabel di report (dulu hanya Tables(1),
        // sehingga join master_packaging/penjualan_so bisa pakai kredensial kadaluarsa).
        try {
            $count = $creport->Database->Tables->Count;
            for ($i = 1; $i <= $count; $i++) {
                $creport->Database->Tables($i)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);
            }
        } catch (\Exception $e) {
            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);
        }
    }
}
