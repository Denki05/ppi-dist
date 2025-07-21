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
use COM;
use DB;
use PDF;
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
        try {
            // Validate and sanitize inputs
            $start = $request->input('start');
            $end = $request->input('end');
            $customer = $request->input('customer');
            // if (!$start || !$end || !is_array($customer)) {
            //     throw new Exception('Invalid input.');
            // }

            $date = date("Y-m");

            // Convert date to the desired format
            $new_date_start = date('d-m-Y', strtotime($start));
            $new_date_end = date('d-m-Y', strtotime($end));

            // Build SQL-style condition for customers
            $sqlStyle = $this->buildSqlStyleCondition($customer);

            $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\omset_penjualan\\omset_penjualan.rpt";
            $my_pdf = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\omset_penjualan\\export\\Omset-Penjualan-$date.pdf";

            $my_server = "LOCAL";
            $my_user = "root";
            $my_password = "";
            $my_database = "ppi_araya";
            $COM_Object = "CrystalDesignRunTime.Application";

            // Create COM object for Crystal Reports
            $crapp = new COM($COM_Object);
            $creport = $crapp->OpenReport($my_report, 1);

            // Set database connection info
            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

            // Disable parameter prompting
            $creport->EnableParameterPrompting = false;
            $creport->ParameterFields(2)->SetCurrentValue($new_date_start); // <-- param 1
            $creport->ParameterFields(3)->SetCurrentValue($new_date_end);   // <-- param 2

            // Set record selection formula based on customer selection
            if ($sqlStyle == "{master_customer_other_addresses.id}='all'") {
                $creport->RecordSelectionFormula = "{penjualan_so.so_date}>=#$start#AND{penjualan_so.so_date}<=#$end#AND{penjualan_so.status}=4";
            } else {
                $creport->RecordSelectionFormula = "($sqlStyle)AND{penjualan_so.so_date}>=#$start#AND{penjualan_so.so_date}<=#$end#AND{penjualan_so.status}=4";
            }

            // Export to PDF
            $creport->ExportOptions->DiskFileName = $my_pdf;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1; // Export to file
            $creport->ExportOptions->FormatType = 31; // PDF type
            $creport->Export(false);

            // Release COM objects
            $creport = null;
            $crapp = null;

            // Download the generated PDF
            $this->downloadFile($my_pdf);

        } catch (Exception $e) {
            // Handle exceptions gracefully
            die("Error: " . $e->getMessage());
        }
    }

    private function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date ? $date : false;
    }

    private function buildSqlStyleCondition($customer)
    {
        $sqlStyle = "";
        $i = 1;
        foreach ($customer as $key => $value) {
            if ($i > 1) {
                $sqlStyle .= " OR ";
            }

            if (is_array($value)) {
                $sec = array();
                foreach ($value as $second_level) {
                    $sec[] = "{master_customer_other_addresses.id}='" . addslashes($second_level) . "'";
                }
                $sqlStyle .= "(" . implode(' AND ', $sec) . ")";
            } else {
                $sqlStyle .= "{master_customer_other_addresses.id}='" . addslashes($value) . "'";
            }
            $i++;
        }
        return $sqlStyle;
    }

    private function downloadFile($file)
    {
        if (file_exists($file)) {
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
            ob_clean();
            flush();
            readfile($file);
            exit();
        } else {
            throw new Exception("File not found.");
        }
    }

}
