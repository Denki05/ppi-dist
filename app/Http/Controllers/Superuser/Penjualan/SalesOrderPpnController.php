<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\SoProforma;
use App\Entities\Penjualan\SoProformaDetail;
use App\Entities\Penjualan\DeliveryOrderMutationItem;
use App\Entities\Finance\Invoicing;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerCategory;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Company;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Packaging;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Sales;
use App\Entities\Master\Ekspedisi;
use App\Entities\Master\Vendor;
use App\Entities\Setting\UserMenu;
use App\Helper\CustomHelper;
use App\Repositories\CodeRepo;
use App\Helper\LogActivity;
use Auth;
use DB;
use Validator;

class SalesOrderPpnController extends Controller
{
    public function __construct(){
        $this->view = "superuser.penjualan.sales_order_ppn.";
        $this->route = "superuser.penjualan.sales_order_ppn";
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

    public function index_ppn_awal(Request $request, $step = 1)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $search = $request->input('search');
        $so_for = $request->input('so_for');
        $customer_other_address_id = $request->input('customer_other_address_id');
        $status_so = $request->input('status_so');

        $table = SalesOrder::where(function($query2) use($search,$so_for,$step){
                                if(!empty($step)){
                                    if ($step === 1) { // SO awal
                                        $query2->whereIn('status', [1, 2, 3, 4, 5]);
                                        $query2->where('so_for', 1);
                                    }
                                    // } else if ($step === 2) { // SO lanjutan
                                    //     $query2->whereIn('status', [2, 4]);
                                    //     $query2->where('so_for', 1);
                                    // } else if ($step === 9) { // SO mutasi
                                    //     $query2->where('so_for', 2);
                                    // }
                                }
                            })
                            ->where(function($query2) use($customer_other_address_id, $status_so){
    							if(!empty($customer_other_address_id)){
    								$query2->whereHas('member', function($query3) use($customer_other_address_id){
    									$query3->where('customer_other_address_id', $customer_other_address_id);
    								});
    							}
    							if(!empty($status_so)){
    								$query2->where(function($query3) use($status_so){
                                        $query3->where('status', $status_so);
    								});
    							}
    						})
                            ->where('type_so', 'ppn')
                            ->where('so_indent', SalesOrder::INDENT['NO'])
                            ->orderBy('id','DESC')
                            ->get();

        $customers = Customer::get();
        $other_address = CustomerOtherAddress::get();
        $brand = BrandLokal::get();
        $packing_order = PackingOrder::get();

        $data = [
            'customers' => $customers,
            'other_address' => $other_address,
            'packing_order' => $packing_order,
            'brand' => $brand,
            'step' => $step,
            'table' => $table,
            'step_txt' => SalesOrder::STEP[$step],
        ];

        return view($this->view."index_awal_ppn", $data);
    }

    public function index_ppn_lanjutan(Request $request, $step = 2)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $search = $request->input('search');
        $so_for = $request->input('so_for');
        $customer_other_address_id = $request->input('customer_other_address_id');
        $status_so = $request->input('status_so');

        $table = SalesOrder::where(function($query2) use($search,$so_for,$step){
                                if(!empty($step)){
                                    if ($step === 2) { // SO Lanjutan
                                        $query2->whereIn('status', [2, 4]);
                                        $query2->where('so_for', 1);
                                    }
                                }
                            })
                            ->where(function($query2) use($customer_other_address_id, $status_so){
    							if(!empty($customer_other_address_id)){
    								$query2->whereHas('member', function($query3) use($customer_other_address_id){
    									$query3->where('customer_other_address_id', $customer_other_address_id);
    								});
    							}
    							if(!empty($status_so)){
    								$query2->where(function($query3) use($status_so){
                                        $query3->where('status', $status_so);
    								});
    							}
    						})
                            ->where('type_so', 'ppn')
                            ->where('so_indent', SalesOrder::INDENT['NO'])
                            ->orderBy('id','DESC')
                            ->get();

