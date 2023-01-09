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
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Company;
use App\Entities\Master\Warehouse;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\ProductType;
use App\Entities\Master\Product;
use App\Entities\Master\Sales;
use App\Entities\Master\Ekspedisi;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use Auth;
use DB;
use PDF;

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
            if(empty($this->access)){
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
                                        $query2->whereIn('status', [1, 2, 3]);
                                        $query2->where('so_for', 1);
                                    } else if ($step === 2) { // SO lanjutan
                                        $query2->whereIn('status', [2, 4]);
                                        $query2->where('so_for', 1);
                                    } else if ($step === 9) { // SO mutasi
                                        $query2->where('so_for', 2);
                                    }
                                }
                            })
                            ->orderBy('id','DESC')
                            ->paginate(10);

        $table->withPath('?field='.$field."&search=".$search."&so_for=".$so_for);

        $customer = Customer::get();

        foreach ($table as $key => $value) {
            $so_item = new SalesOrderItem;
            $so_item = $so_item->where('so_id',$value->id);
            $so_item = $so_item->whereHas('do_item',function($query2){
                $query2->whereHas('do',function($query3){
                    $query3->where('do_code','!=','');
                });
            });
            $so_item = $so_item->count();

            if($so_item > 0){
                $value->is_do = true;
            }
            else{
                $value->is_do = false;
            }
        }

        $data = [
            'table' => $table,
            'customer' => $customer,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
            'customerids' => isset($customerDb)?$customerDb:null
        ];

        return view($this->view."index",$data);
    }

    public function get_customer(Request $request, $step = NULL)
    {
        
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
            'step_txt' => SalesOrder::STEP[$step]
        ];
        return view($this->view."detail",$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($step)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $customer = Customer::all();
        $warehouse = Warehouse::all();
        $sales = Sales::all();
        $ekspedisi = Ekspedisi::all();
        $product_category = ProductCategory::all();
        $product_type = ProductType::all();
        $data = [
            'customer' => $customer,
            'warehouse' => $warehouse,
            'sales' => $sales,
            'ekspedisi' => $ekspedisi,
            'product_category' => $product_category,
            'product_type' => $product_type,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step]
        ];
        return view($this->view."create",$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            if(empty($post["sales_senior_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales senior wajib dipilih";
                goto ResultData;
            }
            if(empty($post["sales_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales wajib dipilih";
                goto ResultData;
            }
            if(empty($post["sales_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales wajib dipilih";
                goto ResultData;
            }
            if(empty($post["customer_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Customer wajib dipilih";
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
            
            DB::beginTransaction();
            try {
                $insert = new SalesOrder;
                $insert->code = CodeRepo::generateSO();
                $insert->sales_senior_id = trim(htmlentities($post["sales_senior_id"]));
                $insert->sales_id = trim(htmlentities($post["sales_id"]));
                if ($customer["so_for"] == 1) {
                    $insert->customer_id = $customer["id"] ?? null;
                } else {
                    $insert->origin_warehouse_id = trim(htmlentities($post["origin_warehouse_id"]));
                    $insert->destination_warehouse_id = $gudang["id"] ?? null;
                }
                $insert->so_for = trim(htmlentities($customer["so_for"]));
                $insert->type_transaction = trim(htmlentities($post["type_transaction"]));
                $insert->brand_type = trim(htmlentities($post["brand_type"]));
                $insert->note = trim(htmlentities($post["note"]));
                $insert->created_by = Auth::id();
                $insert->status = $post["ajukankelanjutan"] == 1 ? 2 : 1;
                $insert->save();

                if (sizeof($post["product_id"]) > 0) {
                    for ($i = 0; $i < sizeof($post["product_id"]); $i++) {
                        if(empty($post["product_id"][$i])) continue;

                        $insertDetail = new SalesOrderItem;
                        $insertDetail->so_id = $insert->id;
                        $insertDetail->product_id = trim(htmlentities($post["product_id"][$i]));
                        $insertDetail->qty = trim(htmlentities($post["qty"][$i]));
                        $insertDetail->packaging = trim(htmlentities($post["packaging"][$i]));
                        $insertDetail->created_by = Auth::id();
                        $insertDetail->save();
                    }
                }
                
                DB::commit();

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Sales Order Berhasil Ditambahkan";
                goto ResultData;
            } catch (\Exception $e) {
                DB::rollback();

                // dd($e);
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales Order Gagal Ditambahkan";
    
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
    public function store_item(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            if(empty($post["so_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "ID Sales Order tidak boleh kosong";
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
            
            $get_so_item = SalesOrderItem::where('so_id',$post["so_id"])
                                         ->where('product_id',$post["product_id"])
                                         ->where('packaging',$post["packaging"])
                                         ->first();
            if($get_so_item){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Item sudah ada";
                goto ResultData;
            }
            $data = [
                'so_id' => trim(htmlentities($post["so_id"])),
                'product_id' => trim(htmlentities($post["product_id"])),
                'qty' => trim(htmlentities($post["qty"])),
                'packaging' => trim(htmlentities($post["packaging"])),
                'created_by' => Auth::id(),
            ];

            $insert = SalesOrderItem::create($data);

            if($insert){
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Item Berhasil Ditambahkan ke SO";
                goto ResultData;
            }
            else{
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Item Gagal Ditambahkan ke SO";
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

        //$result = SalesOrder::where('id',$id)->first();
        $result = SalesOrder::find($id);
        if(empty($result)){
            abort(404);
        }
        $customer = Customer::all();
        $warehouse = Warehouse::all();
        $sales = Sales::all();
        $product_category = ProductCategory::all();
        $product_type = ProductType::all();
        $ekspedisi = Ekspedisi::all();

        $data = [
            'customer' => $customer,
            'warehouse' => $warehouse,
            'sales' => $sales,
            'product_category' => $product_category,
            'product_type' => $product_type,
            'ekspedisi' => $ekspedisi,
            'result' => $result,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
            'packaging_dictionary' => SalesOrderItem::PACKAGING
        ];
        if ($step == 2) {
            $doList = $result->customer->do;
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
            if(($step == 2) && empty($post["origin_warehouse_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Origin gudang wajib dipilih";
                goto ResultData;
            }
            if(($step == 2) && empty($post["type_transaction"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Type transaction wajib dipilih";
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
                    $sales_order->customer_id = $customer["id"] ?? null;
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
                if (sizeof($post["product_id"]) > 0) {
                    for ($i = 0; $i < sizeof($post["product_id"]); $i++) {
                        if(empty($post["product_id"][$i])) continue;

                        $insertDetail = new SalesOrderItem;
                        $insertDetail->so_id = $sales_order->id;
                        $insertDetail->product_id = trim(htmlentities($post["product_id"][$i]));
                        $insertDetail->qty = trim(htmlentities($post["qty"][$i]));
                        $insertDetail->packaging = trim(htmlentities($post["packaging"][$i]));
                        $insertDetail->created_by = Auth::id();
                        $insertDetail->save();
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
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $update = SalesOrder::where('id',$post["id"])->update(['status' => 2]);

            DB::commit();
            return redirect()->back()->with('success','Sales Order berhasil diajukan untuk dilanjutkan');  
            
        }catch(\Throwable $e){
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
            return redirect()->back()->with('success','Sales Order berhasil diajukan untuk dilanjutkan');  
            
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

    public function tutup_so(Request $request) {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            $sales_order = SalesOrder::find($post["id"]);
            if(empty($sales_order)){
                abort(404);
            }

            if(empty($post["origin_warehouse_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Origin gudang wajib dipilih";
                goto ResultData;
            }
            if(empty($post["idr_rate"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "IDR rate wajib dipilih";
                goto ResultData;
            }
            if(count($post["repeater"]) == 0){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Not item sales order are ready";
                goto ResultData;
            }

            DB::beginTransaction();
            try {
                $company = Company::first();

                $sales_order->origin_warehouse_id = trim(htmlentities($post["origin_warehouse_id"]));
                $sales_order->status = 4;
                $sales_order->updated_by = Auth::id();
                $sales_order->save();

                $categories = SalesOrderItem::select('master_products.category_id')
                                            ->where('so_id', $post["id"])
                                            ->join('master_products', 'master_products.id', '=', 'penjualan_so_item.product_id')
                                            ->groupBy('master_products.category_id')->get();
                
                $jumlahitem = 0;
                foreach($categories as $category) {
                    $packing_order = new PackingOrder;
                    $packing_order->code = CodeRepo::generatePO();
                    $packing_order->so_id  = $sales_order->id;
                    $packing_order->customer_id  = $sales_order->customer_id;
                    $packing_order->warehouse_id = $sales_order->origin_warehouse_id;
                    $packing_order->type_transaction  = $sales_order->type_transaction;
                    $packing_order->idr_rate = trim(htmlentities($post["idr_rate"]));
                    $packing_order->other_address = 0;
                    $packing_order->note = $company->note;
                    $packing_order->status = 1;
                    $packing_order->created_by = Auth::id();
                    $packing_order->save();

                    $packing_order_detail = new PackingOrderDetail;
                    $packing_order_detail->do_id = $packing_order->id;
                    $packing_order_detail->created_by = Auth::id();
                    $packing_order_detail->save();
                    
                    $data = [];
                    foreach ($post["repeater"] as $key => $value) {
                        if (empty($value["so_qty"]) || (!empty($value["so_qty"]) && $value["so_qty"] <= 0)) {
                            continue;
                        }

                        $result = SalesOrderItem::where('id',$value["so_item_id"])->first();
                        if ($result->product->category->id !== $category->category_id) {
                            continue;
                        }

                        $jumlahitem = $jumlahitem + 1;

                        $so_item_id = $value["so_item_id"];
                        $price = $value["price"];
                        $so_qty = $value["so_qty"];
                        $do_qty = $value["do_qty"];
                        $rej_qty = $so_qty - $do_qty;
                        $usd_disc = 0;
                        $percent_disc = 0;
                        $total_discount = 0;
                    
                        if(empty($value["so_item_id"])){
                            $data_json["IsError"] = TRUE;
                            $data_json["Message"] = "SO Item ID tidak boleh kosong";
                            goto ResultData;
                        }
                        if(empty($value["product_id"])){
                            $data_json["IsError"] = TRUE;
                            $data_json["Message"] = "Product ID tidak boleh kosong";
                            goto ResultData;
                        }

                        if(empty($value["price"])){
                            $data_json["IsError"] = TRUE;
                            $data_json["Message"] = "Harga produk tidak boleh 0";
                            goto ResultData;
                        }

                        $qty_total = $do_qty + $rej_qty;
                        $sisa = $so_qty - $do_qty;

                        if($so_qty < $qty_total){
                            $data_json["IsError"] = TRUE;
                            $data_json["Message"] = "Jumlah DO,REJ melebihi SO Qty";
                            goto ResultData;
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
                                'product_id' => $value["product_id"],
                                'so_item_id' => $value["so_item_id"],
                                'packaging' => $result->packaging,
                                'qty' => $do_qty,
                                'price' => $price,
                                'usd_disc' => $usd_disc,
                                'percent_disc' => $percent_disc,
                                'usd_disc' => $usd_disc,
                                'total_disc' => $total_disc,
                                'total' => floatval($do_qty * $price) - $total_disc ,
                                'note' => trim(htmlentities($value["note"])),
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
                        $data_json["IsError"] = TRUE;
                        $data_json["Message"] = "Not item sales order are ready";
                        goto ResultData;
                    }
                    foreach ($data as $key => $value) {
                        $insert = PackingOrderItem::create($data[$key]);
                    }
                    app('App\Http\Controllers\Superuser\Penjualan\PackingOrderController')->reset_cost($packing_order->id);

                }
                    
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

    public function get_product(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "GET"){
            $table = Product::where(function($query2) use($post){
                        if(!empty($post["category_id"])){
                            $query2->where('category_id',$post["category_id"]);
                        }
                        if(!empty($post["type_id"])){
                            $query2->where('type_id',$post["type_id"]);
                        }
                    })->get();

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
}
