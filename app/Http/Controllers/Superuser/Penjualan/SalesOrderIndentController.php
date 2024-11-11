<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Exports\Penjualan\SalesOrderIndentExport;
use App\Entities\Setting\UserMenu;
use Excel;
use Auth;
use DB;
use PDF;
use COM;
use Carbon;

class SalesOrderIndentController extends Controller
{
    public function __construct(){
        $this->view = "superuser.penjualan.sales_order_indent.";
        $this->route = "superuser.penjualan.sales_order_indent";
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

    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $sales_order = SalesOrder::where('so_indent', 1)->get();

        $data = [
            'sales_order' => $sales_order,
        ];

        return view($this->view."index",$data);
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
    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();
            try{

                $sales_order = SalesOrder::find($id);

                if($sales_order == null){
                    abort(404);
                }

                $sales_order->deleted_by = Auth::id();
                $sales_order->condition = 0;
                $sales_order->delete();


                foreach($sales_order->so_detail as $detail){
                    $item = SalesOrderItem::where('id', $detail->id)->get();

                    foreach($item as $data){
                        SalesOrderItem::find($data->id)->delete();
                    }
                }

                if($sales_order->save()){
                    DB::commit();
                    $response['redirect_to'] = route('superuser.penjualan.sales_order_indent.index');
                    return $this->response(200, $response);
                }

            }catch (\Exception $e) {
                DB::rollback();
                DD($e);
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => "Internal Server Error",
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function export(Request $request)
    {
        $filename = 'Sales-Order-Indent-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new SalesOrderIndentExport, $filename);
    }

    public function print_out_indent(Request $request, $so_id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $sales_order = SalesOrder::find($so_id);

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\so\\so_indent.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\so\\export\\'.$sales_order->so_code.'-INDENT'.'.pdf';

        //- Variables - Server Information 
        $my_server = "LOCAL"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";

        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{penjualan_so.id}= $sales_order->id";

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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\so\\export\\'.$sales_order->so_code.'-INDENT'.'.pdf';

        // if($get_do->type_transaction == 1 && $get_do->so->payment_status == 1){
        //     $file->SetWatermarkText("PAID");
        // }elseif($get_do->type_transaction == 2 && $get_do->so->payment_status == 2){
        //     $file->SetWatermarkText("COPY");
        // }

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
