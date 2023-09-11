<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Product;
use App\Entities\Master\Packaging;
use App\DataTables\Gudang\PurchaseOrderTable;
use App\Entities\Master\Warehouse;
use Auth;
use DB;
use Validator;

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

        return view($this->view."index");
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
                $purchase_order->edit_marker = 0;
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
                $purchase_order->edit_marker = 1;

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
            if(empty($this->access) || empty($this->access->user) || $this->access->can_edit == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::findOrFail($id);
        $data['merek'] = BrandLokal::get();
        $data['packaging'] = Packaging::get();

        if($data['purchase_order']->status == PurchaseOrder::STATUS['ACC'] OR $data['purchase_order']->status == PurchaseOrder::STATUS['DELETED']) {
            return abort(404);
        }

        return view($this->view."step", $data);
    }

    public function publish(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
           if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
               return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
           }
        }

        $purchase_order = PurchaseOrder::findOrFail($id);

        if($purchase_order == null){
            abort(404);
        }

        DB::beginTransaction();
        try{

            $purchase_order->status = PurchaseOrder::STATUS['ACTIVE'];
            $purchase_order->updated_by = Auth::id();
        
            if ($purchase_order->save()){
                DB::commit();
                return redirect()->back()->with('success','<a href="'.route('superuser.gudang.purchase_order.index').'">'.$purchase_order->code.'</a> : PO berhasil diupdate ke Publish!');
            }
        }catch (\Exception $e) {
            // dd($e);
            DB::rollback();
            $response['notification'] = [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => "Internal Server Error!",
            ];

            return $this->response(400, $response);
        }
    }

    public function save_modify(Request $request, $id, $save_type)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $purchase_order = PurchaseOrder::find($id);

        if($purchase_order == null){
            abort(404);
        }

        DB::beginTransaction();
        try{
            if($save_type == 'save'){
                $purchase_order->edit_counter += 1;
                $purchase_order->edit_marker = 0;
            }elseif($save_type == 'save-acc'){
                if(count($purchase_order->po_detail) == null){
                    return redirect()->route('superuser.gudang.purchase_order.index')->with('error','<a href="'.route('superuser.gudang.purchase_order.show', $purchase_order->id).'">'.$purchase_order->code.'</a> : Tidak ada Item yang di input!');
                }else{
                    $purchase_order->acc_at = Carbon::now()->toDateTimeString();
                    $purchase_order->acc_by = Auth::id();
                }
            }

            $purchase_order->status = $save_type == 'save' ? PurchaseOrder::STATUS['ACTIVE'] : PurchaseOrder::STATUS['ACC'];

            if ($purchase_order->save()){
                DB::commit();
                return redirect()->route('superuser.gudang.purchase_order.index')->with('success','<a href="'.route('superuser.gudang.purchase_order.index').'">'.$purchase_order->code.'</a> : PO berhasil di Update!');
            }
        }catch (\Exception $e){
            DB::rollback();

            $response['notification'] = [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => "Internal Server Error!",
            ];

            return $this->respone(400, $response);
        }
    }

    public function acc($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $purchase_order = PurchaseOrder::findOrFail($id);

        if($purchase_order == null){
            abort(404);
        }

        DB::beginTransaction();
        try{

            $purchase_order->acc_by = Auth::id();
            $purchase_order->acc_at = Carbon::now()->toDateTimeString();

            $purchase_order->status = PurchaseOrder::STATUS['ACC'];

            if($purchase_order->save()){
                DB::commit();
                return redirect()->route('superuser.gudang.purchase_order.index')->with('success','<a href="'.route('superuser.gudang.purchase_order.index').'">'.$purchase_order->code.'</a> : PO berhasil di Approve!');
            }
        }catch (\Exception $e) {
            DB::rollback();
            $response['notification'] = [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => "Internal Server Error!",
            ];

            return $this->response(400, $response);
        }
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

    public function store_item(Request $request, $purchase_id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data_json = [];
        $post = $request->all();

        if($request->method() == "POST"){

            DB::beginTransaction();
            try {

                if(empty($post["merek"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Merek wajib dipilih";
                    goto ResultData;
                }

                if(empty($post["category"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Product wajib dipilih";
                    goto ResultData;
                }

                if(empty($post["product_packaging_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Product wajib dipilih";
                    goto ResultData;
                }

                if(empty($post["qty"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Qty wajib dipilih";
                    goto ResultData;
                }

                $po_detail = new PurchaseOrderDetail;
                $po_detail->po_id = $purchase_id;
                
                if (sizeof($post["product_packaging_id"]) > 0) {
                    for ($i = 0; $i < sizeof($post["product_packaging_id"]); $i++) {
                        if(empty($post["product_packaging_id"][$i])) continue;

                       
                        $po_detail->product_packaging_id = trim(htmlentities(implode("-", [$post["product_packaging_id"][$i],$post["packaging_id"][$i]])));
                        $po_detail->qty = trim(htmlentities($post["qty"][$i]));
                        $po_detail->packaging_id = trim(htmlentities($post["packaging_id"][$i]));
                        // $po_detail->note_produksi = trim(htmlentities($post["note_produksi"][$i])) ?? null;
                        // $po_detail->note_repack = trim(htmlentities($post["note_repack"][$i])) ?? null;
                        $po_detail->created_by = Auth::id();
                    }
                }

                $po_detail->save();

                DB::commit();
                
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Product Berhasil Ditambahkan";
                goto ResultData;

            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = $e->getMessage();
        
                return response()->json($data_json,400);
            }

        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }
}
