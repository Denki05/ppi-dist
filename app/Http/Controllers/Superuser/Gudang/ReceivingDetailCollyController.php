<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Gudang\ReceivingDetailColly;
use App\Repositories\ReceivingDetailCollyRepo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;

class ReceivingDetailCollyController extends Controller
{
    public function create($id, $detail_id)
    {
        if(!Auth::guard('superuser')->user()->can('receiving-create')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::findOrFail($id);
        $data['receiving_detail'] = ReceivingDetail::findOrFail($detail_id);

        return view('superuser.gudang.receiving_detail_colly.create', $data);
    }

    public function store(Request $request, $id, $detail_id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:receiving_detail_colly,code',
                'colly' => 'required|numeric|min:1',
                'ri' => 'required|numeric|min:1',
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
                DB::beginTransaction();
                $error = false;

                $receiving_detail = ReceivingDetail::find($detail_id);

                if ($receiving_detail == null) {
                    abort(404);
                }
                
                $quantity_satuan = $request->ri / $request->colly;
                
                // DD($request);

                for ($i=0; $i < $request->colly ; $i++) { 
                    $receiving_detail_colly = new ReceivingDetailColly;

                    $receiving_detail_colly->receiving_detail_id = $receiving_detail->id;
                    $receiving_detail_colly->code = ReceivingDetailCollyRepo::generateCode();
                    $receiving_detail_colly->quantity_colly = '1';
                    $receiving_detail_colly->quantity_ri = $quantity_satuan;
                    $receiving_detail_colly->is_reject = $request->has('is_reject') ? '1' : null;

                    if ($receiving_detail_colly->save()) {

                        $receiving_detail->total_quantity_colly = ReceivingDetailColly::where('receiving_detail_id', $detail_id)->whereNull('is_reject')->sum('quantity_colly');
                        $receiving_detail->total_quantity_ri = ReceivingDetailColly::where('receiving_detail_id', $detail_id)->whereNull('is_reject')->sum('quantity_ri');
                        $receiving_detail->save();
                        
                        // $receiving_detail_colly->hpp = ($receiving_detail->ppb_detail->total_price_idr / $receiving_detail->ppb_detail->quantity) +
                        //                                ($receiving_detail->delivery_cost / (($receiving_detail->total_quantity_ri == 0) ? 1 : $receiving_detail->total_quantity_ri));

                        $receiving_detail_colly->save();

                        $total_in_colly = ReceivingDetailColly::where('receiving_detail_id', $detail_id)->sum('quantity_ri');
                        if($total_in_colly > $receiving_detail->quantity) {
                            $error = true;
                            break;
                        }
                    }
                }

                if($error) {
                    DB::rollBack();

                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => array('Quantity exceeds the PPB Quantity!'),
                    ];
                } else {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.detail.show', [$id, $detail_id]);
                }

                DB::commit();
                return $this->response(200, $response);
            }
        }
    }

    public function edit($id, $detail_id, $colly_id)
    {
        if(!Auth::guard('superuser')->user()->can('receiving-edit')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::findOrFail($id);
        $data['receiving_detail'] = ReceivingDetail::findOrFail($detail_id);
        $data['receiving_detail_colly'] = ReceivingDetailColly::findOrFail($colly_id);

        return view('superuser.gudang.receiving_detail_colly.edit', $data);
    }

    public function update(Request $request, $id, $detail, $colly)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'colly' => 'required|numeric',
                'ri' => 'required|numeric',
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
                $receiving_detail = ReceivingDetail::find($detail);
                $receiving_detail_colly = ReceivingDetailColly::find($colly);

                if ($receiving == null OR $receiving_detail == null OR $receiving_detail_colly == null) {
                    abort(404);
                }
                
                DB::beginTransaction();
                $error = false;

                $receiving_detail_colly->quantity_colly = $request->colly;
                $receiving_detail_colly->quantity_ri = $request->ri;
                $receiving_detail_colly->is_reject = $request->has('is_reject') ? '1' : null;

                if ($receiving_detail_colly->save()) {
                    $receiving_detail->total_quantity_colly = ReceivingDetailColly::where('receiving_detail_id', $detail)->whereNull('is_reject')->sum('quantity_colly');
                    $receiving_detail->total_quantity_ri = ReceivingDetailColly::where('receiving_detail_id', $detail)->whereNull('is_reject')->sum('quantity_ri');
                    $receiving_detail->save();
                    
                    $receiving_detail_colly->hpp = ($receiving_detail->ppb_detail->total_price_idr / $receiving_detail->ppb_detail->quantity) +
                                                    ($receiving_detail->delivery_cost / (($receiving_detail->total_quantity_ri == 0) ? 1 : $receiving_detail->total_quantity_ri));

                    $receiving_detail_colly->save();

                    $total_in_colly = ReceivingDetailColly::where('receiving_detail_id', $detail)->sum('quantity_ri');
                    if($total_in_colly > $receiving_detail->quantity) {
                        $error = true;
                    }

                    if($error) {
                        DB::rollBack();
    
                        $response['notification'] = [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => array('Quantity exceeds the PPB Quantity!'),
                        ];
                    } else {
                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
    
                        $response['redirect_to'] = route('superuser.gudang.receiving.detail.show', [$id, $detail]);
                    }
    
                    DB::commit();

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id, $detail_id, $colly_id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('receiving-delete')) {
                return abort(403);
            }

            $receiving = Receiving::find($id);
            $receiving_detail = ReceivingDetail::find($detail_id);
            $receiving_detail_colly = ReceivingDetailColly::find($colly_id);

            if ($receiving === null OR $receiving_detail === null OR $receiving_detail_colly === null) {
                abort(404);
            }

            if ($receiving_detail_colly->delete()) {
                $receiving_detail->total_quantity_colly = ReceivingDetailColly::where('receiving_detail_id', $detail_id)->whereNull('is_reject')->sum('quantity_colly');
                $receiving_detail->total_quantity_ri = ReceivingDetailColly::where('receiving_detail_id', $detail_id)->whereNull('is_reject')->sum('quantity_ri');
                $receiving_detail->save();

                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }
}
