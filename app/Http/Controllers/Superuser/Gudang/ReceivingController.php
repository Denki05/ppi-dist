<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\DataTables\Gudang\ReceivingTable;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\Hpp;
use App\Entities\Master\Company;
use App\Entities\Master\SupplierCoa;
use App\Entities\Master\Warehouse;
use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Gudang\ReceivingDetailColly;
use App\Exports\Gudang\ReceivingDetailImportTemplate;
use App\Imports\Gudang\ReceivingDetailImport;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Entities\Finance\SettingFinance;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Entities\Setting\UserMenu;
use Auth;
use Excel;
use Carbon\Carbon;
use DomPDF;
use Validator;
use DB;

class ReceivingController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.receiving.";
        $this->route = "superuser.gudang.receiving";
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

    public function json(Request $request, ReceivingTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view($this->view."index");
    }

    public function create()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouses'] = Warehouse::get();

        return view($this->view."create", $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code'              => 'required|string|unique:receiving,code',
                'warehouse'         => 'required|integer',
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
                $receiving = new Receiving;

                $receiving->code = $request->code;
                $receiving->warehouse_id = $request->warehouse;
                $receiving->pbm_date = $request->pbm_date;
                $receiving->note = $request->note;

                $receiving->status = Receiving::STATUS['ACTIVE'];

                if ($receiving->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.step', ['id' => $receiving->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['receiving'] = Receiving::find($id);

        return view($this->view."edit", $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $receiving = Receiving::find($id);

            if ($receiving == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:receiving,code,' . $receiving->id,
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
                $receiving->code = $request->code;
                $receiving->pbm_date = $request->pbm_date;
                $receiving->note = $request->note;

                if ($receiving->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.step', ['id' => $receiving->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function step($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.gudang.receiving.step', $data);
    }

    public function publish(Request $request, $id)
    {
        return $this->save_acc($request, $id, 'publish');
    }

    public function acc(Request $request, $id)
    {
        return $this->save_acc($request, $id, 'acc');
    }

    private function save_acc(Request $request, $id, $button_type)
    {
        if($request->ajax()){
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $failed = "";

            DB::beginTransaction();

            try{

                $receiving = Receiving::find($id);
                $total_qty_ri = 0;
                foreach($receiving->details as $detail){
                    if($detail->total_quantity_ri == 0){
                        $failed = "Item quantity belum di input!";
                        break;
                    }
                }
                
                if ($failed) {
                    $response['failed'] = $failed;
                    DB::rollback();
                    return $this->response(200, $response);
                }else{
                    $receiving->acc_by = Auth::id();
                    $receiving->acc_at = Carbon::now()->toDateTimeString();
                    $receiving->status = Receiving::STATUS['ACC'];

                    if ($receiving->save()) {
                        // Input stock
                        foreach($receiving->details as $detail){
                            $check_stock = ProductMinStock::where('product_packaging_id', $detail->product_packaging_id)
                                    ->where('warehouse_id', $detail->receiving->warehouse_id)
                                    ->first();

                            $total_quantity = $detail->total_quantity_ri;
                            $get_stock = $check_stock->quantity;
                            // DD($get_stock);
                            $check_stock->quantity = $get_stock + $total_quantity;
                            $check_stock->save();
                            
                            // Record log stock
                            $move = StockMove::where('product_packaging_id', $detail->product_packaging_id)
                            ->where('warehouse_id', $receiving->warehuse_id)
                            ->get();
                            $move_in = $move->sum('stock_in');
                            $move_out = $move->sum('stock_out');
                            
                            $sisa = $get_stock + $move_in - $move_out + $total_quantity;
                            $insert_stock_move = StockMove::create([
                                'code_transaction' => 'Receiving-'.$receiving->code,
                                'warehouse_id' => $receiving->warehouse_id,
                                'product_packaging_id' => $detail->product_packaging_id,
                                'stock_in' => $total_quantity,
                                'stock_balance' => $sisa,
                                'created_by' => Auth::id()
                            ]);
                        }
                        

                        DB::commit();
                        if ($button_type == 'publish') {
                            $response['redirect_to'] = route('superuser.gudang.receiving.index');
                        } else {
                            $response['redirect_to'] = '#datatable';
                        }

                        return $this->response(200, $response);
                    }
                }
            } catch (\Exception $e) {
                dd($e);
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
    
    public function cancel_approve(Request $request, $id)
    {
        if ($request->ajax()) {
            $failed = "";

            DB::beginTransaction();

            try{
                $receiving = Receiving::find($id);
                
                $superuser = Auth::guard('superuser')->user();
                $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

                if($journal_periode) {
                    $min_date = Carbon::parse( $journal_periode->to_date );
                    if($receiving->acc_at <= $min_date ) {
                        $response['failed'] = 'Transaksi sudah terposting';
                        return $this->response(200, $response);
                    }
                }
                
                foreach($receiving->details as $detail){
                    foreach($detail->collys as $colly){
                        if($colly->status_mutation == 1){
                            $failed = "Ada SKU yang sudah dimutasi";
                            break;
                        }

                        if($colly->status_recondition == 1){
                            $failed = "Ada SKU yang sudah direkondisi";
                            break;
                        }
                    }
                }

                if ($failed) {
                    $response['failed'] = $failed;
                    DB::rollback();

                    return $this->response(200, $response);
                }

                //Delete jurnal ACC
                $jurnals = Journal::where('transaction_type', Journal::TRANSACTION_TYPE['RECEIVING'])
                                  ->where('transaction_id', $receiving->id)
                                  ->get();

                if($jurnals->isEmpty()){
                    //Delete jurnal ACC
                    $jurnals = Journal::where('name', Journal::PREJOURNAL['RI_ACC'] . $receiving->code)->get();

                    foreach($jurnals as $jurnal){
                        $data = Journal::find($jurnal->id);

                        $data->delete();
                    }

                    //Delete jurnal TAX
                    $jurnals = Journal::where('name', Journal::PREJOURNAL['RI_TAX'] . $receiving->code)->get();

                    foreach($jurnals as $jurnal){
                        $data = Journal::find($jurnal->id);

                        $data->delete();
                    }

                    foreach($receiving->details as $detail){
                        $jurnals = Journal::where('name', Journal::PREJOURNAL['RI_REJECT'] . $detail->purchase_order->code);

                        foreach($jurnals as $jurnal){
                            $data = Journal::find($jurnal->id);

                            $data->delete();
                        }
                    }
                } else {
                    foreach($jurnals as $jurnal){
                        $data = Journal::find($jurnal->id);

                        $data->delete();
                    }
                }

                $receiving->status = Receiving::STATUS['ACTIVE'];
                $receiving->acc_by = null;
                $receiving->acc_at = null;

                if($receiving->save()){

                    DB::commit();
                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                // DD($e);
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

    public function show($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.gudang.receiving.show', $data);
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['company'] = Company::find(1);
        $data['receiving'] = Receiving::findOrFail($id);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView('superuser.purchasing.receiving.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function print_barcode($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['receiving'] = Receiving::findOrFail($id);

        $pdf = DomPDF::loadView('superuser.purchasing.receiving.print_barcode', $data);

        // 50mm x 70mm
        $customPaper = array(0, 0, 198.10, 141.50);
        $pdf->setPaper($customPaper, 'portrait');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function print_barcodeWithCode($code)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-print')) {
            return abort(403);
        }

        $code = str_replace('\\', '/', $code);

        $data['receiving'] = Receiving::where('code', $code)->first();

        $pdf = DomPDF::loadView('superuser.purchasing.receiving.print_barcode', $data);

        // 50mm x 70mm
        $customPaper = array(0, 0, 198.10, 141.50);
        $pdf->setPaper($customPaper, 'portrait');

        return $pdf->stream();
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    abort(405);
                }
            }

            $receiving = Receiving::find($id);

            if ($receiving === null) {
                abort(404);
            }

            $receiving->status = Receiving::STATUS['DELETED'];

            if ($receiving->save()) {

                $response['redirect_to'] = route('superuser.gudang.receiving.index');
                return $this->response(200, $response);
            }
        }
    }
    
    public function import_template()
    {
        $filename = 'receiving-detail-import-template.xlsx';
        return Excel::download(new ReceivingDetailImportTemplate, $filename);
    }

    public function import(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        // if ($validator->passes()) {
        //     $import = new ReceivingDetailImport($id);
        //     Excel::import($import, $request->import_file);
            
        //     if($import->error) {
        //         return redirect()->back()->withErrors($import->error);
        //     }
            
        //     return redirect()->back()->with(['message' => 'Import success']);
        // }
        if ($validator->passes()) {
            $import = new ReceivingDetailImport($id);
            Excel::import($import, $request->import_file);
        
            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }
}