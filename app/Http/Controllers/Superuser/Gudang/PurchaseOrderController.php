<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Product;
use App\DataTables\Gudang\PurchaseOrderTable;
use App\Exports\Gudang\PurchaseOrderDetailImportTemplate;
use App\Imports\Gudang\PurchaseOrderDetailImport;
use App\Entities\Master\Warehouse;
use Auth;
use COM;
use DB;
use Excel;
use Validator;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.purchase_order.";
        $this->route = "superuser.gudang.purchase_order";
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

    public function json(Request $request, PurchaseOrderTable $datatable)
    {
        return $datatable->build();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::get();

        return view($this->view."index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouse'] = Warehouse::get();

        return view($this->view."create", $data);
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
                'code' => 'required|string|unique:purchase_order,code',
                'warehouse' => 'required|integer',
                'etd'  =>  'required|date',
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

            if ($validator->passes()) {
                $purchase_order = new PurchaseOrder;

                $purchase_order->code = $request->code;
                $purchase_order->warehouse_id = $request->warehouse;
                $purchase_order->etd = $request->etd;
                $purchase_order->note = $request->note;
                $purchase_order->created_by = Auth::id();

                $purchase_order->status = PurchaseOrder::STATUS['DRAFT'];

                if ($purchase_order->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.purchase_order.step', ['id' => $purchase_order->id]);

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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::find($id);

        return view($this->view."show", $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_edit == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::find($id);
        $data['warehouse'] = Warehouse::get();
        
        return view($this->view."edit", $data);
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
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:purchase_order,code,' . $purchase_order->id,
                'warehouse' => 'required|integer',
                'etd'  =>  'required|date',
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

            if ($validator->passes()) {
                $purchase_order->code = $request->code;
                $purchase_order->warehouse_id = $request->warehouse;
                $purchase_order->etd = $request->etd;
                $purchase_order->note = $request->note;

                if ($purchase_order->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.purchase_order.step', ['id' => $purchase_order->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function step($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::findOrFail($id);
        $data['merek'] = BrandLokal::get();

        if($data['purchase_order']->status == PurchaseOrder::STATUS['ACC'] OR $data['purchase_order']->status == PurchaseOrder::STATUS['DELETED']) {
            return abort(404);
        }

        return view($this->view."step", $data);
    }

    public function publish(Request $request, $id)
    {
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order == null) {
                abort(404);
            }

            $purchase_order->updated_by = Auth::id();
            $purchase_order->status = PurchaseOrder::STATUS['ACTIVE'];

            if ($purchase_order->save()) {
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.gudang.purchase_order.index');

                return $this->response(200, $response);
            }
        }
    }

    public function unpublish(Request $request, $id)
    {
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order == null) {
                abort(404);
            }

            $purchase_order->updated_by = Auth::id();
            $purchase_order->status = PurchaseOrder::STATUS['DRAFT'];

            if ($purchase_order->save()) {
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.gudang.purchase_order.index');

                return $this->response(200, $response);
            }
        }
    }

    public function save_modify(Request $request, $id, $save_type)
    {
        if ($request->ajax()) {

            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order == null) {
                abort(404);
            }

            DB::beginTransaction();
            try{

                if($save_type == 'save') {
                    $purchase_order->edit_counter += 1;
                } else {
                    $purchase_order->acc_by = Auth::id();
                    $purchase_order->acc_at = Carbon::now()->toDateTimeString();
                }
                
                $purchase_order->status = $save_type == 'save' ? PurchaseOrder::STATUS['ACTIVE'] : PurchaseOrder::STATUS['ACC'];
    
                if ($purchase_order->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
    
                    $response['redirect_to'] = route('superuser.gudang.purchase_order.index');
    
                    return $this->response(200, $response);
                }
            }catch (\Exception $e) {
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $failed,
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order === null) {
                abort(404);
            }

            DB::beginTransaction();
            try{

                $purchase_order->acc_by = Auth::id();
                $purchase_order->acc_at = Carbon::now()->toDateTimeString();
                $purchase_order->status = PurchaseOrder::STATUS['ACC'];

                if ($purchase_order->save()) {

                    
                    DB::commit();
                    $response['redirect_to'] = route('superuser.gudang.purchase_order.index');
                    return $this->response(200, $response);
                }
            }catch (\Exception $e) {
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $failed,
                ];
    
                return $this->response(400, $response);
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order === null) {
                abort(404);
            }

            $purchase_order->status = PurchaseOrder::STATUS['DELETED'];

            if ($purchase_order->save()) {
                $response['redirect_to'] = route('superuser.gudang.purchase_order.index');
                return $this->response(200, $response);
            }
        }
    }

    public function print_pdf($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PurchaseOrder::where('id', $id)->first();

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\purchase_order\\po.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\purchase_order\\export\\'.$result->code.'.pdf';

        //- Variables - Server Information 
        $my_server = "DEV-SERVER"; 
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
        $creport->RecordSelectionFormula = "{purchase_order.id}= $result->id";

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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\purchase_order\\export\\'.$result->code.'.pdf';

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

    public function import_template()
    {
        $filename = 'purchase-order-detail-import-template.xlsx';
        return Excel::download(new PurchaseOrderDetailImportTemplate, $filename);
    }

    public function import(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new PurchaseOrderDetailImport($id);
            Excel::import($import, $request->import_file);
        
            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }
    
}
