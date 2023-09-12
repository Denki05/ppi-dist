<?php

namespace App\Http\Controllers\Superuser\Gudang;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Master\BrandLokal;
use Auth;
use DB;
use Validator;

class PurchaseOrderDetailController extends Controller
{
    public function create($purchase_id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::findOrFail($purchase_id);
        $data['merek'] = BrandLokal::get();

        return view('superuser.gudang.purchase_order_detail.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $purchase_id)
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
                
                if (sizeof($post["product_packaging_id"]) > 0) {
                    for ($i = 0; $i < sizeof($post["product_packaging_id"]); $i++) {
                        if(empty($post["product_packaging_id"][$i])) continue;

                        $po_detail = new PurchaseOrderDetail;
                        $po_detail->po_id = $purchase_id;
                        $po_detail->brand_lokal_id =  trim(htmlentities($post["merek"]));
                        $po_detail->product_packaging_id = trim(htmlentities(implode("-", [$post["product_packaging_id"][$i],$post["packaging_id"][$i]])));
                        $po_detail->qty = trim(htmlentities($post["qty"][$i]));
                        $po_detail->packaging_id = trim(htmlentities($post["packaging_id"][$i]));
                        $po_detail->note_produksi = trim(htmlentities($post["note_produksi"][$i])) ?? null;
                        $po_detail->note_repack = trim(htmlentities($post["note_repack"][$i])) ?? null;
                        $po_detail->created_by = Auth::id();
                        $po_detail->save();
                    }
                }

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

    public function edit($id, $detail)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::findOrFail($id);
        $data['purchase_order_detail'] = PurchaseOrderDetail::findOrFail($detail);
        $data['merek'] = BrandLokal::get();

        return view('superuser.gudang.purchase_order_detail.edit', $data);
    }

    
    public function update(Request $request, $id)
    {
        //
    }

    public function destroy(Request $request, $id, $detail_id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $purchase_order = PurchaseOrder::find($id);
            $purchase_order_detail = PurchaseOrderDetail::find($detail_id);

            if ($purchase_order === null OR $purchase_order_detail === null) {
                abort(404);
            }

            if ($purchase_order_detail->delete()) {
                $purchase_order->save();
                
                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }
}
