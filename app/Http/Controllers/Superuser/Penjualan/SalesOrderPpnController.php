<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\DeliveryOrderMutationItem;
use App\Entities\Finance\Invoicing;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Packaging;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Product;
use App\Entities\Master\Sales;
use App\Entities\Master\Vendor;
use App\Entities\Master\Warehouse;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use App\Helper\CustomHelper;
use Illuminate\Validation\Rule;
use Auth;
use Validator;
use DB;
use PDF;
use COM;

class SalesOrderPpnController extends Controller
{
    public function search_sku(Request $request)
    {
        $products = Product::where('master_products.name', 'LIKE', '%'.$request->input('q', '').'%')
            ->where('master_products.status', Product::STATUS['ACTIVE'])
            ->leftJoin('master_product_categories', 'master_products.category_id', '=', 'master_product_categories.id')
            ->leftJoin('master_packaging', 'master_product_categories.packaging_id', '=', 'master_packaging.id')
            ->get([
                'master_products.id as id',
                'master_products.code as text', 
                'master_products.name as productName', 
                'master_products.status as productStatus', 
                'master_products.selling_price as productPrice', 
                'master_packaging.id as packId', 
                'master_packaging.pack_value as packValue', 
                'master_packaging.pack_name as packagingName',
                'master_packaging.packaging_packing as packagingKemasan', 
                'master_product_categories.name as catName'
            ]);
        return ['results' => $products];
    }

    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['so_ppn'] = SalesOrder::where('type_so', 'ppn')->get();