        $customers = Customer::get();
        $other_address = CustomerOtherAddress::get();
        $brand = BrandLokal::get();
        $packing_order = PackingOrder::get();

        $data = [
            'customers' => $customers,
            'other_address' => $other_address,
            'packing_order' => $packing_order,
            'brand' => $brand,
            'step' => $step,
            'table' => $table,
            'step_txt' => SalesOrder::STEP[$step],
        ];

        return view($this->view."index_lanjutan_ppn", $data);
    }

    public function create(Request $request, $step = 1)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $brand = BrandLokal::all();
        $products = Product::all();
        $other_address = CustomerOtherAddress::get();
        $warehouse = Warehouse::all();
		$ekspedisi = vendor::where('type', 1)->get();
        $sales = Sales::where('is_active', 1)->get();
        $product_category = ProductCategory::get();
        $type_transaction = SalesOrder::TYPE_TRANSACTION;
        $rekenings = SalesOrder::REKENING;

        $data = [
            'other_address' => $other_address,
            'brand' => $brand,
            'products' => $products,
            'sales' => $sales,
            'warehouse' => $warehouse,
            'ekspedisi' => $ekspedisi,
            'product_category' => $product_category,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
            'type_transaction' => $type_transaction,
            'rekenings' => $rekenings
        ];
        
        
        return view($this->view."create", $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'sales_senior_id' => 'required|integer',
                'sales_id' => 'required|integer',
                'type_transaction' => 'required|string',
                'brand_name' => 'required|string',
                'customer_name' => 'required',
                'no_document' => 'required|string',
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
                $sales_order_ppn = new SalesOrder;

                $sales_order_ppn->so_code = CodeRepo::generateSoAwalPpn();
                $sales_order_ppn->type_transaction = $request->type_transaction ?? null;
                $sales_order_ppn->sales_senior_id = $request->sales_senior_id;
                $sales_order_ppn->sales_id = $request->sales_id;

                $get_customer = CustomerOtherAddress::where('id', $request->customer_name)->first();
                $sales_order_ppn->customer_other_address_id = $request->customer_name;
                $sales_order_ppn->customer_id = $get_customer->store->id;
                $sales_order_ppn->rekening = $request->rekening;
                $sales_order_ppn->type_so = 'ppn';
                $sales_order_ppn->no_ducument_ppn = $request->no_document;
                $sales_order_ppn->brand_name = $request->brand_name;
                $sales_order_ppn->created_by = Auth::id();
                $sales_order_ppn->condition = 1;
                $sales_order_ppn->so_for = 1;
                $sales_order_ppn->payment_status = 0;
                $sales_order_ppn->status = 1;
                $sales_order_ppn->count_rev = 0;
                $sales_order_ppn->idr_rate = 1;
                if($sales_order_ppn->save()){
                    if($request->sku) {
                        foreach($request->sku as $key => $value){
                            if($request->sku[$key]) {

                                $sales_khusus_detail = new SalesOrderItem;
                                $sales_khusus_detail->so_id = $sales_order_ppn->id;
                                $sales_khusus_detail->product_packaging_id = $request->sku[$key];
                                $sales_khusus_detail->packaging_id = $request->packaging[$key];
                                $sales_khusus_detail->qty = $request->qty[$key];
                                $sales_khusus_detail->price = $request->price[$key];
                                $sales_khusus_detail->disc_usd = $request->disc[$key];
                                $sales_khusus_detail->created_by = Auth::id();
                                $sales_khusus_detail->save();
                            }
                        }
                    }

                    LogActivity::addToLog('Created a new SO-PPN: ' . $sales_order_ppn->so_code);

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.penjualan.sales_order_ppn.index_ppn_awal');
                    return $this->response(200, $response);
                }
            }
        }
    }

    public function lanjutkan(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $sales_order = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            $sales_order->status = 2;

            if($sales_order->save()) {
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];
    
                $response['redirect_to'] = route('superuser.penjualan.sales_order_ppn.index_ppn_awal');
                return $this->response(200, $response);
            }
            
        }
    }

    public function edit_ppn($id, $step)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrder::where('id',$id)->first();
        // $result = SalesOrder::find($id);
        if(empty($result)){
            abort(404);
        }

        $other_address = CustomerOtherAddress::get();
        $warehouse = Warehouse::all();
        $sales = Sales::all();
        $product_category = ProductCategory::all();
        $brand = BrandLokal::get();
        $ekspedisi = Vendor::where('type', 1)->get();
        $packaging = Packaging::get();
        $rekening = DB::table('rekening')->where('id', 5)->get();

        $data = [
            'other_address' => $other_address,
            'warehouse' => $warehouse,
            'sales' => $sales,
            'product_category' => $product_category,
            'brand' => $brand,
            'ekspedisi' => $ekspedisi,
            'result' => $result,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
            'packaging' => $packaging,
            'rekening' => $rekening,
        ];

        // DD($data);

        if ($step == 1 || $step == 9) {
            return view($this->view."edit_ppn",$data);
        } else if ($step == 2) {
            return view($this->view."create_lanjutan",$data);
        }
    }

    public function update_awal_ppn(Request $request, $id)
    {
        if ($request->ajax()) {
            DB::beginTransaction();
            try{

                $step = $request->step;
                $errors = [];

                $sales_order_ppn = SalesOrder::find($id);

                if($sales_order_ppn == null){
                    abort(404);
                }

                $sales_order_ppn->sales_senior_id = $request->sales_senior_id;
                $sales_order_ppn->sales_id = $request->sales_id;
                $sales_order_ppn->type_transaction = $request->type_transaction;
                $sales_order_ppn->no_ducument_ppn = $request->no_document;
                $sales_order_ppn->brand_name = $request->brand_name;
                $sales_order_ppn->idr_rate = 1;
                $sales_order_ppn->note = $request->note;
                $sales_order_ppn->updated_by = Auth::id();
                $sales_order_ppn->status = $step;
                if($sales_order_ppn->save()){
                    $update_item = SalesOrderItem::where('so_id',   $sales_order_ppn->id)->update(['status' => 0]);
                    $deleted_item = SalesOrderItem::where('so_id',  $sales_order_ppn->id)->delete();

                    if($request->sku) {
                        foreach($request->sku as $key => $value){
                            if($request->sku[$key]) {

                                $duplicate_product = [];
                                $duplicate = false;
                                $listItem[] = [
                                    'sku' => $request->sku[$key],
                                ];

                                foreach($listItem as $row => $value){
                                    if(in_array($value, $duplicate_product)) {
                                        $duplicate = true;
                                        break;
                                    } else {
                                        array_push($duplicate_product, $value);
                                    }
    
                                    // dd($value); 
                                }

                                if($duplicate){
                                   $errors[] = 'Item sudah ada!';
                                }else{
                                    $sales_khusus_detail = new SalesOrderItem;
                                    $sales_khusus_detail->so_id = $sales_order_ppn->id;
                                    $sales_khusus_detail->product_packaging_id = $request->sku[$key];
                                    $sales_khusus_detail->packaging_id = $request->packaging[$key];
                                    $sales_khusus_detail->qty = $request->qty[$key];
                                    $sales_khusus_detail->created_by = Auth::id();
                                    $sales_khusus_detail->save();
                                }
                            }
                        }
                    }
                }
                DB::commit();
                LogActivity::addToLog('Updated SO-PPN: ' . $sales_order_ppn->so_code);
                if($errors){
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $errors,
                    ];
    
                    return $this->response(400, $response);
                }else{
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
    
                    $response['redirect_to'] = route('superuser.penjualan.sales_order_ppn.index_ppn_awal');
                    return $this->response(200, $response);
                }
            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $errors,
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function tutup_so_ppn(Request $request)
    {
        if ($request->ajax()) {

            DB::beginTransaction();
            try{

                $errors = [];

                $sales_order_ppn = SalesOrder::find($request->id);

                if($sales_order_ppn === null){
                    abort(404);
                }

                if($sales_order_ppn->count_rev == 0) {

                    if($request->so_date == null){
                        $errors[] = 'Tanggal tidak boleh kosong!';
                    }
    
                    if($request->rekening == null){
                        $errors[] = 'Rekening tidak boleh kosong!';
                    }
    
                    if($request->idr_rate == null){
                        $errors[] = 'Kurs tidak boleh kosong!';
                    }
    
                    $sales_order_ppn->code = CodeRepo::generateSOPPN();
                    $sales_order_ppn->so_date = date("y-m-d", strtotime($request->so_date));
                    $sales_order_ppn->rekening = $request->rekening;
                    $sales_order_ppn->idr_rate = $request->idr_rate;
                    $sales_order_ppn->shipping_cost_buyer = $request->shipping_cost_buyer ?? 0;
                    $sales_order_ppn->status = 4;
                    $sales_order_ppn->count_rev = 0;
                    $sales_order_ppn->updated_by = Auth::id();
                    if($sales_order_ppn->save()){
                        $packing_order = new PackingOrder;
                        $packing_order->code = CodeRepo::generatePO();
                        $packing_order->do_code = $sales_order_ppn->code;
                        $packing_order->so_id  = $sales_order_ppn->id;
                        $packing_order->customer_id  = $sales_order_ppn->customer_id;
                        $packing_order->customer_other_address_id  = $sales_order_ppn->customer_other_address_id;
                        $packing_order->warehouse_id = $sales_order->origin_warehouse_id ?? null;
                        $packing_order->type_transaction  = $sales_order_ppn->type_transaction;
                        $packing_order->idr_rate = $request->idr_rate;
                        $packing_order->other_address = 0 ?? Null;
                        $packing_order->note = $company->note ?? null;
                        $packing_order->status = 6;
                        $packing_order->count_cancel = 0;
                        $packing_order->created_by = Auth::id();
                        $packing_order->save();
    
                        // definisi hasil penjumlahan di view
                        $discount_agen_idr = $request->disc_agen_idr;
                        $discount_kemasan_idr = $request->disc_kemasan_idr;
                        $ppn_percent = $request->ppn_percent;
                        $ppn_idr = $request->ppn_idr;
                        $sub_total = $request->subtotal_2;
                        $grand_total_idr = $request->grand_total_idr;
    
                        if($grand_total_idr == null){
                            $errors[] = 'Grand Total tidak boleh kosong!';
                        }
    
                        // pecah format currency 
                        $discount_agen_idr = str_replace('.', '', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace('.', '', $discount_kemasan_idr);
                        $ppn_percent = str_replace('.', '', $ppn_percent);
                        $ppn_idr = str_replace('.', '', $ppn_idr);
                        $sub_total = str_replace('.', '', $sub_total);
                        $grand_total_idr = str_replace('.', '', $grand_total_idr);
                        
                        // ubah decimal koma ke titik
                        $discount_agen_idr = str_replace(',', '.', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace(',', '.', $discount_kemasan_idr);
                        $ppn_percent = str_replace(',', '.', $ppn_percent);
                        $ppn_idr = str_replace(',', '.', $ppn_idr);
                        $sub_total = str_replace(',', '.', $sub_total);
                        $grand_total_idr = str_replace(',', '.', $grand_total_idr);
    
                        $packing_order_detail = new PackingOrderDetail;
                        $packing_order_detail->do_id = $packing_order->id;
                        $packing_order_detail->discount_1 = $request->disc_agen_percent;
                        $packing_order_detail->discount_1_idr = $discount_agen_idr;
                        $packing_order_detail->discount_2 = $request->disc_kemasan_percent;
                        $packing_order_detail->discount_2_idr = $discount_kemasan_idr;
                        $packing_order_detail->discount_idr = $request->disc_tambahan_idr;
                        $packing_order_detail->ppn_percent = $ppn_percent;
                        $packing_order_detail->ppn_idr = $ppn_idr;
                        $packing_order_detail->voucher_idr = $request->voucher_idr;
                        $packing_order_detail->purchase_total_idr = $sub_total;
                        $packing_order_detail->delivery_cost_idr = $request->delivery_cost_idr;
                        $packing_order_detail->other_cost_idr = 0;
                        $packing_order_detail->grand_total_idr = $grand_total_idr;
                        $packing_order_detail->terbilang = CustomHelper::terbilang($grand_total_idr);
                        $packing_order_detail->created_by = Auth::id();
                        $packing_order_detail->save();
    
                        foreach($request->repeater as $key => $value){
                            $so_item_id = $value["so_item_id"];
                            $price = $value["price"];
                            $so_qty = $value["so_qty"];
                            $do_qty = $value["do_qty"];
                            $rej_qty = $so_qty - $do_qty;
                            $usd_disc = $value["usd_disc"];
                            $percent_disc = 0;
                            $total_discount = 0;
    
                            if(empty($value["so_item_id"])){
                                $errors[] = 'SO Item ID tidak boleh kosong';
                            }
    
                            if(empty($value["product_packaging_id"])){
                                $errors[] = 'Product ID tidak boleh kosong';
                            }
    
                            $qty_total = $do_qty + $rej_qty;
                            $sisa = $so_qty - $do_qty;
    
                            if($so_qty < $qty_total){
                                $errors[] = 'Jumlah DO,REJ melebihi SO Qty';
                            }
            
                            if($do_qty == 0 && $rej_qty == 0){
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty' => 0
                                ]);
                            }
    
                            if($do_qty > 0){
                                $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc/100))) * $do_qty);
                                $data[] = [
                                    'do_id' => $packing_order->id,
                                    'product_packaging_id' => $value["product_packaging_id"],
                                    'so_item_id' => $value["so_item_id"],
                                    'packaging_id' => $value["packaging"],
                                    'qty' => $do_qty,
                                    'price' => $price,
                                    'usd_disc' => $usd_disc,
                                    'percent_disc' => $percent_disc,
                                    'total_disc' => $total_disc,
                                    'total' => floatval($do_qty * $price) - $total_disc,
                                    'created_by' => Auth::id(),
                                ];
            
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty_worked' => $do_qty
                                ]);
                            }
    
                            if(empty($do_qty) && $rej_qty > 0){
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty_worked' => $do_qty
                                ]);
                            }
                        }
    
                        if (count($data) == 0) {
                            DB::rollback();
                            $errors[] =  'Not item sales order are ready';
                        }
    
                        foreach ($data as $key => $value) {
                            $insert = PackingOrderItem::create($data[$key]);
                        }
    
                        // Cetak Invoice disini
                        if(empty($packing_order->invoicing)){
                            $data = [
                                'code' => $sales_order_ppn->code,
                                'do_id' => $packing_order->id,
                                'customer_id' => $sales_order_ppn->customer_id,
                                'customer_other_address_id' => $sales_order_ppn->customer_other_address_id,
                                'grand_total_idr' => $packing_order_detail->grand_total_idr,
                                'created_by' => Auth::id(),
                            ];
    
                            $insert_invoice = Invoicing::create($data);
                        }
    
                        DB::commit();
                        LogActivity::addToLog('Cloased SO-PPN: ' . $sales_order_ppn->code);
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
                    
                            $response['redirect_to'] = route('superuser.penjualan.sales_order_ppn.index_ppn_lanjutan');
                             return $this->response(200, $response);
                        }
                    }
                } elseif ($sales_order_ppn->count_rev == 1) {
                    if($request->so_date == null){
                        $errors[] = 'Tanggal tidak boleh kosong!';
                    }
    
                    if($request->rekening == null){
                        $errors[] = 'Rekening tidak boleh kosong!';
                    }
    
                    if($request->idr_rate == null){
                        $errors[] = 'Kurs tidak boleh kosong!';
                    }

                    $sales_order_ppn->so_date = date("y-m-d", strtotime($request->so_date));
                    $sales_order_ppn->code = $sales_order_ppn->keep_code;
                    $sales_order_ppn->rekening = $request->rekening;
                    $sales_order_ppn->idr_rate = $request->idr_rate;
                    $sales_order_ppn->status = 4;
                    $sales_order_ppn->count_rev = 0;
                    $sales_order_ppn->updated_by = Auth::id();

                    $valuePoDetail = [];
                    if($sales_order_ppn->save()){
                        
                        $jumlahitem = 0;
                        $data = [];

                        $get_po = PackingOrder::where('so_id', $sales_order_ppn->id)->first();

                        foreach ($request->repeater as $key => $value) {
                            if (empty($value["so_qty"]) || (!empty($value["so_qty"]) && $value["so_qty"] <= 0)) {
                                continue;
                            }

                            $result = SalesOrderItem::where('id',$value["so_item_id"])->first();
                           

                            $jumlahitem = $jumlahitem + 1;

                            $so_item_id = $value["so_item_id"];
                            $price = $value["price"];
                            $so_qty = $value["so_qty"];
                            $do_qty = $value["do_qty"];
                            $rej_qty = $so_qty - $do_qty;
                            $usd_disc = $value["usd_disc"];
                            $percent_disc = 0;
                            $total_discount = 0;


                            if($value["so_item_id"] == null){
                                $errors[] = 'SO Item ID tidak boleh kosong';
                            }
                            if($value["product_packaging_id"] == null){
                                $errors[] = 'Product ID tidak boleh kosong';
                            }
                            if($value["price"] == null){
                                $errors[] = 'Harga tidak boleh kosong';
                            }

                            $qty_total = $do_qty + $rej_qty;
                            $sisa = $so_qty - $do_qty;

                            if($so_qty < $qty_total){
                                $errors[] = 'Jumlah DO,REJ melebihi SO Qty';
                            }

                            if($do_qty == 0 && $rej_qty == 0){
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty' => 0
                                ]);
                            }

                            if($do_qty > 0){
                                $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc/100))) * $do_qty);
                                $data[] = [
                                    'do_id' => $get_po->id,
                                    'product_packaging_id' => $value["product_packaging_id"],
                                    'so_item_id' => $value["so_item_id"],
                                    'packaging_id' => $result->packaging_id,
                                    'qty' => $do_qty,
                                    'price' => $price,
                                    'usd_disc' => $usd_disc,
                                    'percent_disc' => $percent_disc,
                                    'total_disc' => $total_disc,
                                    'total' => floatval($do_qty * $price) - $total_disc,
                                    'created_by' => Auth::id(),
                                ];
                        
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty_worked' => $do_qty
                                ]);
                            }
                        
                            if(empty($do_qty) && $rej_qty > 0){
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty_worked' => $do_qty
                                ]);
                            }
                        }
    
                        $updatePo = PackingOrder::where('id', $get_po->id)->update([
                            'status' => 2,
                            'do_code' => $sales_order_ppn->code,
                            'idr_rate' => $request->idr_rate,
                        ]);

                        // definisi hasil penjumlahan di view
                        $discount_agen_idr = $request->disc_agen_idr;
                        $discount_kemasan_idr = $request->disc_kemasan_idr;
                        $disc_tambahan_idr = $request->disc_tambahan_idr;
                        $sub_total = $request->subtotal_2;
                        $grand_total_idr = $request->grand_total_idr;

                        // pecah format currency 
                        $discount_agen_idr = str_replace('.', '', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace('.', '', $discount_kemasan_idr);
                        $sub_total = str_replace('.', '', $sub_total);
                        $grand_total_idr = str_replace('.', '', $grand_total_idr);
                        
                        // ubah decimal koma ke titik
                        $discount_agen_idr = str_replace(',', '.', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace(',', '.', $discount_kemasan_idr);
                        $sub_total = str_replace(',', '.', $sub_total);
                        $grand_total_idr = str_replace(',', '.', $grand_total_idr);
    
                        $valuePoDetail[] = [
                            'discount_1' => $request->disc_agen_percent,
                            'discount_2' => $request->disc_kemasan_percent,
                            'discount_1_idr' => $discount_agen_idr,
                            'discount_2_idr' => $discount_kemasan_idr,
                            'discount_idr' => $disc_tambahan_idr,
                            'voucher_idr' => $request->voucher_idr,
                            'purchase_total_idr' => $sub_total,
                            'delivery_cost_idr' => $request->delivery_cost_idr,
                            'other_cost_idr' => $request->resi_ongkir ?? 0,
                            'grand_total_idr' => $grand_total_idr,
                            'updated_by' => Auth::id(),
                            'created_by' => Auth::id(),
                        ];
    
                        if($get_po->status == 7){
                            foreach ($valuePoDetail as $key => $value) {
                                $updatePoDetail = PackingOrderDetail::where('do_id', $get_po->id)->update($valuePoDetail[$key]);
                            }
    
                            foreach( $data as $key => $value ){
                                $insertItem = PackingOrderItem::create($data[$key]);
                            }
                            
                        }

                        if(empty($get_po->invoicing)){
                            $data = [
                                'code' => $sales_order_ppn->code,
                                'do_id' => $get_po->id,
                                'customer_id' => $sales_order_ppn->customer_id,
                                'customer_other_address_id' => $sales_order_ppn->customer_other_address_id,
                                'grand_total_idr' => $grand_total_idr,
                                'status' => 1,
                                'created_by' => Auth::id(),
                            ];

                            $insert_invoice = Invoicing::create($data);
                        }else{
                            $data = [
                                'code' => $sales_order_ppn->code,
                                'do_id' => $get_po->id,
                                'customer_id' => $sales_order_ppn->customer_id,
                                'customer_other_address_id' => $sales_order_ppn->customer_other_address_id,
                                'grand_total_idr' => $grand_total_idr,
                                'status' => 1,
                                'created_by' => Auth::id(),
                            ];

                            $update_invoice = Invoicing::where('do_id', $get_po->id)->update($data);
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
                
                            $response['redirect_to'] = route('superuser.penjualan.sales_order_ppn.index_ppn_lanjutan');
                            return $this->response(200, $response);
                        }
                    }
                }
            }catch (\Exception $e) {
                // dd($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $errors,
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        
        $result = SalesOrder::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }

        $step = 2;
        if ($result->status === 1 || $result->status === 3) {
            $step = 1;
        }

        $data = [
            'result' => $result,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
        ];
        return view($this->view."show",$data);
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


    public function get_brand(Request $request)
    {
        $brands = BrandLokal::where('status', BrandLokal::STATUS['ACTIVE'])
            ->where(function ($query) use ($request) {
                $query->where('brand_name', 'LIKE', $request->input('q', '') . '%');
            })
            ->whereIn('id', [3, 4, 5, 6])
            ->get();

        $results = [];

        foreach ($brands as $item) {
            $results[] = [
                'id' => $item->brand_name,
                'text' => $item->brand_name,
            ];
        }

        return ['results' => $results];
    }

    public function get_product_pack(Request $request)
    {
        if ($request->ajax()) {
                $data = [];
                
                $product = Product::where('master_products.brand_name', $request->id)
                        ->where('master_products_packaging.status', 1)
                        ->leftJoin('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
                        ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                        ->leftJoin('master_warehouses', 'master_products_packaging.warehouse_id', '=', 'master_warehouses.id')
                        ->select('master_products_packaging.id as id' ,
                                    'master_products_packaging.code as ProductCode', 
                                    'master_products_packaging.name as productName', 
                                    'master_products_packaging.price as productPrice', 
                                    'master_packaging.id as  productPackagingID', 
                                    'master_packaging.pack_name as productPackaging', 
                                    'master_warehouses.name as warehouseName',
                        )
                        ->get();

                foreach($product as $key){
                    $data[] = [
                        'id' => $key->id,
                        'code' => $key->ProductCode,
                        'name' => $key->productName,
                        'price' => $key->productPrice,
                        'packName' => $key->productPackaging,
                        'packID' => $key->productPackagingID,
                        'warehouseName' => $key->warehouseName,
                    ];
                }

                return response()->json(['code' => 200, 'data' => $data]);
        }
    }

    public function destroy_ppn(Request $request)
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
            $update = SalesOrder::where('id',$post["id"])->update(['deleted_by' => Auth::id()]);
            $destroy = SalesOrder::where('id',$post["id"])->delete();
            $so_item = SalesOrderItem::where('so_id',$post["id"])->get();

            foreach ($so_item as $index => $value) {
                $check_do_item = PackingOrderItem::where('so_item_id',$value->id)->first();
                $check_do_mutation_item = DeliveryOrderMutationItem::where('so_item_id',$value->id)->first();
                if($check_do_item || $check_do_mutation_item){
                    return redirect()->back()->with('error','Gagal menghapus Item . Item SO ini sudah digunakan di Packing Order / Delivery Order Mutation');
                }
            }
            $destroy_item = SalesOrderItem::where('so_id',$post["id"])->delete();
            
            DB::commit();
            LogActivity::addToLog('Deleted SO-PPN: ' . $post["id"]);
            return redirect()->back()->with('success','SO berhasil dihapus');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function destroy_lanjutan(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }

        if ($request->ajax()) {
            $sales_order = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            // if ($sales_order->count_rev > 0) {
            //     return $this->response(400, ['failed' => 'Invoice sudah terbuat!']);
            // }

            $sales_order->deleted_by = Auth::id();
            $sales_order->delete();

            if($sales_order->save()){
                foreach ($sales_order->so_detail as $detail) {
                    SalesOrderItem::where('id', $detail->id)->delete();
                }

                LogActivity::addToLog('Deleted SO-Lanjutan: ' . $sales_order->so_code);
                $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                return $this->response(200, $response);
            }
        }
    }

    public function cancel_approve(Request $request, $id)
    {
        // Access Control
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0) {
                abort(405);
            }
        }

        if ($request->ajax()) {
            DB::beginTransaction(); // Start the transaction

            try {
                $sales_order_ppn = SalesOrder::findOrFail($id); // Automatically handles 404

                // Store current code before nullifying it, to retain it in keep_code
                $sales_order_ppn->keep_code = $sales_order_ppn->code;
                $sales_order_ppn->code = null;
                $sales_order_ppn->status = 2;
                $sales_order_ppn->count_rev = 1; // Ensure this is correct per your business logic
                $sales_order_ppn->updated_by = Auth::id();

                if ($sales_order_ppn->save()) {
                    // Delete DO & Invoice
                    $get_do = PackingOrder::where('so_id', $sales_order_ppn->id)->first();

                    if ($get_do) {
                        // Update the status of PackingOrder
                        $get_do->update(['status' => 7]);
                        PackingOrderItem::where('do_id', $get_do->id)->delete();
                    }

                    DB::commit(); // Commit the transaction

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.penjualan.sales_order_ppn.index_ppn_lanjutan');
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback(); // Rollback the transaction on error

                // Log the exception for further investigation
                Log::error('Error canceling sales order: ' . $e->getMessage());

                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'An error occurred while processing your request. Please try again later.',
                ];

                return $this->response(400, $response);
            }
        }

        return $this->response(400, [
            'notification' => [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Invalid Request',
                'content' => 'This request is not valid.',
            ],
        ]);
    }
}