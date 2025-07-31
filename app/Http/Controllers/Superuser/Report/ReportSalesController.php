<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Finance\Invoicing;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Master\Sales;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Company;
use App\DataTables\Report\SalesReportTable;
use App\Entities\Setting\UserMenu;
use Rap2hpoutre\FastExcel\FastExcel;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use \Carbon\Carbon;
use Auth;
use PDF;
use DB;
use COM;
use Validator;

class ReportSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.report.sales.";
        $this->route = "superuser.report.sales";
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

    public function json(Request $request, SalesReportTable $datatable)
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

        $customer = CustomerOtherAddress::get();
        $brand = DB::table('master_brand_lokal')->select('brand_name')->get();

        $data = [
            'customer' => $customer,
            'brand' => $brand,
        ];

        return view($this->view."index", $data);
    }
    
    private function reportsGenerator($model)
    {
        $datas = $model->cursor();
        foreach ($datas as $data) {
            yield [
                'SO Date' => Carbon::parse($data->so_date)->format('d/m/Y H:i'),
                'SO Number' => $data->so_code,
                'Invoice Number' => $data->invoice_number,
                'Total' => $data->total,
                'Payment' => $data->payment,
                'Sales Senior' => $data->sales_senior(),
                'Sales' => $data->sales(),
            ];
        }

        yield [
            'SO Date' => '',
            'SO Number' => '',
            'Invoice Number' => '',
            'Total' => 'Total',
            'Sales Senior' => '',
            'Sales' => '',
        ];
    }

    private function excel(Request $request)
    {
        $datatable = new SalesReportTable();
        $model = $datatable->query($request);

        // $list = \DB::select($model->toSql(), $model->getBindings());

        $filename = 'SR-' . Carbon::parse($request->start_date)->format('dmy') . '-' . Carbon::parse($request->end_date)->format('dmy') . '.xlsx';
        $header_style = (new StyleBuilder())->setFontSize(11)->setFontBold()->build();

        $rows_style = (new StyleBuilder())
            ->setFontSize(11)
            ->build();

        return (new FastExcel($this->reportsGenerator($model)))->headerStyle($header_style)
            ->rowsStyle($rows_style)->download($filename);
    }

    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer' => 'required',
            'datesearch' => 'required',
            'download_type' => 'required',
        ]);

        if ($validator->fails()) {
            abort(404);
        }

        $split = explode('-', str_replace(' ', '', $request->datesearch));
        $from_date = Carbon::createFromFormat('d/m/Y', $split[0])->format('Y-m-d');
        $to_date = Carbon::createFromFormat('d/m/Y', $split[1])->format('Y-m-d');

        // ADD request date to use in datatable query
        $request->request->add(['start_date' => $from_date, 'end_date' => $to_date]);
        if ($request->download_type == 'excel') {
            return $this->excel($request);
        }

        if ($request->download_type == 'pdf') {
            return $this->pdf($request);
        }
    }

    public function print_report(Request $request)
    {
        $start = $request->all()['start'];
        $end = $request->all()['end'];
        $customer = $request->all()['customer'];
        $date = date("Y-m");

        // Convert date
        $new_date_start = date('d-m-Y', strtotime($start));
        $new_date_end = date('d-m-Y', strtotime($end));

        $sqlStyle = "";
        $i = 1;
        foreach ($customer as $key => $value) {
            if ($i > 1) {
                $sqlStyle .= " OR ";
            }

            if (is_array($value)) {
                $sec = array();
                foreach ($value as $second_level) {
                    $sec[] = "{master_customer_other_addresses.id}='$second_level'";
                }
                $sqlStyle .= "(" . implode(' AND ', $sec) . ")";
            } else {
                $sqlStyle .= "{master_customer_other_addresses.id}='$value'";
            }
            $i++;
        }

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\omset_penjualan\\omset_penjualan.rpt";
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\omset_penjualan\\export\\Omset-Penjualan-'.$date.'.pdf';

        $my_server = "SERVER 2"; 
        $my_user = "dev_denki"; 
        $my_password = "Denki@05121996"; 
        $my_database = "ppi_araya";
        $COM_Object = "CrystalDesignRunTime.Application";

        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        $creport->EnableParameterPrompting = FALSE;
        $creport->ParameterFields(2)->SetCurrentValue ("$new_date_start"); // <-- param 1
        $creport->ParameterFields(3)->SetCurrentValue ("$new_date_end"); // <-- param 2

        $sqlString = $sqlStyle;
        $creport->RecordSelectionFormula = "($sqlString)AND{penjualan_so.so_date}>=#$start#AND{penjualan_so.so_date}<=#$end#AND{penjualan_so.status}=4";

        $creport->ExportOptions->DiskFileName=$my_pdf; //export to pdf
        $creport->ExportOptions->PDFExportAllPages=true;
        $creport->ExportOptions->DestinationType=1; // export to file
        $creport->ExportOptions->FormatType=31; // PDF type
        $creport->Export(false);

            //------ Release the variables ------
        $creport = null;
        $crapp = null;
        $ObjectFactory = null;

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\omset_penjualan\\export\\Omset-Penjualan-'.$date.'.pdf';

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
