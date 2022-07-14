<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Vendor;
use App\Entities\Master\VendorDetail;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Entities\Setting\UserMenu;
use App\Entities\Master\Unit;

class VendorDetailController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.vendor_detail";
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

    public function create($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['vendor'] = Vendor::findOrFail($id);
        $data['unit'] = Unit::all();

        return view('superuser.master.vendor_detail.create', $data);
    }

    public function store(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'transaction' => 'required|string',
                'quantity' => 'required|string',
                'satuan' => 'required|string',
                'grand_total' => 'required|string'
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
                $vendor = Vendor::find($id);

                if ($vendor == null) {
                    abort(404);
                }

                $detail = new VendorDetail;

                $detail->vendor_id = $vendor->id;
                $detail->transaction = $request->transaction;
                $detail->quantity = $request->quantity;
                $detail->satuan = $request->satuan;
                $detail->grand_total = $request->grand_total;
                
                if ($detail->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.vendor.show', $id);

                    return $this->response(200, $response);
                }
            }
        }
    }


    // public function edit($id, $detail)
    // {
    //     if(!Auth::guard('superuser')->user()->can('purchase order-edit')) {
    //         return abort(403);
    //     }

    //     $data['purchase_order'] = PurchaseOrder::findOrFail($id);
    //     $data['purchase_order_detail'] = PurchaseOrderDetail::findOrFail($detail);

    //     return view('superuser.purchasing.purchase_order_detail.edit', $data);
    // }

    // public function edit($id, $detail)
    // {
    //     // Access
    //     if(Auth::user()->is_superuser == 0){
    //         if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
    //             return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
    //         }
    //     }

    //     $data['vendor'] = Vendor::findOrFail($id);
    //     $data['detail'] = VendorDetail::findOrFail($detail);
        

    //     return view('superuser.master.vendor_detail.edit', $data);
    // }

    // public function update(Request $request, $id, $detail)
    // {
    //     if ($request->ajax()) {
    //         $validator = Validator::make($request->all(), [
    //             'transaction' => 'required|string',
    //             'quantity' => 'required|string',
    //             'satuan' => 'required|string',
    //             'grand_total' => 'required|string'
    //         ]);

    //         if ($validator->fails()) {
    //             $response['notification'] = [
    //                 'alert' => 'block',
    //                 'type' => 'alert-danger',
    //                 'header' => 'Error',
    //                 'content' => $validator->errors()->all(),
    //             ];
  
    //             return $this->response(400, $response);
    //         }

    //         if ($validator->passes()) {
    //             $vendor = Vendor::find($id);
    //             $detail = VendorDetail::find($detail);

    //             if ($vendor == null OR $detail == null) {
    //                 abort(404);
    //             }

    //             $detail->vendor_id = $vendor->id;
    //             $detail->transaction = $request->transaction;
    //             $detail->quantity = $request->quantity;
    //             $detail->satuan = $request->satuan;
    //             $detail->grand_total = $request->grand_total;

    //             if ($detail->save()) {
    //                 $response['notification'] = [
    //                     'alert' => 'notify',
    //                     'type' => 'success',
    //                     'content' => 'Success',
    //                 ];

    //                 $response['redirect_to'] = route('superuser.master.vendor.index', $id);

    //                 return $this->response(200, $response);
    //             }
    //         }
    //     }
    // }

    // public function destroy(Request $request, $id, $detail)
    // {
    //     // Access
    //     if(Auth::user()->is_superuser == 0){
    //         if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
    //             abort(405);
    //         }
    //     }
    //     if ($request->ajax()) {
    //         $vendor = Vendor::find($id);
    //         $detail = VendorDetail::find($detail);

    //         if ($vendor === null OR $detail === null) {
    //             abort(404);
    //         }

    //         $detail->status = VendorDetail::STATUS['DELETED'];

    //         if ($detail->save()) {
    //             $response['redirect_to'] = 'reload()';
    //             return $this->response(200, $response);
    //         }
    //     }
    // }

    // public function bulk_delete(Request $request)
    // {
    //     if ($request->ajax()) {
    //         if(!Auth::guard('superuser')->user()->can('purchase order-delete')) {
    //             return abort(403);
    //         }
    //         $purchase_order = PurchaseOrder::find($request->purchase_order_id);

    //         if ($purchase_order === null) {
    //             abort(404);
    //         }

    //         $ids = $request->ids;

    //         if($ids) {
    //             foreach ($ids as $id) {
    //                 $purchase_order_detail = PurchaseOrderDetail::find($id);
    //                 $purchase_order_detail->delete();
    //             }

    //             $purchase_order->grand_total_rmb = PurchaseOrderDetail::where('ppb_id', $request->purchase_order_id)->sum('total_price_rmb');
    //             $purchase_order->grand_total_idr = PurchaseOrderDetail::where('ppb_id', $request->purchase_order_id)->sum('total_price_idr');
    //             $purchase_order->save();
    //         }
                
    //         $response['redirect_to'] = 'reload()';
    //         return $this->response(200, $response);
    //     }
    // }
}