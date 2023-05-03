<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\DataTables\Penjualan\SaleReturnTable;
use App\Entities\Master\Product;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SaleReturn;
use App\Entities\Penjualan\SaleReturnDetail;
use App\Entities\Master\Warehouse;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DomPDF;

class SaleReturnController extends Controller
{
    // public function json(Request $request, SaleReturnTable $datatable)
    // {
    //     return $datatable->build($request);
    // }

    public function search_do(Request $request)
    {
        // DD(MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
        // DB::enableQueryLog();
        $delivery_orders = DeliveryOrderDetail::where('created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString())
            ->where(function ($query) use ($request) {
                $query->where('code', 'LIKE', $request->input('q', '') . '%')
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('sales_order', function ($query) use ($request) {
                            $query->where('code', 'LIKE', $request->input('q', '') . '%')
                                ->orWhere('resi', 'LIKE', $request->input('q', '') . '%')
                                ->orWhere('store_name', 'LIKE', $request->input('q', '') . '%')
                                ->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
                        });
                    });
            })
            // ->whereHas('sales_order', function ($query) use ($request) {
            //     $query->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
            // })
            ->whereHas('delivery_order', function ($query2) {
                $query2->where('status', 2);
            })
            ->get();

        // DD(DB::getQueryLog());
        $results = [];

