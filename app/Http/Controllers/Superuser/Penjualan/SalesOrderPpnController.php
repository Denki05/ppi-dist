<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
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


        $data = [
            // 'customer' => $customer,
            'member' => $member,
            'sales' => $sales,
            'ekspedisi' => $ekspedisi,
            'warehouse' => $warehouse,
            'brand' => $brand,
            'product_category' => $product_category,
        ];

        return view('superuser.penjualan.sales_order_ppn.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {

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

    // public function lanjutkan(Request $request)
    // {
    //     // Access
    //     if(Auth::user()->is_superuser == 0){
    //         if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
    //             return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
    //         }
    //     }

    //     DB::beginTransaction();
    //     try{
    //         $request->validate([
    //             'id' => 'required'
    //         ]);
    //         $post = $request->all();
    //         $update = SalesOrder::where('id',$post["id"])->update(['status' => 4]);

    //         DB::commit();
    //         return redirect()->back()->with('success','Sales Order berhasil diajukan untuk dilanjutkan');  
            
    //     }catch(\Throwable $e){
    //         // dd($e);
    //         DB::rollback();
    //         return redirect()->back()->with('error',$e->getMessage());
    //     }
    // }

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
}