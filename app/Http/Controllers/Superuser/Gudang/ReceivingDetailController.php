<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Gudang\ReceivingDetailColly;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;

class ReceivingDetailController extends Controller
{
    public function get_sku_json(Request $request)
    {
        if ($request->ajax()) {
            switch ($request->type) {
                case 'GET_SELECT_SKU':
                    $receiving = Receiving::where('status', '!=', Receiving::STATUS['DELETED'] )->pluck('id')->all();
                    $receiving_detail_ids = ReceivingDetail::whereIn('receiving_id', $receiving )
                                    ->where('po_id', $request->id)
                                    ->selectRaw('receiving_detail.id, receiving_detail.po_detail_id, receiving_detail.quantity, sum(receiving_detail_colly.quantity_ri) AS qty')
                                    ->join('receiving_detail_colly', 'receiving_detail_colly.receiving_detail_id', '=', 'receiving_detail.id')
                                    ->groupBy('receiving_detail.id', 'po_detail_id', 'receiving_detail.quantity')
                                    ->having('qty', \DB::raw('receiving_detail.quantity'))
                                    ->pluck('po_detail_id')
                                    ->all();

                    $purchase_order_details = PurchaseOrderDetail::whereNotIn('id', $receiving_detail_ids)->where('po_id', $request->id)->get();

                    foreach($purchase_order_details as $purchase_order_detail) {
                        $data[] = [
                            'po_detail_id' => $purchase_order_detail->id,
                            'product'       => ProductPack::findOrFail($purchase_order_detail->product_pack->id),
                        ];
                    }

                    return response()->json(['code'=> 200, 'data' => $data]);
                    break;
                case 'GET_TEXT_DETAIL':
                    $purchase_order_detail = PurchaseOrderDetail::findOrFail($request->id);

                    $quantity = ReceivingDetail::where('po_detail_id', $request->id)->sum('quantity');

                    $total_quantity_ri = 0;
                    $receiving_detail = ReceivingDetail::where('po_detail_id', $request->id)->get();
                    foreach ($receiving_detail as $detail) {
                        $total_in_colly = ReceivingDetailColly::where('receiving_detail_id', $detail->id)->sum('quantity_ri');
                        $total_quantity_ri = $total_quantity_ri + $total_in_colly;
                    }

                    $data = [
                        'product'               => ProductPack::findOrFail($purchase_order_detail->product_pack->id),
                        'packaging'             => Packaging::findOrFail($purchase_order_detail->product_pack->kemasan()->id),
                        'purchase_order_detail' => $purchase_order_detail,
                        'quantity'              => $purchase_order_detail->quantity - $total_quantity_ri,
                    ];
                    return response()->json(['code'=> 200, 'data' => $data]);
                    break;
                default:
                    return response()->json(['code'=> 301]);
            }

            
        }
    }

    public function show($id, $detail_id)
    {
        if(!Auth::guard('superuser')->user()->can('receiving-show')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::findOrFail($id);
        $data['receiving_detail'] = ReceivingDetail::findOrFail($detail_id);

        return view('superuser.gudang.receiving_detail.show', $data);
    }
    
    public function create($id)
    {
        if(!Auth::guard('superuser')->user()->can('receiving-create')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::findOrFail($id);

        $receiving = Receiving::where('status', '!=', Receiving::STATUS['DELETED'] )->pluck('id')->all();
    
        $receiving_detail_ids = ReceivingDetail::whereIn('receiving_id', $receiving )
                                ->selectRaw('receiving_detail.id, receiving_detail.po_detail_id, receiving_detail.quantity, sum(receiving_detail_colly.quantity_ri) AS qty')
                                ->join('receiving_detail_colly', 'receiving_detail_colly.receiving_detail_id', '=', 'receiving_detail.id')
                                ->groupBy('receiving_detail.id', 'po_detail_id', 'receiving_detail.quantity')
                                ->having('qty', \DB::raw('receiving_detail.quantity'))
                                ->pluck('po_detail_id')
                                ->all();

        $receiving_detail_current = ReceivingDetail::where('receiving_id', $id)->pluck('po_detail_id')->all();
        $merge = array_merge($receiving_detail_ids, $receiving_detail_current);
        $purchase_order_details = PurchaseOrderDetail::whereNotIn('id', $merge)->pluck('po_id')->all();
        $purchase_orders = PurchaseOrder::whereIn('id', $purchase_order_details)
                                ->where([
                                    [ 'status', PurchaseOrder::STATUS['ACC'] ],
                                    ])
                                ->orderBy('code')->get();
        
        $data['purchase_orders'] = $purchase_orders;

        return view('superuser.gudang.receiving_detail.create', $data);
    }

    public function store(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'ppb' => 'required|integer',
                'ppb_detail' => 'required|integer',
                'product' => 'required|string',
                'quantity' => 'required|numeric',
                'delivery_cost' => 'nullable|numeric',
                'description' => 'nullable|string',
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
                $receiving = Receiving::find($id);
                $ppb_detail = PurchaseOrderDetail::find($id);

                if ($receiving == null) {
                    abort(404);
                }

                $receiving_detail = new ReceivingDetail;

                $receiving_detail->receiving_id = $receiving->id;
                $receiving_detail->po_id = $request->ppb;
                $receiving_detail->po_detail_id = $request->ppb_detail;
                $receiving_detail->product_packaging_id = $request->product;
                $receiving_detail->quantity = $request->quantity;
                $receiving_detail->no_batch = $request->no_batch;
                $receiving_detail->note = $request->description;
                $receiving_detail->created_by = Auth::id();

                

                if ($receiving_detail->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.step', $id);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id, $detail_id)
    {
        if(!Auth::guard('superuser')->user()->can('receiving-edit')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::findOrFail($id);
        $data['receiving_detail'] = ReceivingDetail::findOrFail($detail_id);

        return view('superuser.gudang.receiving_detail.edit', $data);
    }

    public function update(Request $request, $id, $detail_id)
    {
        if ($request->ajax()) {
            $receiving_detail = ReceivingDetail::find($detail_id);

            if ($receiving_detail == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'description' => 'nullable',
                'delivery_cost' => 'nullable|numeric'
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
                $receiving_detail->description = $request->description;
                $receiving_detail->delivery_cost = $request->delivery_cost ?? 0;

                if ($receiving_detail->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.step', ['id' => $id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id, $detail_id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('receiving-delete')) {
                return abort(403);
            }

            $receiving = Receiving::find($id);
            $receiving_detail = ReceivingDetail::find($detail_id);

            if ($receiving === null OR $receiving_detail === null) {
                abort(404);
            }

            if ($receiving_detail->delete()) {
                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }
}
