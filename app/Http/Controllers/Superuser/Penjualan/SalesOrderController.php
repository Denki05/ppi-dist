<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
use App\Repositories\CodeRepo;
use App\Helper\CustomHelper;
use Auth;
use DB;
use PDF;
use COM;
use Carbon;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.penjualan.sales_order.";
        $this->route = "superuser.penjualan.sales_order";
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

    public function index(Request $request, $step = NULL)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $field = $request->input('field');
        $search = $request->input('search');
        $so_for = $request->input('so_for');

        $table = SalesOrder::where(function($query2) use($field,$search,$so_for,$step){
                                if(!empty($field) && !empty($search)) {
                                    $fieldDb = '';
                                    $ids = array();
                                    if ($field == 'customer') {
                                        $customerDb = Customer::where('name', 'like', '%'.$search.'%')->get();
                                        for($c=0; $c<count($customerDb); $c++) $ids[$c] = $customerDb[$c]->id;
                                        $fieldDb = 'customer_id';
                                    } else if ($field == 'sales') {
                                        $salesDb = Sales::where('name', 'like', '%'.$search.'%')->get();
                                        for($c=0; $c<count($salesDb); $c++) $ids[$c] = $salesDb[$c]->id;
                                        $fieldDb = 'sales_id';
                                    } else if ($field == 'transaksi') {
                                        if (str_contains('cash', strtolower($search))) {
                                            $ids = [1];
                                        } else if (str_contains('tempo', strtolower($search))) {
                                            $ids = [2];
                                        } else if (str_contains('marketplace', strtolower($search))) {
                                            $ids = [3];
                                        }
                                        $fieldDb = 'type_transaction';
                                    }
                                    
                                    
                                    if ($fieldDb != '') {
                                        $query2->where(function($query3)  use ($field, $fieldDb, $ids){
                                            if ($field == 'sales') {
                                                $query3->where('sales_senior_id',$ids);
                                                $query3->orWhereIn('sales_id',$ids);
                                            } else {
                                                $query3->whereIn($fieldDb, $ids);
                                            }
                                        });
                                    } else {
                                        $query2->where($field, 'like', '%'.$search.'%');
                                    }
                                }
                                if(!empty($so_for)){
                                    $query2->where('so_for','=',$so_for);
                                }
                                if(!empty($step)){
                                    if ($step === 1) { // SO awal
                                        $query2->whereIn('status', [1, 2, 3, 4]);
                                        $query2->where('so_for', 1);
                                    } else if ($step === 2) { // SO lanjutan
                                        $query2->whereIn('status', [2, 4]);
                                        $query2->where('so_for', 1);
                                    } else if ($step === 9) { // SO mutasi
                                        $query2->where('so_for', 2);
                                    }
                                }
                            })
                            ->where('type_so', 'nonppn')
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

        return view($this->view."index",$data);
    }
    
    public function index_awal(Request $request)
    {
        return $this->index($request, 1);
    }
    
    public function index_lanjutan(Request $request)
    {
        return $this->index($request, 2);
    }
    
    public function index_mutasi(Request $request)
    {
        return view("superuser.coming-soon");
        return $this->index($request, 9);
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
        return view($this->view."detail",$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $store, $step, $member)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $brand = BrandLokal::all();
        $products = Product::all();
        $customers = Customer::find($store);
        $other_address = CustomerOtherAddress::find($member);
        $warehouse = Warehouse::all();
		$ekspedisi = Ekspedisi::all();
        $sales = Sales::where('is_active', 1)->get();
        $product_category = ProductCategory::get();
        $type_transaction = SalesOrder::TYPE_TRANSACTION;
        $rekenings = SalesOrder::REKENING;

        $data = [
            'customers' => $customers,
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
        
        
        return view($this->view."create",$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $store, $member)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            $customer = [];
            $gudang = [];
            if(!empty($post["customer_id"])){
                $customer["id"] = empty($post["customer_id"]) ? null : $post["customer_id"];
                $customer["so_for"] = 1;
            }
            else{
                $gudang["id"] = empty($post["destination_warehouse_id"]) ? null : $post["destination_warehouse_id"];
                $customer["so_for"] = 2;
            }

            DB::beginTransaction();
            try {
                if(empty($post["sales_senior_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Sales Senior wajib dipilih";
                    goto ResultData;
                }
                if(empty($post["sales_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Sales wajib dipilih";
                    goto ResultData;
                }
                if(empty($post["idr_rate"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "IDR Rate tidak boleh kosong";
                    goto ResultData;
                }

                $insert = new SalesOrder;
                $insert->code = CodeRepo::generateSO();
                $insert->so_code = CodeRepo::generateSoAwal();
                $insert->customer_id = $member;
                $insert->customer_other_address_id = $store;
                $insert->sales_senior_id = $request->sales_senior_id;
                $insert->sales_id = $request->sales_id;
                $insert->so_for = 1;
                // $insert->type_transaction = $request->type_transaction;
                $insert->so_date = Carbon\Carbon::now();
                $insert->type_so = 'nonppn';
                $insert->idr_rate = $request->idr_rate;
                $insert->note = $request->note;
                $insert->created_by = Auth::id();
                $insert->status = $post["ajukankelanjutan"] == 1 ? 2 : 1;
                $insert->condition = 1;
                $insert->payment_status = 0;
                $insert->count_rev = 0;
                $insert->save();

                if (sizeof($post["product_id"]) > 0) {
                    for ($i = 0; $i < sizeof($post["product_id"]); $i++) {
                        if(empty($post["product_id"][$i])) continue;

                        $duplicate_product = [];
                        $duplicate = false;
                        $listItem[] = [
                            'product_id' => $post["product_id"][$i],
                            'free_product' => $post["free_product"][$i],
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
                            $data_json["IsError"] = TRUE;
                            $data_json["Message"] = "Item sudah ada";
                            goto ResultData;
                        }else{
                            $insertDetail = new SalesOrderItem;
                            $insertDetail->so_id = $insert->id;
                            $insertDetail->product_packaging_id = trim(htmlentities(implode("-", [$post["product_id"][$i],$post["packaging_id"][$i]])));
                            $insertDetail->qty = trim(htmlentities($post["qty"][$i]));
                            $insertDetail->disc_usd = trim(htmlentities($post["usd"][$i]));
                            $insertDetail->packaging_id = trim(htmlentities($post["packaging_id"][$i]));
                            $insertDetail->free_product = trim(htmlentities($post["free_product"][$i]));
                            $insertDetail->created_by = Auth::id();
                            $insertDetail->save();
                        }
                    }
                }
                
                DB::commit();
                
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Sales Order Berhasil Ditambahkan";
                goto ResultData;
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = $e->getMessage();
    
                return response()->json($data_json,400);
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

    // public function store_item(Request $request)
    // {
    //     $data_json = [];
    //     $post = $request->all();
    //     if($request->method() == "POST"){
    //         if(empty($post["so_id"])){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "ID Sales Order tidak boleh kosong";
    //             goto ResultData;
    //         }
    //         if(empty($post["product_id"])){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Product wajib dipilih";
    //             goto ResultData;
    //         }
    //         if(empty($post["qty"])){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Quantity tidak boleh kosong";
    //             goto ResultData;
    //         }
    //         if(empty($post["packaging"])){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Packaging tidak boleh kosong";
    //             goto ResultData;
    //         }
            
    //         $get_so_item = SalesOrderItem::where('so_id',$post["so_id"])
    //                                      ->where('product_id',$post["product_id"])
    //                                      ->where('packaging',$post["packaging"])
    //                                      ->first();
    //         if($get_so_item){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Item sudah ada";
    //             goto ResultData;
    //         }
    //         $data = [
    //             'so_id' => trim(htmlentities($post["so_id"])),
    //             'product_id' => trim(htmlentities($post["product_id"])),
    //             'qty' => trim(htmlentities($post["qty"])),
    //             'packaging' => trim(htmlentities($post["packaging"])),
    //             'created_by' => Auth::id(),
    //         ];

    //         $insert = SalesOrderItem::create($data);

    //         if($insert){
    //             $data_json["IsError"] = FALSE;
    //             $data_json["Message"] = "Item Berhasil Ditambahkan ke SO";
    //             goto ResultData;
    //         }
    //         else{
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Item Gagal Ditambahkan ke SO";
    //             goto ResultData;
    //         }
    //     }
    //     else{
    //         $data_json["IsError"] = TRUE;
    //         $data_json["Message"] = "Invalid Method";
    //         goto ResultData;
    //     }
    //     ResultData:
    //     return response()->json($data_json,200);
    // }

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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $step)
    {
        // Access
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
        $customers = Customer::get();
        $warehouse = Warehouse::all();
        $sales = Sales::all();
        $product_category = ProductCategory::all();
        $brand = BrandLokal::get();
        $ekspedisi = Vendor::where('type', 1)->get();
        $packaging = Packaging::get();
        $rekening = DB::table('rekening')->get();

        $data = [
            'customers' => $customers,
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
        if ($step == 2) {
            $doList = $result->member->do;
            $invoiceList = [];
            for ($i = 0; $i < sizeof($doList); $i++) {
                $do = $doList[$i];
                if (isset($do->invoicing)) {
                    $total_payable = 0;
                    for ($j = 0; $j < sizeof($do->invoicing->payable_detail); $j++) {
                        $payable_d = $do->invoicing->payable_detail[$j];
                        $total_payable += $payable_d->total;
                    }
                    if ($total_payable < $do->invoicing->grand_total_idr) {
                        // Ambil yang belum lunas terbayar
                        array_push($invoiceList, $do->invoicing);
                    }
                }
            }
            $data['customer_history'] = $invoiceList;
        }

        if ($step == 1 || $step == 9) {
            return view($this->view."edit",$data);
        } else if ($step == 2) {
            return view($this->view."create_lanjutan",$data);
        }
    }

    public function edit_item($id)
    {
        $result = SalesOrderItem::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $product_category = ProductCategory::all();
        $product_type = ProductType::all();
        $data = [
            'product_category' => $product_category,
            'product_type' => $product_type,
            'result' => $result,
        ];
        return view($this->view."edit_item",$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            $step = $post["step"];

            if(empty($post["id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "ID Sales Order tidak boleh kosong";
                goto ResultData;
            }
            if(($step == 1) && empty($post["sales_senior_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales senior wajib dipilih";
                goto ResultData;
            }
            if(($step == 1) && empty($post["sales_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales wajib dipilih";
                goto ResultData;
            }

            $customer = [];
            $gudang = [];
            if(!empty($post["customer_id"])){
                $customer["id"] = empty($post["customer_id"]) ? null : $post["customer_id"];
                $customer["so_for"] = 1;
            }
            else{
                $gudang["id"] = empty($post["destination_warehouse_id"]) ? null : $post["destination_warehouse_id"];
                $customer["so_for"] = 2;
            }
            
            $sales_order = SalesOrder::find($post["id"]);

            DB::beginTransaction();
            try {
                
                if ($step == 1) {
                    $sales_order->sales_senior_id = trim(htmlentities($post["sales_senior_id"]));
                    $sales_order->sales_id = trim(htmlentities($post["sales_id"]));
                    $sales_order->idr_rate = trim(htmlentities($post["idr_rate"]));
                    $sales_order->note = trim(htmlentities($post["note"]));
                    $sales_order->updated_by = Auth::id();
                    $sales_order->status = $step;
                } else if ($step == 2) {
                    // di set statusnya, kalau dari front end dia di cancel, tidak di forward, maka status jadi 3 => awal perlu revisi
                    $data = [
                        'origin_warehouse_id' => trim(htmlentities($post["origin_warehouse_id"])),
                        'destination_warehouse_id' => $gudang["id"] ??  null,
                        'type_transaction' => trim(htmlentities($post["type_transaction"])),
                        'updated_by' => Auth::id(),
                        'status' => $step,
                        'ekspedisi_id' => (empty($post["ekspedisi_id"])) ? null : $post["ekspedisi_id"],
                    ];
                }
                $sales_order->save();
                
                SalesOrderItem::where('so_id', $post["id"])->delete();
                // if (sizeof($post["product_id"]) > 0) {
                //     for ($i = 0; $i < sizeof($post["product_id"]); $i++) {
                //         if(empty($post["product_id"][$i])) continue;

                //         $insertDetail = new SalesOrderItem;
                //         $insertDetail->so_id = $sales_order->id;
                //         $insertDetail->product_id = trim(htmlentities($post["product_id"][$i]));
                //         $insertDetail->qty = trim(htmlentities($post["qty"][$i]));
                //         $insertDetail->packaging = trim(htmlentities($post["packaging"][$i]));
                //         $insertDetail->created_by = Auth::id();
                //         $insertDetail->save();
                //     }
                // }

                if (sizeof($post["product_id"]) > 0) {
                    for ($i = 0; $i < sizeof($post["product_id"]); $i++) {
                        if(empty($post["product_id"][$i])) continue;

                        $duplicate_product = [];
                        $duplicate = false;
                        $listItem[] = [
                            'product_id' => $post["product_id"][$i],
                            'free_product' => $post["free_product"][$i],
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
                            $data_json["IsError"] = TRUE;
                            $data_json["Message"] = "Item sudah ada";
                            goto ResultData;
                        }else{
                            $insertDetail = new SalesOrderItem;
                            $insertDetail->so_id = $sales_order->id;
                            $insertDetail->product_packaging_id =  trim(htmlentities($post["product_id"][$i]));
                            $insertDetail->qty = trim(htmlentities($post["qty"][$i]));
                            $insertDetail->disc_usd = trim(htmlentities($post["usd"][$i]));
                            $insertDetail->packaging_id = trim(htmlentities($post["packaging_id"][$i]));
                            $insertDetail->free_product = trim(htmlentities($post["free_product"][$i]));
                            $insertDetail->created_by = Auth::id();
                            $insertDetail->save();
                        }
                    }
                }
                    
                DB::commit();

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Sales Order Berhasil Diubah";
                goto ResultData;
            } catch (\Exception $e) {

                dd($e);
                DB::rollback();

                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales Order Gagal Diubah";
    
                return response()->json($data_json,400);
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
    public function update_item(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            if(empty($post["id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "ID item so tidak boleh kosong";
                goto ResultData;
            }
            if(empty($post["product_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Product wajib dipilih";
                goto ResultData;
            }
            if(empty($post["qty"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Quantity tidak boleh kosong";
                goto ResultData;
            }
            if(empty($post["packaging"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Packaging tidak boleh kosong";
                goto ResultData;
            }

            $result = $update = SalesOrderItem::where('id',$post["id"])->first();
            $get_so_item = SalesOrderItem::where('id','!=',$post["id"])
                                         ->where('so_id',$result->so_id)
                                         ->where('product_id',$post["product_id"])
                                         ->where('packaging',$post["packaging"])
                                         ->first();
            if($get_so_item){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Item sudah ada";
                goto ResultData;
            }
            $data = [
                'product_id' => trim(htmlentities($post["product_id"])),
                'qty' => trim(htmlentities($post["qty"])),
                'packaging' => trim(htmlentities($post["packaging"])),
                'updated_by' => Auth::id(),
            ];

            $update = SalesOrderItem::where('id',$post["id"])->update($data);

            if($update){
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Item Berhasil Diubah dan Ditambahkan ke SO";
                goto ResultData;
            }
            else{
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Item Gagal Diubah dan Ditambahkan ke SO";
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

    public function lanjutkan(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();
        try{
            $sales_order = SalesOrder::find($request->id);

            $sales_order->status = 2;
            if($sales_order->save()){
                DB::commit();
                return redirect()->back()->with('success','Sales Order berhasil diajukan untuk dilanjutkan'); 
            } 
        }catch(\Throwable $e){
            // dd($e);
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function kembali(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $update = SalesOrder::where('id',$post["id"])->update(['status' => 1]);

            DB::commit();
            return redirect()->back()->with('success','Sales Order tidak di lanjutkan');  
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
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
            return redirect()->back()->with('success','SO berhasil dihapus');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
    public function destroy_item(Request $request)
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
            $update = SalesOrderItem::where('id',$post["id"])->update(['deleted_by' => Auth::id()]);
            $destroy = SalesOrderItem::where('id',$post["id"])->delete();
            $check_do_item = PackingOrderItem::where('so_item_id',$post["id"])->first();
            $check_do_mutation_item = DeliveryOrderMutationItem::where('so_item_id',$post["id"])->first();
            if($check_do_item || $check_do_mutation_item){
                return redirect()->back()->with('error','Gagal menghapus SO . Item di SO sudah digunakan di Packing Order / Delivery Order Mutation');
            }
            DB::commit();
            return redirect()->back()->with('success','Item berhasil dihapus');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function tidak_lanjut_so(Request $request) {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            $sales_order = SalesOrder::find($post["id"]);
            if(empty($sales_order)){
                abort(404);
            }

            if(empty($post["keterangan"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Keterangan wajib diisi";
                goto ResultData;
            }

            DB::beginTransaction();
            try {
                $sales_order->keterangan_tidak_lanjut = trim(htmlentities($post["keterangan"]));
                $sales_order->status = 3;
                $sales_order->save();
                    
                DB::commit();

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Sales Order Berhasil Diubah";
                goto ResultData;
            } catch (\Exception $e) {
                DB::rollback();

                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales Order Gagal Diubah, ".$e;
    
                return response()->json($data_json,400);
            }
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            return response()->json($data_json,400);
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function tutup_so(Request $request)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_accses == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();
            try{
                $errors = [];
                
                $sales_order = SalesOrder::find($request->id);

                if($sales_order === null){
                    abort(404);
                }

                if($sales_order->count_rev == 0){
                    if($request->origin_warehouse_id == null){
                        $errors[] = 'Warehouse tidak boleh kosong!';
                    }

                    if($request->rekening == null){
                        $errors[] = 'Rekening tidak boleh kosong!';
                    }

                    $sales_order->origin_warehouse_id = $request->origin_warehouse_id;
                    $sales_order->ekspedisi_id = $request->ekspedisi ?? null;
                    $sales_order->so_date = date("y-m-d", strtotime($request->so_date));
                    $sales_order->rekening = $request->rekening;
                    $sales_order->type_transaction = $request->type_transaction;
                    $sales_order->shipping_cost_buyer = $request->shipping_cost_buyer ?? 0;
                    $sales_order->status = 4;
                    $sales_order->count_rev = 0;
                    $sales_order->updated_by = Auth::id();
                    if($sales_order->save()){
                        $packing_order = new PackingOrder;
                        $packing_order->code = CodeRepo::generatePO();
                        $packing_order->do_code = $sales_order->code;
                        $packing_order->so_id  = $sales_order->id;
                        $packing_order->customer_id  = $sales_order->customer_id;
                        $packing_order->customer_other_address_id  = $sales_order->customer_other_address_id;
                        $packing_order->warehouse_id = $sales_order->origin_warehouse_id;
                        $packing_order->type_transaction  = $sales_order->type_transaction;
                        $packing_order->idr_rate = $request->idr_rate;
                        $packing_order->other_address = 0 ?? Null;
                        $packing_order->note = $company->note ?? null;
                        $packing_order->vendor_id = $sales_order->ekspedisi_id ?? null;
                        $packing_order->status = 2;
                        $packing_order->count_cancel = 0;
                        $packing_order->created_by = Auth::id();
                        $packing_order->save();

                        // definisi hasil penjumlahan di view
                        $discount_agen_idr = $request->disc_agen_idr;
                        $discount_kemasan_idr = $request->disc_kemasan_idr;
                        $sub_total = $request->subtotal_2;
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

                        $packing_order_detail = new PackingOrderDetail;
                        $packing_order_detail->do_id = $packing_order->id;
                        $packing_order_detail->discount_1 = $request->disc_agen_percent;
                        $packing_order_detail->discount_1_idr = $discount_agen_idr;
                        $packing_order_detail->discount_2 = $request->disc_kemasan_percent;
                        $packing_order_detail->discount_2_idr = $discount_kemasan_idr;
                        $packing_order_detail->discount_idr = $request->disc_tambahan_idr;
                        $packing_order_detail->voucher_idr = $request->voucher_idr;
                        $packing_order_detail->purchase_total_idr = $sub_total;
                        if($sales_order->shipping_cost_buyer == 0){
                            $packing_order_detail->delivery_cost_idr = $request->delivery_cost_idr;
                        }elseif($sales_order->shipping_cost_buyer == 1){
                            $packing_order_detail->delivery_cost_idr = 0;
                        }
                        $packing_order_detail->other_cost_idr = 0;
                        $packing_order_detail->grand_total_idr = $grand_total_idr;
                        $packing_order_detail->terbilang = CustomHelper::terbilang($grand_total_idr);
                        $packing_order_detail->created_by = Auth::id();
                        $packing_order_detail->save();

                        $data = [];
                        $product = 0;
                        $out_of_stock = false;
                        foreach($request->repeater as $key => $value){
                            $result = SalesOrderItem::where('id',$value["so_item_id"])->first();

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

                            // Check Stock
                            foreach($sales_order->so_detail as $detail){
                                $stock_order = ProductMinStock::where('product_packaging_id', $detail->product_packaging_id)->where('warehouse_id', $request->origin_warehouse_id)->first();

                                // if($stock_order){
                                //     if($stock->quantity < $detail->qty){
                                //         $out_of_stock = true;
                                //         $product = $detail->product_packaging_id;
                                //         break;
                                //     }
                                // }
                                if($stock_order) {
                                    if($stock_order->quantity < $detail->qty) {
                                        $out_of_stock = true;
                                        $product = $detail->product_packaging_id;
                                        break;
                                    }
                                } else {
                                    $out_of_stock = true;
                                    $product = $detail->product_packaging_id;
                                    break;
                                }
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

                        if($out_of_stock){
                            $product = ProductPack::find($product);
                            $errors[] = 'Out Of Stock! <b>'.$product->name.'</b> Please contact Administrator';
                            DB::rollback();
                        }else{
                            foreach ($data as $key => $value) {
                                $insert = PackingOrderItem::create($data[$key]);
                            }
                        }

                        // Cetak Invoice disini
                        if(empty($packing_order->invoicing)){
                            $data = [
                                'code' => $sales_order->code,
                                'do_id' => $packing_order->id,
                                'customer_id' => $sales_order->customer_id,
                                'customer_other_address_id' => $sales_order->customer_other_address_id,
                                'grand_total_idr' => $packing_order_detail->grand_total_idr,
                                'created_by' => Auth::id(),
                            ];

                            $insert_invoice = Invoicing::create($data);
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
                
                            $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                            return $this->response(200, $response);
                        }
                    }
                }elseif($sales_order->count_rev == 1){
                    $sales_order->origin_warehouse_id = $request->origin_warehouse_id;
                    $sales_order->status = 4;
                    $sales_order->count_rev = 0;
                    $sales_order->updated_by = Auth::id();

                    $valuePoDetail = [];
                    if($sales_order->save()){
                        
                        $jumlahitem = 0;
                        $data = [];

                        $get_po = PackingOrder::where('so_id', $sales_order->id)->first();

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

                            // if(empty($value["so_item_id"])){
                            //     $errors[] = 'SO Item ID tidak boleh kosong';
                            // }
                            // if(empty($value["product_id"])){
                            //     $errors[] = 'Product ID tidak boleh kosong';
                            // }
                            // if(empty($value["price"])){
                            //     $errors[] = 'Harga tidak boleh kosong';
                            // }

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
                            'status' => 2
                        ]);

                        // definisi hasil penjumlahan di view
                        $discount_agen_idr = $request->disc_agen_idr;
                        $discount_kemasan_idr = $request->disc_kemasan_idr;
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

                        // if ($failed) {
                        //     $response['failed'] = $failed;
        
                        //     return $this->response(200, $response);
                        // }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
            
                        $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                        return $this->response(200, $response);
                    }
                }

            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $failed,
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function ajax_customer_detail(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                $result = Customer::where('id',$post["id"])->first();
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

    public function ajax_warehouse_detail(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                $result = Warehouse::where('id',$post["id"])->first();

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

    public function print_rejected_so($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrder::where('id',$id)->first();
        $company = Company::first();
        if(empty($result)){
            abort(404);
        }

        $data = [
            'result' => $result,
            'company' => $company
        ];

        $pdf = PDF::loadview($this->view."print_rejected_so",$data)->setPaper('a5','potrait');
        return $pdf->stream($result->code ?? '');
    }

    public function print_proforma($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrder::where('id',$id)->first();

        // GET DO & ITEM
        $get_do = PackingOrder::where('so_id', $result->id)->first();

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\proforma\\proforma.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\proforma\\export\\'.$result->code.'.pdf';
       
        //- Variables - Server Information 
        $my_server = "DEV-SERVER"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";


        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{penjualan_do.id}= $get_do->id";


        //export to PDF process
        $creport->ExportOptions->DiskFileName=$my_pdf; //export to pdf
        $creport->ExportOptions->PDFExportAllPages=true;
        $creport->ExportOptions->DestinationType=1; // export to file
        $creport->ExportOptions->FormatType=31; // PDF type
        $creport->Export(false);

        //------ Release the variables ------
        $creport = null;
        $crapp = null;
        $ObjectFactory = null;

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\proforma\\export\\'.$result->code.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function get_product(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "GET"){
            $table = Product::where(function($query2) use($post){
                        if(!empty($post["brand_name"])){
                            $query2->where('brand_name', $post["brand_name"]);
                        }
                    })
                    ->selectRaw(
                        'master_products.id as id, 
                        master_products.name as productName, 
                        master_products.code as productCode, 
                        master_products.selling_price as productPrice'
                    )
                    ->get();
            $data_json["IsError"] = FALSE;
            $data_json["Data"] = $table;
            goto ResultData;
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function get_packaging(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "GET"){
            $table = ProductPack::where(function($query2) use($post){
                if(!empty($post["product_id"])){
                    $query2->where('product_id', $post["product_id"]);
                }
            })
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->selectRaw(
                'master_packaging.id, master_packaging.pack_name'
            )
            ->get();
            $data_json["IsError"] = FALSE;
            $data_json["Data"] = $table;
            goto ResultData;
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function delete_lanjutan(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $failed = "";

            DB::beginTransaction();

            try{

                $sales_order = SalesOrder::find($id);

                if($sales_order){
                    if($sales_order->count_rev){
                        $failed = 'Invoice sudah terbuat!';
                    }
                }

                $sales_order->deleted_by = Auth::id();
                $sales_order->delete();


                foreach($sales_order->so_detail as $detail){
                    $item = SalesOrderItem::where('id', $detail->id)->get();

                    foreach($item as $data){
                        SalesOrderItem::find($data->id)->delete();
                    }
                }

                if ($failed) {
                    $response['failed'] = $failed;

                    return $this->response(200, $response);
                }

                if($sales_order->save()){
                    DB::commit();
                    $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                // DD($e);
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

    public function indent(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();

            try{

                $result = SalesOrder::find($id);

                if($result == null){
                    abort(404);
                }

                $result->so_indent = 1;
                $result->code = null;
                $result->updated_by = Auth::id();

                if($result->save()){
                    DB::commit();
                    $response['redirect_to'] = route('superuser.penjualan.sales_order_indent.index');
                    return $this->response(200, $response);
                }

            }catch (\Exception $e) {
                DB::rollback();
                // DD($e);
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
}
