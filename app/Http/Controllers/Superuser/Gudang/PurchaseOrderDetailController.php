<?php

namespace App\Http\Controllers\Superuser\Gudang;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Setting\UserMenu;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Master\BrandLokal;
use App\Services\PurchaseOrder\PurchaseOrderDetailService;
use Auth;

class PurchaseOrderDetailController extends Controller
{
    protected $service;

    public function __construct(PurchaseOrderDetailService $service){
        $this->service = $service;
        $this->view = "superuser.gudang.purchase_order_detail.";
        $this->route = "superuser.gudang.purchase_order.detail";
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

    public function create($purchase_id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::with('brandLokal')->findOrFail($purchase_id);
        $data['merek'] = BrandLokal::get();
        $data['packagings'] = \App\Entities\Master\Packaging::where('status', \App\Entities\Master\Packaging::STATUS['ACTIVE'])
            ->orderBy('pack_name')->get(['id', 'pack_name']);

        // return view('superuser.gudang.purchase_order_detail.create', $data);
        return view($this->view."create", $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $purchase_id)
    {
        if ($request->method() != "POST") {
            return response()->json(["IsError" => TRUE, "Message" => "Invalid Method"], 200);
        }

        $result = $this->service->storeDetails($purchase_id, $request->all(), Auth::id());
        $http = $result["http"];
        unset($result["http"]);

        return response()->json($result, $http);
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
            if(empty($this->access) || empty($this->access->user) || $this->access->can_edit == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::findOrFail($id);
        $data['purchase_order_detail'] = PurchaseOrderDetail::findOrFail($detail);
        $data['merek'] = BrandLokal::get();

        // return view('superuser.gudang.purchase_order_detail.edit', $data);
        return view($this->view."edit", $data);
    }

    public function update(Request $request, $id, $detail)
    {
        if ($request->ajax()) {
            $result = $this->service->updateDetail($id, $detail, $request->all());

            if ($result["status"] === "validation") {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $result["errors"],
                ];

                return $this->response(400, $response);
            }

            if ($result["status"] === "not_found") {
                abort(404);
            }

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = route('superuser.gudang.purchase_order.step', $id);

            return $this->response(200, $response);
        }
    }

    public function destroy(Request $request, $id, $detail_id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $result = $this->service->deleteDetail($id, $detail_id);

            if ($result["status"] === "not_found") {
                abort(404);
            }

            $response['redirect_to'] = 'reload()';
            return $this->response(200, $response);
        }
    }

    public function detail_json($purchase_id)
    {
        $data = $this->service->listDetails($purchase_id, $this->route);

        return response()->json(['IsError' => false, 'Data' => $data], 200);
    }

    public function get_product(Request $request){
        if ($request->method() != "GET") {
            return response()->json(["IsError" => TRUE, "Message" => "Invalid Method"], 200);
        }

        $post = $request->all();
        $table = $this->service->getProducts(
            isset($post["brand_name"]) ? $post["brand_name"] : null,
            isset($post["packaging_id"]) ? $post["packaging_id"] : []
        );

        return response()->json(["IsError" => FALSE, "Data" => $table], 200);
    }

    public function get_packaging(Request $request){
        if ($request->method() != "GET") {
            return response()->json(["IsError" => TRUE, "Message" => "Invalid Method"], 200);
        }

        $post = $request->all();
        $table = $this->service->getPackagings(isset($post["product_id"]) ? $post["product_id"] : null);

        return response()->json(["IsError" => FALSE, "Data" => $table], 200);
    }
}
