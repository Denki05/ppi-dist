<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
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
use Auth;
use DB;
use PDF;
use COM;

class SalesOrderPpnController extends Controller
{

    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view('superuser.penjualan.sales_order_ppn.index');
    }

    public function create(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $customer = Customer::get();
        $member = CustomerOtherAddress::get();
        $sales = Sales::where('is_active', 1)->get();
        $ekspedisi = Vendor::where('type', 1)->get();
        $warehouse = Warehouse::get();
        $brand = BrandLokal::get();
        $product_category = ProductCategory::get();


        $data = [
            'customer' => $customer,
            'member' => $member,
            'sales' => $sales,
            'ekspedisi' => $ekspedisi,
            'warehouse' => $warehouse,
            'brand' => $brand,
            'product_category' => $product_category,
        ];

        return view('superuser.penjualan.sales_order_ppn.create', $data);
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
}