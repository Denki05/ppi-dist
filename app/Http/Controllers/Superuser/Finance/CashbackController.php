<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Finance\Cashback;
use App\Entities\Finance\CashbackItem;
use App\Entities\Penjualan\PackingOrder;
use App\DataTables\Finance\CashbackTable;
use App\DataTables\Report\CashbackReportTable;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Setting\UserMenu;
use Carbon\Carbon;
use Validator;
use COM;
use Auth;
use DB;

class CashbackController extends Controller
{
    public function __construct(){
        $this->route = "superuser.finance.cashback";
        $this->view = "superuser.finance.cashback.";
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

    public function json(Request $request, CashbackTable $datatable)
    {
        return $datatable->build($request);
    }

    public function json2(Request $request, CashbackReportTable $datatable)
    {
        return $datatable->build($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Define the range of years for filtering
        $currentYear = date('Y');
        $years = range($currentYear, $currentYear - 2); // Generate a range of 10 years back

        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->endOfYear();
        $customer = CustomerOtherAddress::leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
            ->where('master_customers.status', 1)
            ->select(
                'master_customer_other_addresses.id AS id', 
                'master_customer_other_addresses.name AS name', 
                'master_customer_other_addresses.text_kota AS kota'
            )
            ->get();

            $months = [];
            for ($date = $start; $date <= $end; $date->addMonth()) {
                $months[] = [
                    'id' => $date->format('n'),
                    'monthName' => $date->format('F'),
                ];
            }

            $bulan = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];
        
                // Get the selected month, default to the current month
            $selectedBulan = $request->bulan ?? now()->month;
            $selectedTahun = $request->tahun ?? $currentYear;

        $data = [
            'months' => $months,
            'years' => $years,
            'customer' => $customer,
            'bulan' => $bulan,
            'selectedBulan' => $selectedBulan,
            'selectedTahun' => $selectedTahun,
        ];


        return view($this->view."index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_create == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $invoice = PackingOrder::find($id);

        // dd($invoice);

        $data = [
            'invoice' => $invoice
        ];

        return view($this->view ."create", $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'do_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];
  
                return $this->response(400, $response);
            }

            // get invoice
            $invoice = PackingOrder::find($request->do_id);

            if ($validator->passes()) {
                $cashback = new Cashback;

                $cashback->code = "C".$invoice->do_code;
                $cashback->customer_other_address_id = $invoice->customer_other_address_id;
                $cashback->do_id = $invoice->id;
                $cashback->idr_rate = $request->idr_rate;
                $cashback->note = $request->note;
                $cashback->status = 1;
                $cashback->created_by = Auth::id();

                if($cashback->save()){
                    // update invoice
                    $updateInvoice = PackingOrder::where('id', $cashback->do_id)->update(['cashback_status' => 1]);

                    if($request->product){
                        foreach($request->product as $key => $value){
                            if($request->product[$key]) {

                                $cashback_detail = new CashbackItem;
                                $cashback_detail->cashback_id = $cashback->id;
                                $cashback_detail->product_packaging_id = $request->product[$key];
                                $cashback_detail->price = $request->item_price[$key];
                                $cashback_detail->price_cashback = $request->cashback[$key];
                                $cashback_detail->qty = $request->item_qty[$key];
                                $cashback_detail->subtotal_item_idr = $request->item_purchase_total[$key];
                                $cashback_detail->amount_cashback = $request->item_grand_total[$key];
                                $cashback_detail->save();
                            }
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.finance.cashback.index');
                    return $this->response(200, $response);
                }
            }
        }
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

    public function destroy($id)
    {
        $cashback = Cashback::findOrFail($id);

        if ($cashback->delete()) {
            $updateDo = PackingOrder::where('id', $cashback->do_id)->update(['cashback_status' => 0]);
            $updateCashback = Cashback::where('id', $cashback->id)->update(['status' => 0]);
            return response()->json(['message' => 'Cashback deleted successfully.']);
        } else {
            return response()->json(['message' => 'Failed to delete cashback.'], 500);
        }
    }

    public function get_invoice(Request $request)
    {
        $yearInvoice = $request->input('year_invoice');
        $monthInvoice = $request->input('month_invoice'); // Get the month_invoice value
        $customerName = $request->input('customer_name'); // Get the customer_name value

        $invoice = DB::table('penjualan_do')
            ->leftJoin('master_customer_other_addresses', 'master_customer_other_addresses.id', '=', 'penjualan_do.customer_other_address_id')
            ->where(function($query) use ($yearInvoice, $monthInvoice, $customerName, $request) {
                // Filter by year and month
                if ($yearInvoice && $monthInvoice) {
                    $query->whereYear('penjualan_do.created_at', $yearInvoice)
                        ->whereMonth('penjualan_do.created_at', $monthInvoice)
                        ->where('penjualan_do.cashback_status', 0)
                        ->where('penjualan_do.status', 6);
                }

                // Filter by customer
                if ($customerName) {
                    $query->orWhere(function($subQuery) use ($customerName) {
                        $subQuery->where('master_customer_other_addresses.id', $customerName)
                                ->where('penjualan_do.cashback_status', 0)
                                ->where('penjualan_do.status', 6);
                    });
                }
            })
            ->where(function($query) use ($request) {
                // Search by DO code
                $query->where('penjualan_do.do_code', 'LIKE', $request->input('q', '') . '%');
            })
            ->select(
                'penjualan_do.id AS id', 
                'penjualan_do.do_code AS code', 
                'master_customer_other_addresses.name AS store', 
                'master_customer_other_addresses.text_kota AS city'
            )
            ->get();

        $results = [];

        foreach ($invoice as $row) {
            $results[] = [
                'id' => $row->id,
                'text' => $row->code,
            ];
        }

        return ['results' => $results];
    }


    public function print_invoice_beli($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Cashback::where('id',$id)->first();

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\invoice\\invoice_beli_araya.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\invoice\\export\\'.$result->code.'-BELI'.'.pdf';
       
        //- Variables - Server Information 
        $my_server = "LOCAL"; 
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
        $creport->RecordSelectionFormula = "{finance_cashback.id}= $result->id";


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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\invoice\\export\\'.$result->code.'-BELI'.'.pdf';

        if (file_exists($file)) {
            // Set headers for file download
            header("Content-Description: File Transfer");
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
            header("Content-Transfer-Encoding: binary");
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Pragma: public");
            header("Content-Length: " . filesize($file));

            // Clear the output buffer and read the file
            ob_clean();
            flush();
            readfile($file);

            // Delete the file after download
            unlink($file);

            exit();
        } else {
            return redirect()->route('superuser.index')->with('error', 'File not found.');
        }
    }

    public function print_invoice_jual($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Cashback::where('id',$id)->first();

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\invoice\\invoice_jual_araya.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\invoice\\export\\'.$result->code.'-JUAL'.'.pdf';
       
        //- Variables - Server Information 
        $my_server = "LOCAL"; 
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
        $creport->RecordSelectionFormula = "{finance_cashback.id}= $result->id";


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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\invoice\\export\\'.$result->code.'-JUAL'.'.pdf';

        if (file_exists($file)) {
            // Set headers for file download
            header("Content-Description: File Transfer");
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
            header("Content-Transfer-Encoding: binary");
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Pragma: public");
            header("Content-Length: " . filesize($file));

            // Clear the output buffer and read the file
            ob_clean();
            flush();
            readfile($file);

            // Delete the file after download
            unlink($file);

            exit();
        } else {
            return redirect()->route('superuser.index')->with('error', 'File not found.');
        }
    }

    public function pageReport(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // $currentDate = Carbon::now();
        $customer = CustomerOtherAddress::get();

        $data = [
            'customer' => $customer
        ];
        // return view('superuser.finance.invoicing.index' ,$data);
        return view($this->view."report",$data);
    }

    public function pageReportBeli(Request $request)
    {
        // Access control
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Default to current month and year if no filters are provided
        $month = $request->get('month', date('m')); // Default to current month
        $year = $request->get('year', date('Y'));   // Default to current year

        // Query the cashback data
        $query = DB::table('finance_cashback AS fc')
            ->leftJoin('master_customer_other_addresses AS mcoa', 'fc.customer_other_address_id', '=', 'mcoa.id')
            ->leftJoin('penjualan_do AS pdo', 'fc.do_id', '=', 'pdo.id')
            ->leftJoin('penjualan_so AS pso', 'pdo.so_id', '=', 'pso.id')
            ->leftJoin('finance_cashback_detail AS fcd', 'fc.id', '=', 'fcd.cashback_id')
            ->select(
                'pso.so_date AS date', 
                'fc.code AS code', 
                'mcoa.name AS customer_name', 
                'mcoa.text_kota AS customer_kota', 
                DB::raw('SUM(fcd.amount_cashback) AS total')
            )
            ->where('fc.status', 1)
            ->groupBy('fc.id', 'fc.code', 'pso.so_date', 'mcoa.name', 'mcoa.text_kota');

        // Apply filters BEFORE calling get()
        if ($month) {
            $query->whereMonth('pso.so_date', $month);
        }
        if ($year) {
            $query->whereYear('pso.so_date', $year);
        }

        // Execute the query
        $data['cashback'] = $query->get();

        return view($this->view . "beli", $data);
    }

    public function pageReportJual(Request $request)
    {
        // Access control
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Default to current month and year if no filters are provided
        $month = $request->get('month', date('m')); // Default to current month
        $year = $request->get('year', date('Y'));   // Default to current year

        // Query the cashback data
        $query = DB::table('finance_cashback AS fc')
            ->leftJoin('master_customer_other_addresses AS mcoa', 'fc.customer_other_address_id', '=', 'mcoa.id')
            ->leftJoin('penjualan_do AS pdo', 'fc.do_id', '=', 'pdo.id')
            ->leftJoin('penjualan_so AS pso', 'pdo.so_id', '=', 'pso.id')
            ->leftJoin('finance_cashback_detail AS fcd', 'fc.id', '=', 'fcd.cashback_id')
            ->select(
                'pso.so_date AS date', 
                'fc.code AS code', 
                'mcoa.name AS customer_name', 
                'mcoa.text_kota AS customer_kota', 
                DB::raw('SUM(fcd.subtotal_item_idr) AS total')
            )
            ->where('fc.status', 1)
            ->groupBy('fc.id', 'fc.code', 'pso.so_date', 'mcoa.name', 'mcoa.text_kota');

        // Apply filters BEFORE calling get()
        if ($month) {
            $query->whereMonth('pso.so_date', $month);
        }
        if ($year) {
            $query->whereYear('pso.so_date', $year);
        }

        // Execute the query
        $data['cashback'] = $query->get();

        return view($this->view . "jual", $data);
        
    }
}