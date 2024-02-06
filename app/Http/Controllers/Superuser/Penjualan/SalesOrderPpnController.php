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
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
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

    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['so_ppn'] = SalesOrder::where('type_so', 'ppn')->get();
        $data['so_khusus'] = SalesOrder::where('type_so', 'khusus')->get();

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
        $code = CodeRepo::generateSOPPN();

        $data = [
            // 'customer' => $customer,
            'member' => $member,
            'sales' => $sales,
            'ekspedisi' => $ekspedisi,
            'warehouse' => $warehouse,
            'brand' => $brand,
            'product_category' => $product_category,
            'type_transaction' => $type_transaction,
            'code' => $code,
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

                if($request->invoice_ppn == null){
                    $sales_khusus = new SalesOrder;

                    if($request->origin_warehouse_id == null){
                        $errors[] = 'Warehouse tidak boleh kosong!';
                    }

                    if($request->rekening == null){
                        $errors[] = 'Rekening tidak boleh kosong!';
                    }

                    if($request->customer_name == null){
                        $errors[] = 'Customer tidak boleh kosong!';
                    }

                    $sales_khusus->so_date = $request->so_date;
                    $sales_khusus->so_code = CodeRepo::generateSoAwal();
                    $sales_khusus->code = CodeRepo::generateSO();;
                    $sales_khusus->type_transaction = $request->type_transaction;
                    $sales_khusus->sales_senior_id = $request->sales_senior_id;
                    $sales_khusus->sales_id = $request->sales_id;
                    $sales_khusus->origin_warehouse_id = $request->origin_warehouse_id;
                    $sales_khusus->vendor_id = $request->ekspedisi;
                    $sales_khusus->type_so = 'khusus';

                    $get_customer = CustomerOtherAddress::where('id', $request->customer_name)->first();
                    
                    $sales_khusus->customer_id = $get_customer->store->id;
                    $sales_khusus->customer_other_address_id = $request->customer_name;
                    $sales_khusus->catatan = $request->no_document;
                    $sales_khusus->rekening = $request->rekening;
                    $sales_khusus->idr_rate = $request->idr_rate;
                    $sales_khusus->created_by = Auth::id();
                    $sales_khusus->condition = 1;
                    $sales_khusus->payment_status = 0;
                    $sales_khusus->status = 4;
                    $sales_khusus->count_rev = 0;
                    if($sales_khusus->save()){
                        if($request->sku) {
                            foreach($request->sku as $key => $value){
                                if($request->sku[$key]) {
    
                                    $sales_khusus_detail = new SalesOrderItem;
                                    $sales_khusus_detail->so_id = $sales_khusus->id;
                                    $sales_khusus_detail->product_packaging_id = $request->sku[$key];
                                    $sales_khusus_detail->packaging_id = $request->packaging[$key];
                                    $sales_khusus_detail->qty = $request->qty[$key];
                                    $sales_khusus_detail->free_product = 0;
                                    $sales_khusus_detail->created_by = Auth::id();
                                    $sales_khusus_detail->save();
                                }
                            }
                        }

                        $packing_order = new PackingOrder;
                        $packing_order->code = CodeRepo::generatePO();
                        $packing_order->do_code = $sales_khusus->code;
                        $packing_order->so_id  = $sales_khusus->id;
                        $packing_order->customer_id  = $sales_khusus->customer_id;
                        $packing_order->customer_other_address_id  = $sales_khusus->customer_other_address_id;
                        $packing_order->warehouse_id = $sales_khusus->origin_warehouse_id;
                        $packing_order->type_transaction  = $sales_khusus->type_transaction;
                        $packing_order->idr_rate = $request->idr_rate;
                        $packing_order->other_address = 0 ?? Null;
                        $packing_order->note = $company->note ?? null;
                        $packing_order->vendor_id = $sales_khusus->ekspedisi_id ?? null;
                        $packing_order->status = 2;
                        $packing_order->mitra_id = 0;
                        $packing_order->status_mitra = 0;
                        $packing_order->count_cancel = 0;
                        $packing_order->created_by = Auth::id();
                        $packing_order->save();

                        // definisi hasil penjumlahan di view
                        $discount_agen_idr = $request->disc_agen_idr;
                        $discount_kemasan_idr = $request->disc_kemasan_idr;
                        $sub_total = $request->sub_total_item;
                        $grand_total_idr = $request->grand_total_idr;

                        if($grand_total_idr == null){
                            $errors[] = 'Grand Total tidak boleh kosong!';
                        }

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

                        // DD($sub_total);

                        $packing_order_detail = new PackingOrderDetail;
                        $packing_order_detail->do_id = $packing_order->id;
                        $packing_order_detail->discount_1 = $request->disc_agen_percent;
                        $packing_order_detail->discount_1_idr = $discount_agen_idr;
                        $packing_order_detail->discount_2 = $request->disc_kemasan_percent;
                        $packing_order_detail->discount_2_idr = $discount_kemasan_idr;
                        $packing_order_detail->discount_idr = $request->disc_tambahan_idr;
                        $packing_order_detail->voucher_idr = $request->voucher_idr;
                        $packing_order_detail->purchase_total_idr = $sub_total;
                        $packing_order_detail->other_cost_idr = 0;
                        $packing_order_detail->grand_total_idr = $grand_total_idr;
                        $packing_order_detail->terbilang = CustomHelper::terbilang($grand_total_idr);
                        $packing_order_detail->created_by = Auth::id();
                        $packing_order_detail->save();

                        $data = [];
                        $product = 0;
                        $out_of_stock = false;
                        foreach($request->sku as $key => $value){
                            $so_item_id = $sales_khusus_detail->id;
                            $price = $request->price[$key];
                            $so_qty = $request->qty[$key];
                            $usd_disc = $request->disc[$key];
                            $percent_disc = 0;
                            $total_discount = 0;

                            if(empty($so_item_id)){
                                $errors[] = 'SO Item ID tidak boleh kosong';
                            }

                            if(empty($request->sku[$key])){
                                $errors[] = 'Product ID tidak boleh kosong';
                            }

                            $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc/100))) * $so_qty);
                            $data[] = [
                                'do_id' => $packing_order->id,
                                'product_packaging_id' => $request->sku[$key],
                                'so_item_id' => $sales_khusus_detail->id,
                                'packaging_id' => $request->packaging[$key],
                                'qty' => $so_qty,
                                'price' => $price,
                                'usd_disc' => $usd_disc,
                                'percent_disc' => $percent_disc,
                                'total_disc' => $total_disc,
                                'total' => floatval($so_qty * $price) - $total_disc,
                                'created_by' => Auth::id(),
                            ];
                            // DD($request->sku[$key]);

                            // Check stock
                            $stock_order = ProductMinStock::where('product_packaging_id', $sales_khusus_detail->product_packaging_id)->where('warehouse_id', $request->origin_warehouse_id)->get();

                            foreach($stock_order as $key => $value){
                                if($key){
                                    if($value->quantity < $request->qty[$key]){
                                        $out_of_stock = true;
                                        $product = $sales_khusus_detail->product_packaging_id;
                                        break;
                                    }else{
                                        $out_of_stock = true;
                                        $product = $sales_khusus_detail->product_packaging_id;
                                        break;
                                    }
                                }
                            }
                        }

                        if (count($data) == 0) {
                            DB::rollback();
                            $errors[] =  'Not item sales order are ready';
                        }

                        // DD($out_of_stock);

                        if($out_of_stock){
                            $product = ProductPack::find($product);
                            $errors[] = 'Out Of Stock! <b>'.$product->name.'</b> Please contact Administrator';
                            DB::rollback();
                        }else{
                            foreach ($data as $key => $value) {
                                $insert = PackingOrderItem::create($data[$key]);
                            }

                            // Cetak Invoice disini
                            if(empty($packing_order->invoicing)){
                                $data = [
                                    'code' => $sales_khusus->code,
                                    'do_id' => $packing_order->id,
                                    'customer_id' => $sales_khusus->customer_id,
                                    'customer_other_address_id' => $sales_khusus->customer_other_address_id,
                                    'grand_total_idr' => $packing_order_detail->grand_total_idr,
                                    'created_by' => Auth::id(),
                                ];

                                $insert_invoice = Invoicing::create($data);
                            }
                        }

                        
                        if($errors) {
                            $response['notification'] = [
                                'alert' => 'block',
                                'type' => 'alert-danger',
                                'header' => 'Error',
                                'content' => $errors,
                            ];
        
                            return $this->response(400, $response);
                        } else {
                            DB::commit();
                            $response['notification'] = [
                                'alert' => 'notify',
                                'type' => 'success',
                                'content' => 'Success',
                            ];
                
                            $response['redirect_to'] = route('superuser.penjualan.sales_order_ppn.index');
                            return $this->response(200, $response);
                        }
                    }
                }elseif($request->invoice_ppn == 1){
                    $sales_order_ppn = new SalesOrder;

                    $sales_order_ppn->so_code = CodeRepo::generateSoAwal();
                    $sales_order_ppn->code = CodeRepo::generateSOPPN();
                    $sales_order_ppn->so_date = $request->so_date;
                    $sales_order_ppn->type_transaction = $request->type_transaction ?? null;
                    $sales_order_ppn->sales_senior_id = $request->sales_senior_id;
                    $sales_order_ppn->sales_id = $request->sales_id;
                    $sales_order_ppn->origin_warehouse_id = $request->origin_warehouse_id ?? null;

                    $get_customer = CustomerOtherAddress::where('id', $request->customer_name)->first();
                    $sales_order_ppn->customer_other_address_id = $request->customer_name;
                    $sales_order_ppn->customer_id = $get_customer->store->id;
                    $sales_order_ppn->rekening = $request->rekening;
                    $sales_order_ppn->type_so = 'ppn';
                    $sales_order_ppn->idr_rate = $request->idr_rate;
                    $sales_order_ppn->catatan = $request->no_document;
                    $sales_order_ppn->created_by = Auth::id();
                    $sales_order_ppn->condition = 1;
                    $sales_order_ppn->payment_status = 0;
                    $sales_order_ppn->status = 4;
                    $sales_order_ppn->count_rev = 0;
                    if($sales_order_ppn->save()){
                        if($request->sku) {
                            foreach($request->sku as $key => $value){
                                if($request->sku[$key]) {
    
                                    $sales_order_ppn_detail = new SalesOrderItem;
                                    $sales_order_ppn_detail->so_id = $sales_order_ppn->id;
                                    $sales_order_ppn_detail->product_packaging_id = $request->sku[$key];
                                    $sales_order_ppn_detail->packaging_id = $request->packaging[$key];
                                    $sales_order_ppn_detail->qty = $request->qty[$key];
                                    $sales_order_ppn_detail->free_product = 0;
                                    $sales_order_ppn_detail->created_by = Auth::id();
                                    $sales_order_ppn_detail->save();
                                }
                            }
                        }

                        $packing_order = new PackingOrder;
                        $packing_order->code = CodeRepo::generatePO();
                        $packing_order->do_code = $sales_order_ppn->code;
                        $packing_order->so_id  = $sales_order_ppn->id;
                        $packing_order->customer_id  = $sales_order_ppn->customer_id;
                        $packing_order->customer_other_address_id  = $sales_order_ppn->customer_other_address_id;
                        $packing_order->warehouse_id = null;
                        $packing_order->type_transaction  = $sales_order_ppn->type_transaction;
                        $packing_order->idr_rate = $request->idr_rate;
                        $packing_order->other_address = 0 ?? Null;
                        $packing_order->note = $company->note ?? null;
                        $packing_order->vendor_id = $sales_order_ppn->ekspedisi_id;
                        $packing_order->status = 4;
                        $packing_order->mitra_id = 0;
                        $packing_order->status_mitra = 0;
                        $packing_order->count_cancel = 0;
                        $packing_order->created_by = Auth::id();
                        $packing_order->save();

                        // definisi hasil penjumlahan di view
                        $discount_agen_idr = $request->disc_agen_idr;
                        $discount_kemasan_idr = $request->disc_kemasan_idr;
                        $ppn_idr = $request->ppn_idr;
                        $sub_total = $request->sub_total_item;
                        $grand_total_idr = $request->grand_total_idr;

                        if($grand_total_idr == null){
                            $errors[] = 'Grand Total tidak boleh kosong!';
                        }

                        // pecah format currency 
                        $discount_agen_idr = str_replace('.', '', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace('.', '', $discount_kemasan_idr);
                        $ppn_idr = str_replace('.', '', $ppn_idr);
                        $sub_total = str_replace('.', '', $sub_total);
                        $grand_total_idr = str_replace('.', '', $grand_total_idr);
                        
                        // ubah decimal koma ke titik
                        $discount_agen_idr = str_replace(',', '.', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace(',', '.', $discount_kemasan_idr);
                        $ppn_idr = str_replace(',', '.', $ppn_idr);
                        $sub_total = str_replace(',', '.', $sub_total);
                        $grand_total_idr = str_replace(',', '.', $grand_total_idr);

                        // DD($sub_total);

                        $packing_order_detail = new PackingOrderDetail;
                        $packing_order_detail->do_id = $packing_order->id;
                        $packing_order_detail->discount_1 = $request->disc_agen_percent;
                        $packing_order_detail->discount_1_idr = $discount_agen_idr;
                        $packing_order_detail->discount_2 = $request->disc_kemasan_percent;
                        $packing_order_detail->discount_2_idr = $discount_kemasan_idr;
                        $packing_order_detail->discount_idr = $request->disc_tambahan_idr;
                        $packing_order_detail->voucher_idr = $request->voucher_idr;
                        $packing_order_detail->ppn = $request->ppn_percent;
                        $packing_order_detail->ppn_idr = $ppn_idr;
                        $packing_order_detail->purchase_total_idr = $sub_total;
                        $packing_order_detail->other_cost_idr = 0;
                        $packing_order_detail->grand_total_idr = $grand_total_idr;
                        $packing_order_detail->terbilang = CustomHelper::terbilang($grand_total_idr);
                        $packing_order_detail->created_by = Auth::id();
                        $packing_order_detail->save();

                        $data = [];
                        // $product = 0;
                        // $out_of_stock = false;
                        foreach($request->sku as $key => $value){
                            $so_item_id = $sales_order_ppn_detail->id;
                            $price = $request->price[$key];
                            $so_qty = $request->qty[$key];
                            $usd_disc = $request->disc[$key];
                            $percent_disc = 0;
                            $total_discount = 0;

                            if(empty($so_item_id)){
                                $errors[] = 'SO Item ID tidak boleh kosong';
                            }

                            if(empty($request->sku[$key])){
                                $errors[] = 'Product ID tidak boleh kosong';
                            }

                            $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc/100))) * $so_qty);
                            $data[] = [
                                'do_id' => $packing_order->id,
                                'product_packaging_id' => $request->sku[$key],
                                'so_item_id' => $sales_order_ppn_detail->id,
                                'packaging_id' => $request->packaging[$key],
                                'qty' => $so_qty,
                                'price' => $price,
                                'usd_disc' => $usd_disc,
                                'percent_disc' => $percent_disc,
                                'total_disc' => $total_disc,
                                'total' => floatval($so_qty * $price) - $total_disc,
                                'created_by' => Auth::id(),
                            ];
                            // DD($request->sku[$key]);

                            // // Check stock
                            // $stock_order = ProductMinStock::where('product_packaging_id', $sales_khusus_detail->product_packaging_id)->where('warehouse_id', $request->origin_warehouse_id)->get();

                            // foreach($stock_order as $key => $value){
                            //     if($key){
                            //         if($value->quantity < $request->qty[$key]){
                            //             $out_of_stock = true;
                            //             $product = $sales_khusus_detail->product_packaging_id;
                            //             break;
                            //         }else{
                            //             $out_of_stock = true;
                            //             $product = $sales_khusus_detail->product_packaging_id;
                            //             break;
                            //         }
                            //     }
                            // }
                        }

                        if (count($data) == 0) {
                            DB::rollback();
                            $errors[] =  'Not item sales order are ready';
                        }

                        foreach ($data as $key => $value) {
                            $insert = PackingOrderItem::create($data[$key]);
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

                // DD($request->invoice_ppn);
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

    public function show($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['so_khusus'] = SalesOrder::find($id);

        return view('superuser.penjualan.sales_order_ppn.show', $data);
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
            ->whereNotIn('id', [1, 2])
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
                        ->select('master_products_packaging.id as id' ,
                                    'master_products_packaging.code as ProductCode', 
                                    'master_products_packaging.name as productName', 
                                    'master_products_packaging.price as productPrice', 
                                    'master_packaging.id as  productPackagingID', 
                                    'master_packaging.pack_name as productPackaging', 
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
                    ];
                }

                return response()->json(['code' => 200, 'data' => $data]);
        }
    }
}