        foreach ($delivery_orders as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->code . ' / ' . $item->sales_order->code . ' / ' . $item->sales_order->resi . ' / ' . $item->sales_order->store_name,
            ];
        }

        return ['results' => $results];
    }

    public function get_product(Request $request)
    {
        if ($request->ajax()) {
            $data = [];

            $delivery_order_detail = DeliveryOrderDetail::find($request->id);
            $sale_return = SaleReturn::where('delivery_order_id', $request->id)
                                     ->where('status', SaleReturn::STATUS['ACC'])
                                     ->get();



            foreach ($delivery_order_detail->sales_order->sales_order_details as $key => $value) {
                $qty = $value->quantity;

                foreach($sale_return as $item){
                    foreach($item->sale_return_details as $val){
                        if($val->product_id == $value->product_id){
                            $value->quantity -= $val->quantity;
                        }
                    }
                }

                if($value->quantity > 0){
                    $data[] = [
                        'id' => $value->product_id,
                        'sku' => $value->product->code,
                        'name' => $value->product->name,
                        'quantity' => $value->quantity,
                        'hpp' => $value->hpp_total ? $value->hpp_total / $qty : '',
                        'price' => $value->price,
                    ];
                }
            }

            return response()->json(['code' => 200, 'data' => $data]);
        }
    }

    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $sales_retun = SaleReturn::get();

        $data = [
            'sales_return' => $sales_retun
        ];

        return view('superuser.penjualan.sale_return.index', $data);
    }

    public function create()
    {
        if (!Auth::guard('superuser')->user()->can('sale return-create')) {
            return abort(403);
        }

        $data['warehouses'] = Warehouse::get();

        return view('superuser.penjualan.sale_return.create', $data);
    }

    public function getDataSalesorder($delivery_order_id){
        // DB::enableQueryLog();

        $data = SalesOrderDetail::leftJoin('sales_order', 'sales_order_detail.sales_order_id', '=', 'sales_order.id')
                                ->leftJoin('delivery_order_detail', 'delivery_order_detail.sales_order_id', '=', 'sales_order.id')
                                ->leftJoin('sale_return', 'sale_return.delivery_order_id', '=', 'delivery_order_detail.id')
                                ->leftJoin('sale_return_detail', function ($join) {
                                    $join->on('sale_return_detail.sale_return_id', '=', 'sale_return.id');
                                    $join->on('sale_return_detail.product_id', '=', 'sales_order_detail.product_id');
                                })
                                ->where('sale_return.delivery_order_id', $delivery_order_id)
                                ->where('sale_return.status', SaleReturn::STATUS['ACC'])
                                ->groupBy('sales_order_detail.product_id')
                                ->selectRaw('sales_order_detail.product_id, sales_order_detail.quantity - COALESCE(SUM(sale_return_detail.`quantity`), 0) AS Sisa, sales_order_detail.id')
                                ->get();

        // DD(count($data));
        if(count($data) == 0){
            $data = SalesOrderDetail::leftJoin('sales_order', 'sales_order_detail.sales_order_id', '=', 'sales_order.id')
                                ->leftJoin('delivery_order_detail', 'delivery_order_detail.sales_order_id', '=', 'sales_order.id')
                                ->where('delivery_order_detail.id', $delivery_order_id)
                                ->groupBy('sales_order_detail.product_id')
                                ->selectRaw('sales_order_detail.product_id, sales_order_detail.quantity AS Sisa, sales_order_detail.id')
                                ->get();
        }
        // DD(DB::getQueryLog());

        // DD($data);
        return $data;
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:sale_return,code',
                'delivery_order' => 'required',
                'warehouse_reparation' => 'required|integer',
                'return_date' => 'nullable',
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
                $sale_return = new SaleReturn;

                $sale_return->code = $request->code;
                $sale_return->delivery_order_id = $request->delivery_order;
                $sale_return->warehouse_reparation_id = $request->warehouse_reparation;
                $sale_return->return_date = $request->return_date;
                $sale_return->status = SaleReturn::STATUS['ACTIVE'];

                if ($sale_return->save()) {
                    if ($request->sku) {
                        foreach ($request->sku as $key => $value) {
                            if ($request->sku[$key] && $request->quantity[$key]) {
                                $sale_return_detail = new SaleReturnDetail;
                                $sale_return_detail->sale_return_id = $sale_return->id;
                                $sale_return_detail->product_id = $request->sku[$key];
                                $sale_return_detail->quantity = $request->quantity[$key];
                                $sale_return_detail->hpp = $request->hpp[$key];
                                $sale_return_detail->price = $request->price[$key];
                                $sale_return_detail->description = $request->description[$key];
                                $sale_return_detail->save();
                            }
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.sale.sale_return.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if (!Auth::guard('superuser')->user()->can('sale return-show')) {
            return abort(403);
        }

        $data['sale_return'] = SaleReturn::findOrFail($id);

        return view('superuser.sale.sale_return.show', $data);
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('sale return-edit')) {
            return abort(403);
        }

        $data['sale_return'] = SaleReturn::findOrFail($id);

        return view('superuser.sale.sale_return.edit', $data);
    }

    public function update(Request $request, $id)
    {
        // DD($request->ajax());
        if ($request->ajax()) {
            $sale_return = SaleReturn::find($id);

            // DD($sale_return);
            if ($sale_return == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:sale_return,code,'. $sale_return->id,
                // 'delivery_order' => 'required',
                // 'warehouse_reparation' => 'required|integer',
                // 'return_date' => 'nullable',
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
                // $sale_return = new SaleReturn;

                $sale_return->code = $request->code;
                // $sale_return->delivery_order_id = $request->delivery_order;
                // $sale_return->warehouse_reparation_id = $request->warehouse_reparation;
                // $sale_return->return_date = $request->return_date;
                // $sale_return->status = SaleReturn::STATUS['ACTIVE'];

                if ($sale_return->save()) {
                    SaleReturnDetail::where('sale_return_id', $sale_return->id)->delete();
                    if ($request->sku) {
                        foreach ($request->sku as $key => $value) {
                            if ($request->sku[$key] && $request->quantity[$key]) {
                                $sale_return_detail = new SaleReturnDetail;
                                $sale_return_detail->sale_return_id = $sale_return->id;
                                $sale_return_detail->product_id = $request->sku[$key];
                                $sale_return_detail->quantity = $request->quantity[$key];
                                $sale_return_detail->hpp = $request->hpp[$key];
                                $sale_return_detail->price = $request->price[$key];
                                $sale_return_detail->description = $request->description[$key];
                                $sale_return_detail->save();
                            }
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.sale.sale_return.index');

                    return $this->response(200, $response);
                }
            }
        }
    }
    
    public function setHPP($sales_order_detail_id, $sisa){
        $dataFIFO = SalesOrderFIFO::where('detail_id', $sales_order_detail_id)->orderBy('urutan', 'desc')->get();

        $data = array();

        foreach($dataFIFO as $fifo){
            if($sisa <= $fifo->qty){
                $fifo->qty = $sisa;

                array_push($data, $fifo);
                break;
            } else {
                array_push($data, $fifo);

                $sisa -= $fifo->qty;
            }
        }

        return $data;
    }

    public function acc(Request $request, $id)
    {
        // if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('sale return-acc')) {
                return abort(403);
            }

            $sale_return = SaleReturn::find($id);

            if ($sale_return === null) {
                abort(404);
            }

            DB::beginTransaction();
            // try {
                $failed = '';
                $superuser = Auth::guard('superuser')->user();

                $return_transaction_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'return_transaction_debet')->first();

                $return_transaction_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'return_transaction_credit')->first();

                $return_hpp_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'return_hpp_debet')->first();

                $return_hpp_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'return_hpp_credit')->first();

                if ($return_transaction_debet == null or $return_transaction_debet->coa_id == null or $return_transaction_credit == null or $return_transaction_credit->coa_id == null or $return_hpp_debet == null or $return_hpp_debet->coa_id == null or $return_hpp_credit == null or $return_hpp_credit->coa_id == null) {
                    $failed = 'Finance Setting is not set, please contact your Administrator!';
                } else {
                    $empty_hpp = false;
                    $price_total = 0;
                    $hpp_total = 0;


                    $SalesOrder = $this->getDataSalesorder($sale_return->delivery_order_id);

                    // DD($SalesOrder);

                    foreach ($sale_return->sale_return_details as $detail) {

                        if($detail->product->non_stock == '1') {
                            continue;
                        }

                        $dataSales = SalesOrder::find($sale_return->delivery_order->sales_order->id);

                        // Checking Qty yg sudah pernah masuk Sale Return
                        if(!$failed){
                            $dataSaleReturn = SaleReturn::where('delivery_order_id', $sale_return->delivery_order_id)
                                                        ->where('status', SaleReturn::STATUS['ACC'])
                                                        ->get();

                            $qty = $detail->quantity;
                            $product = 0;


                            foreach($dataSaleReturn as $item)
                            {
                                foreach($item->sale_return_details as $val){
                                    if($val->product_id == $detail->product_id){
                                        $qty += $val->quantity;
                                    }
                                }
                            }

                            foreach($dataSales->sales_order_details as $item){
                                if($item->product_id == $detail->product_id){
                                    if(is_null($detail->hpp)){
                                        $sale_return_detail = SaleReturnDetail::find($detail->id);

                                        $sale_return_detail->hpp = $item->hpp_total / $item->quantity;
                                        $sale_return_detail->save();

                                        $detail->hpp =  $sale_return_detail->hpp;
                                    }
                                    
                                    if($item->quantity < $qty){
                                        $product = $item->product_id;
                                    }
                                }
                            }

                            if($product){
                                $product = Product::find($product);

                                $failed = "SKU ". $product->name ." lebih dari Qty Sales Order, Cek pada Transaksi Sale Return lain";
                            }

                            // DD($SalesOrder);
                            foreach($SalesOrder as $item){
                                // DD($item->product_id, $detail->product_id);
                                if($item->product_id == $detail->product_id){
                                    $dataHPP = $this->setHPP($item->id, $item->Sisa);
                                    // DD($dataHPP);
                                    $item->Sisa -= $detail->quantity;

                                    $qty = $detail->quantity;
                                    $detail->hpp = 0;
                                    for($i = count($dataHPP) - 1; $i >= 0; $i--){
                                        $fifo = $dataHPP[$i];

                                        $hpp = new SaleReturnFIFO;

                                        if($qty <= $fifo->qty){
                                            $hpp->qty = $qty;
                                            $hpp->detail_id = $detail->id;
                                            $hpp->hpp = $fifo->hpp;
                                            $hpp->tgl = $fifo->tgl;
                                            $hpp->urutan = $fifo->urutan;
                                            $hpp->save();

                                            $detail->hpp += $fifo->hpp * $qty;

                                            break;
                                        } else {
                                            $hpp->qty = $fifo->qty;
                                            $hpp->detail_id = $detail->id;
                                            $hpp->hpp = $fifo->hpp;
                                            $hpp->tgl = $fifo->tgl;
                                            $hpp->urutan = $fifo->urutan;
                                            $hpp->save();

                                            $qty -= $fifo->qty;
                                            $detail->hpp += $fifo->hpp * $fifo->qty;
                                        }
                                        
                                        // DD($detail->hpp);
                                    }

                                    $sale_return_detail = SaleReturnDetail::find($detail->id);
                                    $sale_return_detail->hpp = $detail->hpp;
                                    $sale_return_detail->save();
                                }
                            }
                        }



                        if(!$failed){
                            if ($detail->hpp == null) {
                                $empty_hpp = true;
                                break;
                            }

                            // $hpp = new Hpp;
                            // $hpp->type = $superuser->type;
                            // $hpp->branch_office_id = $superuser->branch_office_id;
                            // $hpp->product_id = $detail->product_id;
                            // $hpp->quantity = $detail->quantity;
                            // $hpp->price = $detail->hpp;
                            // $hpp->save();

                            $price_total = $price_total + ($detail->quantity * $detail->price);
                            $hpp_total = $hpp_total + $detail->hpp;
                        }
                    }

                    // DD($SalesOrder);

                    if(!$failed){
                        $setDiskon = true;

                        foreach($SalesOrder as $item){
                            if($item->Sisa > 0){
                                $setDiskon = false;
                            }
                        }

                        // DD($setDiskon);

                        if($setDiskon){
                            if($sale_return->diskon == 0){
                                $sale_return->diskon = $sale_return->delivery_order->sales_order->discount;
                                $sale_return->save();
                            }
                        }
                    }

                    if(!$failed){
                        if ($empty_hpp) {
                            $failed = 'HPP Reference invalid!';
                            DB::rollback();
                        } else {
                            // ADD JOURNAL
                            // TRANSACTION
                            $journal = new Journal;
                            $journal->coa_id = $return_transaction_debet->coa_id;
                            $journal->name = Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code;
                            $journal->debet = $price_total;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();

                            $journal = new Journal;
                            $journal->coa_id = $return_transaction_credit->coa_id;
                            $journal->name = Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code;
                            $journal->credit = $price_total - (($sale_return->diskon > 0 ) ? $sale_return->diskon : 0);
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();

                            // diskon
                            if($sale_return->diskon > 0){
                                $diskon_coa = null;

                                if ($sale_return->delivery_order->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Shopee']) {

                                    $diskon_shopee = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'diskon_shopee')->first();
                                    if($diskon_shopee != null AND $diskon_shopee->coa_id != null) {
                                        $diskon_coa = $diskon_shopee->coa_id;
                                    }
                                } elseif ($sale_return->delivery_order->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Tokopedia']) {

                                    $diskon_tokopedia = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'diskon_tokopedia')->first();
                                    if($diskon_tokopedia != null AND $diskon_tokopedia->coa_id != null) {
                                        $diskon_coa = $diskon_tokopedia->coa_id;
                                    }
                                } elseif ($sale_return->delivery_order->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Lazada']) {

                                    $diskon_lazada = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'diskon_lazada')->first();
                                    if($diskon_lazada != null AND $diskon_lazada->coa_id != null) {
                                        $diskon_coa = $diskon_lazada->coa_id;
                                    }
                                } elseif ($sale_return->delivery_order->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Blibli']) {

                                    $diskon_blibli = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'diskon_blibli')->first();
                                    if($diskon_blibli != null AND $diskon_blibli->coa_id != null) {
                                        $diskon_coa = $diskon_blibli->coa_id;
                                    }
                                } elseif ($sale_return->delivery_order->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Non Marketplace']) {

                                    $diskon_offline = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'diskon_offline')->first();
                                    if($diskon_offline != null AND $diskon_offline->coa_id != null) {
                                        $diskon_coa = $diskon_offline->coa_id;
                                    }
                                } elseif ($sale_return->delivery_order->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Tiktok']) {
                                    $diskon_tiktok = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'diskon_tiktok')->first();
                                    if($diskon_tiktok != null AND $diskon_tiktok->coa_id != null) {
                                        $diskon_coa = $diskon_tiktok->coa_id;
                                    }
                                }

                                $journal = new Journal;
                                $journal->coa_id = $diskon_coa;
                                $journal->name = Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code;
                                $journal->credit = $sale_return->diskon;
                                $journal->status = Journal::STATUS['UNPOST'];
                                $journal->save();
                            }

                            // HPP
                            $journal = new Journal;
                            $journal->coa_id = $return_hpp_debet->coa_id;
                            $journal->name = Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code;
                            $journal->debet = $hpp_total;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();

                            $journal = new Journal;
                            $journal->coa_id = $return_hpp_credit->coa_id;
                            $journal->name = Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code;
                            $journal->credit = $hpp_total;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();
                        }
                    }
                }

                if ($failed) {
                    $response['failed'] = $failed;

                    return $this->response(200, $response);
                }

                $sale_return->status = SaleReturn::STATUS['ACC'];
                if ($sale_return->save()) {
                    DB::commit();
                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
        //     } catch (\Exception $e) {
        //         DB::rollback();
        //         // dd($e);
        //         $response['redirect_to'] = '#datatable';
        //         return $this->response(400, $response);
        //     }
        // }
    }
    
    public function cancel_approve(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('sale return-acc')) {
                return abort(403);
            }

            $sale_return = SaleReturn::find($id);

            if ($sale_return === null) {
                abort(404);
            }

            DB::beginTransaction();
            $failed = '';
            try{
                $superuser = Auth::guard('superuser')->user();
                $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

                if($journal_periode) {
                    $min_date = Carbon::parse( $journal_periode->to_date );
                    if($sale_return->updated_at <= $min_date ) {
                        $response['failed'] = 'Transaksi sudah terposting';
                        return $this->response(200, $response);
                    }
                }
                
                foreach($sale_return->sale_return_details as $detail){
                    $recondition_detail = ReconditionDetail::where('sale_return_detail_id', $detail->id)->first();

                    if($recondition_detail){
                        $failed = "SKU pada Sale Return sudah masuk dalam transaksi Recondition";
                    }

                    $data = SaleReturnFIFO::where('detail_id', $detail->id)->get();

                    foreach($data as $item){
                        SaleReturnFIFO::find($item->id)->delete();
                    }
                }

                if ($failed) {
                    $response['failed'] = $failed;
                    DB::rollback();

                    return $this->response(200, $response);
                }

                $jurnals = Journal::where('transaction_type', Journal::TRANSACTION_TYPE['SALE_RETURN'])
                                  ->where('transaction_id', $sale_return->id)
                                  ->get();

                if($jurnals->isEmpty()){
                    $jurnals = Journal::where('name', Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code)->get();
                }

                foreach($jurnals as $jurnal){
                    $data = Journal::find($jurnal->id);

                    $data->delete();
                }

                $sale_return->status = SaleReturn::STATUS['ACTIVE'];
                if ($sale_return->save()) {
                    DB::commit();
                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                // dd($e);
                $response['redirect_to'] = '#datatable';
                return $this->response(400, $response);
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('sale return-delete')) {
                return abort(403);
            }

            $sale_return = SaleReturn::find($id);

            if ($sale_return === null) {
                abort(404);
            }

            $sale_return->status = SaleReturn::STATUS['DELETED'];

            if ($sale_return->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if(!Auth::guard('superuser')->user()->can('sale return-manage')) {
            return abort(403);
        }

        // if (is_string($data)) {
        //     $data = json_decode($data);
        // }

        if ($id == NULL) {
            abort(404);
        }

        $data['data'] = SaleReturn::findOrFail($id);

        $pdf = DomPDF::loadView('superuser.sale.sale_return.pdf', $data);
        $pdf->setPaper('a5', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }
}