        return view('superuser.penjualan.sales_order_ppn.index', $data);
    }

    public function create(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $member = CustomerOtherAddress::get();
        $sales = Sales::where('is_active', 1)->get();
        $ekspedisi = Vendor::where('type', 1)->get();
        $warehouse = Warehouse::get();
        $brand = BrandLokal::get();
        $product_category = ProductCategory::get();
        $type_transaction = SalesOrder::TYPE_TRANSACTION;


        $data = [
            // 'customer' => $customer,
            'member' => $member,
            'sales' => $sales,
            'ekspedisi' => $ekspedisi,
            'warehouse' => $warehouse,
            'brand' => $brand,
            'product_category' => $product_category,
            'type_transaction' => $type_transaction,
        ];

        return view('superuser.penjualan.sales_order_ppn.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();
            try{

                $errors = [];
                $validator = Validator::make($request->all(), [
                    // 'code' => 'required|string|unique:sales_order,code',
                    'sales_senior_id' => 'required|string',
                    'sales_id' => 'required|string',
                    'origin_warehouse_id' => 'required|string',
                    'customer_other_address_id' => 'required|string',
                    'ekspedisi_id' => 'required|string',
                    'type_transaction' => 'required|string',
                    'idr_rate' => 'required|string',
                    'product' => 'required|array',
                    'product.*' => 'required|string|distinct',
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
                    $sales_order = new SalesOrder;
    
                    $sales_order->code = CodeRepo::generatePPN();
                    $sales_order->customer_other_address_id = $request->customer_other_address_id;
                    $sales_order->customer_id = $request->customer_id;
                    $sales_order->sales_senior_id = $request->sales_senior_id;
                    $sales_order->sales_id = $request->sales_id;
                    $sales_order->origin_warehouse_id = $request->origin_warehouse_id;
                    $sales_order->vendor_id = $request->ekspedisi_id;
                    $sales_order->type_transaction = $request->type_transaction;
                    $sales_order->idr_rate = $request->idr_rate;
                    $sales_order->type_so = 'ppn';
                    $sales_order->so_for = 1;
                    $sales_order->note = $request->note;
                    $sales_order->created_by = Auth::id();
                    $sales_order->status = $request->ajukankelanjutan == 1 ? 2 : 1;
                    $sales_order->condition = 1;
                    $sales_order->payment_status = 0;
                    $sales_order->count_rev = 0;
    
                    if ($sales_order->save()) {
                        $packing_order = new PackingOrder;
                        $packing_order->code = CodeRepo::generatePO();
                        $packing_order->do_code = CodeRepo::generateDO();
                        $packing_order->so_id = $sales_order->id;
                        $packing_order->warehouse_id = $sales_order->origin_warehouse_id;
                        $packing_order->customer_id = $sales_order->customer_id;
                        $packing_order->customer_other_address_id = $sales_order->customer_other_address_id;
                        $packing_order->vendor_id = $sales_order->vendor_id;
                        $packing_order->idr_rate = $sales_order->idr_rate;
                        $packing_order->type_transaction = $sales_order->type_transaction;
                        $packing_order->count_cancel = 0;
                        $packing_order->other_address = 0 ?? Null;
                        $packing_order->note = $company->note ?? null;
                        $packing_order->status = 2;
                        $packing_order->created_by = Auth::id();
                        $packing_order->save();
    
                        $packing_order_detail = new PackingOrderDetail;
                        $packing_order_detail->do_id = $packing_order->id;
                        $packing_order_detail->discount_1 = $request->disc_percent;
                        $packing_order_detail->discount_1_idr = $request->disc_percent_idr;
                        $packing_order_detail->discount_2 = $request->disc_pack;
                        $packing_order_detail->discount_2_idr = $request->disc_pack_idr;
                        $packing_order_detail->discount_idr = $request->discount_idr;
                        $packing_order_detail->ppn = $request->tax_ammount_idr;
                        $packing_order_detail->voucher_idr = $request->voucher_idr;
                        $packing_order_detail->purchase_total_idr = $request->subtotal;
                        $packing_order_detail->delivery_cost_idr = $request->delivery_cost;
                        $packing_order_detail->other_cost_idr = 0;
                        $packing_order_detail->grand_total_idr = $request->grand_total_idr;
                        $packing_order_detail->terbilang = CustomHelper::terbilang($request->grand_total_idr);
                        $packing_order_detail->created_by = Auth::id();
                        $packing_order_detail->save();
    
                        if($request->product) {
                            foreach($request->product as $key => $value){
                                if($request->product[$key]) {
                                    if(empty($request->product[$key])){
                                        $errors = 'No item input';
                                    }
    
                                    $sales_order_detail = new SalesOrderItem;
                                    $sales_order_detail->so_id = $sales_order->id;
                                    $sales_order_detail->product_id = $request->product[$key];
                                    $sales_order_detail->qty = $request->qty[$key];
                                    $sales_order_detail->packaging_id = $request->packaging_id[$key];
                                    $sales_order_detail->free_product = 0;
                                    $sales_order_detail->created_by = Auth::id();
                                    $sales_order_detail->save();
                                }
    
                                $so_item_id = $sales_order_detail->id;
                                $price = $request->price[$key];
                                $qty = $request->qty[$key];
                                $usd_disc = $request->disc_cash[$key];
                                $percent_disc = 0;
                                $total_discount = 0;
    
                                if($qty > 0){
                                    $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc/100))) * $qty);
                                    $data[] = [
                                        'do_id' => $packing_order->id,
                                        'product_id' => $request->product[$key],
                                        'so_item_id' => $so_item_id,
                                        'packaging_id' => $request->packaging_id[$key],
                                        'qty' => $qty,
                                        'price' => $price,
                                        'usd_disc' => $usd_disc,
                                        'percent_disc' => $percent_disc,
                                        'total_disc' => $total_disc,
                                        'total' => floatval($qty * $price) - $total_disc,
                                        'created_by' => Auth::id(),
                                    ];
                                }
                            }
    
                            foreach ($data as $key => $value) {
                                $insert = PackingOrderItem::create($data[$key]);
                            }

                            // Cetak Invoice
                            if(empty($packing_order->invoicing))
                                {
                                    $data = [
                                        'code' => CodeRepo::generateInvoicing($packing_order->do_code),
                                        'do_id' => $packing_order->id,
                                        'customer_other_address_id' => $packing_order->customer_other_address_id,
                                        'grand_total_idr' => $packing_order_detail->grand_total_idr,
                                        'created_by' => Auth::id()
                                    ];
    
                                    $insertInv = Invoicing::create($data);
                                }
                        }

                        DB::commit();
                        if($errors) {
                            $response['notification'] = [
                                'alert' => 'block',
                                'type' => 'alert-danger',
                                'header' => 'Error',
                                'content' => $errors,
                            ];
        
                            return $this->response(400, $response);
                        } else {
                            $response['notification'] = [
                                'alert' => 'notify',
                                'type' => 'success',
                                'content' => 'Success',
                            ];
                
                            $response['redirect_to'] = route('superuser.penjualan.sales_order_ppn.index');
                            return $this->response(200, $response);
                        }
                    }
                }
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
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

    public function edit($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_edit == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['sales_order'] = SalesOrder::findOrFail($id);
        
        $data['do'] = PackingOrder::where('so_id', $id)->first();
        $data['sales'] = Sales::where('is_active', 1)->get();
        $data['warehouse'] = Warehouse::get();
        $data['ekspedisi'] = Vendor::where('type', 1)->get();
        $data['brand'] = BrandLokal::get();
        $data['product_category'] = ProductCategory::get();
        $data['member'] = CustomerOtherAddress::get();
        $data['type_transaction'] = SalesOrder::TYPE_TRANSACTION;

        return view('superuser.penjualan.sales_order_ppn.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $sales_order = SalesOrder::find($id);

            if ($sales_order == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'customer_other_address_id' => 'required|string',
                'customer_id' => 'required|string',
                'origin_warehouse_id' => 'required|string',
                'sales_senior_id' => 'required|string',
                'sales_id' => 'required|string',
                'idr_rate' => 'required|string',
                'type_transaction' => 'required|string',
                'ekspedisi_id' => 'required|string',
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
                $sales_order->sales_senior_id = $request->sales_senior_id;
                $sales_order->sales_id = $request->sales_id;
                $sales_order->origin_warehouse_id = $request->origin_warehouse_id;
                $sales_order->customer_other_address_id = $request->customer_other_address_id;
                $sales_order->customer_id = $request->customer_id;
                $sales_order->idr_rate = $request->idr_rate;
                $sales_order->vendor_id = $request->ekspedisi_id;
                $sales_order->type_transaction = $request->type_transaction;
                $sales_order->condition = 1;
                $sales_order->payment_status = 0;
                $sales_order->type_so = 'ppn';
                $sales_order->updated_by = Auth::id();

                if ($sales_order->save()) {

                    $packing_order = PackingOrder::where('so_id', $sales_order->id)->first();

                    $packing_order->warehouse_id = $sales_order->origin_warehouse_id;
                    $packing_order->customer_id = $sales_order->customer_id;
                    $packing_order->customer_other_address_id = $sales_order->customer_other_address_id;
                    $packing_order->vendor_id = $sales_order->vendor_id;
                    $packing_order->idr_rate = $sales_order->idr_rate;
                    $packing_order->type_transaction = $sales_order->type_transaction;
                    $packing_order->count_cancel = 1;
                    $packing_order->note = $request->note;
                    $packing_order->save();

                    $packing_order_detail = PackingOrderDetail::where('do_id', $packing_order->id)->first();

                    $packing_order_detail->discount_1 = $request->disc_percent;
                    $packing_order_detail->discount_1_idr = $request->disc_percent_idr;
                    $packing_order_detail->discount_2 = $request->disc_pack;
                    $packing_order_detail->discount_2_idr = $request->disc_pack_idr;
                    $packing_order_detail->discount_idr = $request->discount_idr;
                    $packing_order_detail->ppn = $request->tax_ammount_idr;
                    $packing_order_detail->voucher_idr = $request->voucher_idr;
                    $packing_order_detail->delivery_cost_idr = $request->delivery_cost;
                    $packing_order_detail->purchase_total_idr = $request->subtotal;
                    $packing_order_detail->grand_total_idr = $request->grand_total_idr;
                    $packing_order_detail->terbilang = CustomHelper::terbilang($request->grand_total_idr);
                    $packing_order_detail->updated_by = Auth::id();
                    $packing_order_detail->save();

                    if($request->ids_delete) {
                        $pieces = explode(",",$request->ids_delete);
                        foreach($pieces as $piece){
                            SalesOrderitem::where('id', $piece)->delete();
                        }
                    }

                    if($request->product) {
                        foreach($request->product as $key => $value){
                            if($request->product[$key]) {

                                if($request->edit[$key]) {
                                    $sales_order_item = SalesOrderitem::find($request->edit[$key]);

                                    $sales_order_item->product_id = $request->product[$key];
                                    $sales_order_item->qty = $request->qty[$key];
                                    $sales_order_item->packaging_id = $request->packaging_id[$key];
                                    $sales_order_item->free_product = 0;
                                    $sales_order_item->updated_by = Auth::id();
                                    $sales_order_item->save();
                                } else {
                                    $sales_order_item = new SalesOrderitem;
                                    $sales_order_item->so_id = $sales_order->id;
                                    $sales_order_item->product_id = $request->product[$key];
                                    $sales_order_item->qty = $request->qty[$key];
                                    $sales_order_item->packaging_id = $request->packaging_id[$key];
                                    $sales_order_item->free_product = 0;
                                    $sales_order_item->created_by = Auth::id();
                                    $sales_order_item->save();
                                }
                            }

                            $price = $request->price[$key];
                            $qty = $request->qty[$key];
                            $usd_disc = $request->disc_cash[$key];
                            $percent_disc = 0;
                            $total_discount = 0;

                            if($qty > 0){
                                $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc/100))) * $qty);
                                $data[] = [
                                    'product_id' => $request->product[$key],
                                    'price' => $price,
                                    'qty' => $qty,
                                    'usd_disc' => $usd_disc,
                                    'packaging_id' => $request->packaging_id[$key],
                                    'percent_disc' => $percent_disc,
                                    'total_disc' => $total_disc,
                                    'total' => floatval($qty * $price) - $total_disc,
                                    'updated_by' => Auth::id(),
                                ];
                            }
                        }

                        foreach ($data as $key => $value) {
                            $update_item = PackingOrderItem::where('do_id', $packing_order->id)->update($data[$key]);
                        }

                        // update grand total invoice
                        if(empty($packing_order->invoicing)){
                            $data = [
                                'code' => CodeRepo::generateInvoicing($packing_order->do_code),
                                'do_id' => $packing_order->id,
                                'customer_other_address_id' => $packing_order->customer_other_address_id,
                                'grand_total_idr' => $packing_order_detail->grand_total_idr,
                                'created_by' => Auth::id()
                            ];
                            $insertInv = Invoicing::create($data);
                        }else{
                            $data = [
                                'customer_other_address_id' => $packing_order->customer_other_address_id,
                                'grand_total_idr' => $packing_order_detail->grand_total_idr,
                                'updated_by' => Auth::id()
                            ];
                            $update_inv = Invoicing::where('do_id', $packing_order->id)->update($data);
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.penjualan.sales_order_ppn.index');

                    return $this->response(200, $response);
                }
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

        $data['sales_order'] = SalesOrder::findOrFail($id);

        $data['table'] = SalesOrder::where('penjualan_so.id', $id)
                        ->leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
                        ->leftJoin('penjualan_do', 'penjualan_so.id', '=', 'penjualan_do.so_id')
                        ->leftjoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
                        ->leftJoin('penjualan_do_item', 'penjualan_do.id', '=', 'penjualan_do_item.do_id')
                        ->leftJoin('master_products', 'penjualan_do_item.product_id', '=', 'master_products.id')
                        ->leftJoin('master_product_categories', 'master_products.category_id', '=', 'master_product_categories.id')
                        ->leftJoin('master_packaging', 'master_product_categories.packaging_id', '=', 'master_packaging.id')
                        ->select(
                            'master_products.code as productCode',
                            'master_products.name as productName',
                            'master_product_categories.name as categoryName',
                            'penjualan_do_item.qty as doQty',
                            'master_packaging.pack_name as packagingName',
                            'penjualan_do_item.usd_disc as doUsdDisc',
                            'master_products.selling_price as productPrice',
                            'penjualan_so.idr_rate as soKurs',
                            'penjualan_do_details.discount_1 as discPercent',
                            'penjualan_do_details.discount_1_idr as discPercentIdr',
                            'penjualan_do_details.discount_2 as discPack',
                            'penjualan_do_details.discount_2_idr as discPackIdr',
                            'penjualan_do_details.discount_idr as discIdr',
                            'penjualan_do_details.ppn as taxAmmount',
                            'penjualan_do_details.voucher_idr as voucherIdr',
                            'penjualan_do_details.delivery_cost_idr as ongkirIdr',
                            'penjualan_do_details.grand_total_idr as grandTotalIdr',
                            'penjualan_do_details.purchase_total_idr as doPurchaseTotal',
                        )
                        ->get();

        $data['sales'] = Sales::where('is_active', 1)->get();
        $data['warehouse'] = Warehouse::get();
        $data['ekspedisi'] = Vendor::where('type', 1)->get();
        $data['brand'] = BrandLokal::get();
        $data['product_category'] = ProductCategory::get();
        $data['member'] = CustomerOtherAddress::get();

        // dd($data['table']);

        return view('superuser.penjualan.sales_order_ppn.show', $data);
    }

    public function lanjutkan(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();

        try {
            
            $sales_order  = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            if($sales_order->type_transaction == 1){
                if($sales_order->payment_status == 0){
                    return redirect()->route('superuser.penjualan.sales_order_ppn.index')->with('error','<a href="'.route('superuser.penjualan.sales_order_ppn.show', $sales_order->id).'">'.$sales_order->code.'</a> : There is no payment can not be continued!');
                }elseif($sales_order->payment_status == 1){
                    $sales_order->status = 4;
                    $sales_order->updated_by = Auth::id();
                    $sales_order->save();

                    DB::commit();
                    return redirect()->back()->with('success','<a href="'.route('superuser.penjualan.sales_order_ppn.show', $sales_order->id).'">'.$sales_order->code.'</a> : SO successfully to the next proceed!');
                }
            }elseif($sales_order->type_transaction == 2){
                $sales_order->status = 4;
                $sales_order->updated_by = Auth::id();
                $sales_order->save();

                DB::commit();
                return redirect()->back()->with('success','Sales Order berhasil diajukan untuk dilanjutkan');
            }

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function ajax_customer_detail(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                $result = CustomerOtherAddress::where('id',$post["id"])->first();
                $data_json["IsError"] = FALSE;
                $data_json["Data"] = $result;
                goto ResultData;

            }catch(\Throwable $e){

                // dd($e);
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultData;
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

    public function delete(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            
            $get_so = SalesOrder::where('id', $post["id"])->first();

            $delivery_order = PackingOrder::where('so_id', $get_so->id)->first();

            if($delivery_order){
                if($delivery_order->status == 3){
                    return redirect()->back()->with('error','<a href="'.route('superuser.penjualan.sales_order_ppn.show', $get_so->id).'">'.$get_so->code.'</a> : Gagal menghapus. Item SO ini sudah digunakan di Packing Order / Delivery Order Mutation');
                }
            }

            if($delivery_order){
                $id_do = $delivery_order->id;

                $delivery_order_item = PackingOrderItem::where('do_id', $id_do)->first();
                $delivery_order_cost = PackingOrderDetail::where('do_id', $id_do)->first();
                
                if(count($delivery_order->do_detail) <= 1){
                    $delivery_order_item->delete();
                    $delivery_order_cost->delete();
                    $delivery_order->delete();
                }else{
                    $update_do = PackingOrder::where('id', $id_do)->update([
                        'deleted_by' => Auth::id(),
                        'updated_by' =>  Auth::id(),
                    ]);

                    $update_do_item = PackingOrderItem::where('do_id', $id_do)->update([
                        'deleted_by' => Auth::id(),
                        'updated_by' =>  Auth::id(),
                    ]);

                    $update_do_cost = PackingOrderDetail::where('do_id', $id_do)->update([
                        'deleted_by' => Auth::id(),
                        'updated_by' =>  Auth::id(),
                    ]);

                    // destroy
                    $destroy_do = PackingOrder::where('id', $id_do)->delete();
                    $destroy_do_item = PackingOrderItem::where('do_id', $id_do)->delete();
                    $destroy_do_cost = PackingOrderDetail::where('do_id', $id_do)->delete();
                }
            }

            $update_so = SalesOrder::where('id', $get_so->id)->update([
                'deleted_by' => Auth::id(),
                'condition' => 0,
            ]);

            $destroy_so = SalesOrder::where('id', $get_so->id)->delete();
            $destroy_so_item = SalesOrderItem::where('so_id', $get_so->id)->delete();
                
            DB::commit();
            return redirect()->back()->with('success','<a href="'.route('superuser.penjualan.sales_order_ppn.show', $get_so->id).'">'.$get_so->code.'</a> : Sales order data successfully deleted!');
        }catch(\Throwable $e){
            // dd($e);
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
}