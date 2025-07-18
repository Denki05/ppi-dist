<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Gudang\PurchaseOrderSummary;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\DataTables\Gudang\PurchaseOrderSPKTable;
use App\Exports\Gudang\PurchaseOrderDetailImportTemplate;
use App\Imports\Gudang\PurchaseOrderDetailImport;
use App\Entities\Master\Warehouse;
use Auth;
use COM;
use DB;
use Excel;
use PDF;
use Validator;
use Carbon\Carbon;

class PurchaseOrderSPKController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.purchase_order_spk.";
        $this->route = "superuser.gudang.purchase_order_spk";
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

    public function json(Request $request, PurchaseOrderSPKTable $datatable)
    {
        return $datatable->build();
    }

    public function search_sku(Request $request)
    {
        $products = ProductPack::where('name', 'LIKE', '%'.$request->input('q', '').'%')
            ->where('status', ProductPack::STATUS['ACTIVE'])
            ->get(['id', 'code as text', 'name']);
        return ['results' => $products];
    }

    public function search_kemasan(Request $request)
    {
        $packagings = Packaging::where('pack_name', 'LIKE', '%'.$request->input('q', '').'%')
            ->where('status', Packaging::STATUS['ACTIVE'])
            ->get(['id', 'pack_name as text']);
        return ['results' => $packagings];
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

        $data['purchase_order'] = PurchaseOrder::where('type', PurchaseOrder::TYPE['SPK'])->get();

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
                $purchase_order->type = 0;
                $purchase_order->note = $request->note;
                $purchase_order->created_by = Auth::id();

                $purchase_order->status = PurchaseOrder::STATUS['DRAFT'];

                if ($purchase_order->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.step', ['id' => $purchase_order->id]);

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

                    $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.step', ['id' => $purchase_order->id]);

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

                $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');

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

                $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');

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
    
                    $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');
    
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
                    $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');
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
                $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');
                return $this->response(200, $response);
            }
        }
    }

    public function print_pdf($id)
    {
        if (empty($id) || !is_numeric($id)) {
            abort(404, 'PO ID tidak valid.');
        }

        $result = PurchaseOrder::find($id);
        if (!$result) {
            abort(404, 'PO tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.gudang.purchase_order_spk.print_pdf', $data)
                ->setPaper('a5', 'landscape');

        $generate = false; // Ubah sesuai logika bisnis.

        if ($generate) {
            return $pdf->download("PO-{$result->code}.pdf");
        }

        return $pdf->stream("PO-{$result->code}.pdf");
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
    
    public function cancel_acc(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                abort(405);
            }
        }
        
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order === null) {
                abort(404);
            }

            $purchase_order->acc_at = null;
            $purchase_order->acc_by = null;
            $purchase_order->updated_by = Auth::id();
            $purchase_order->status = PurchaseOrder::STATUS['DRAFT'];

            if ($purchase_order->save()) {
                $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');
                return $this->response(200, $response);
            }
        }
    }

    public function send(Request $request, $id)
    {
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order === null) {
                abort(404);
            }

            $purchase_order->updated_by = Auth::id();
            $purchase_order->status = PurchaseOrder::STATUS['SENT'];

            if ($purchase_order->save()) {
                $purchase_order_details = PurchaseOrderDetail::where('po_id', $id)->get();
                foreach ($purchase_order_details as $detail) {
                    $summary = new PurchaseOrderSummary;
                    $summary->po_id = $id;
                    $summary->product_packaging_id = $detail->product_packaging_id;
                    $summary->quantity = $detail->quantity;
                    $summary->status = PurchaseOrderSummary::STATUS['UNDONE'];
                    $summary->save();
                }

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');

                return $this->response(200, $response);
            }
        }
    } 

    public function summary()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $summary = DB::table('purchase_order_summary')
            ->leftJoin('master_products_packaging', 'purchase_order_summary.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('purchase_order', 'purchase_order_summary.po_id', '=', 'purchase_order.id')
            ->select(
                'master_products_packaging.id as id',
                'master_products_packaging.name as produk_name',
                'master_products_packaging.code as produk_code',
                'master_packaging.pack_name as kemasan',
                DB::raw('SUM(purchase_order_summary.quantity) as total_quantity'),
                DB::raw('GROUP_CONCAT(DISTINCT purchase_order.code ORDER BY purchase_order.code SEPARATOR ", ") as kode_po'),
                'purchase_order_summary.created_at as created_at'
            )
            ->where('purchase_order_summary.status', PurchaseOrderSummary::STATUS['UNDONE'])
            ->where('purchase_order.type', 0)
            ->groupBy(
                'master_products_packaging.id',
                'master_products_packaging.name',
                'master_products_packaging.code',
                'master_packaging.pack_name'
            )
            ->orderBy('master_products_packaging.name', 'ASC')
            ->get();

        return view($this->view."summary", compact('summary'));
    }

    public function cancel_send(Request $request, $id)
    {
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order === null) {
                abort(404);
            }

            if ($purchase_order->receiving_detail()->exists()) { 
                return $this->response(400, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'content' => 'PO tidak dapat dibatalkan karena sudah ada penerimaan yang terkait.',
                    ]
                ]);
            }

            DB::beginTransaction();
            try {

                $purchase_order->updated_by = Auth::id();
                $purchase_order->status = PurchaseOrder::STATUS['ACC'];
                $purchase_order->save();

                PurchaseOrderSummary::where([
                    ['po_id', $purchase_order->id],
                    ['status', PurchaseOrderSummary::STATUS['UNDONE']]
                ])->delete();

                DB::commit();

                return $this->response(200, [
                    'notification' => [
                        'alert'   => 'notify',
                        'type'    => 'success',
                        'content' => 'PO berhasil dibatalkan dari status SENT'
                    ],
                    'redirect_to' => route('superuser.gudang.purchase_order_spk.index')
                ]);

            }catch (\Exception $e) {
                DB::rollBack();
                return $this->response(500, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'content' => 'Terjadi kesalahan: '.$e->getMessage()
                    ]
                ]);
            }
        }
    }
}