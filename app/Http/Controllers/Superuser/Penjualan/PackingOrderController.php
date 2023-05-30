<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Finance\Invoicing;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Customer;
use App\Entities\Master\Dokumen;
use App\Entities\Master\Vendor;
use App\Repositories\MasterRepo;
use App\Entities\Master\CustomerSaldoLog;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Company;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SoProforma;
use App\Entities\Penjualan\SoProformaDetail;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use Auth;
use DB;
use PDF;

class PackingOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.penjualan.packing_order.";
        $this->route = "superuser.penjualan.packing_order";
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
        
        $field = $request->input('field');
        $search = $request->input('search');
        $customer_id = $request->input('customer_id');
        $type_transaction = $request->input('type_transaction');
        $table = PackingOrder::where(function($query2) use($field,$search){
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
                                    } else if ($field == 'referensiSO') {
                                        // Cari SO > lalu cari SO Item > lalu cari DO Item > lalu cari Id nya

                                        // Cari SO
                                        $salesOrderDb = SalesOrder::where('code', 'like', '%'.$search.'%')->get();
                                        $salesOrderId = array();
                                        for($c=0; $c<count($salesOrderDb); $c++) $salesOrderId[$c] = $salesOrderDb[$c]->id;

                                        // Cari SO Item
                                        $salesOrderItemDb = SalesOrderItem::whereIn('so_id', $salesOrderId)->get();
                                        $salesOrderItemId = array();
                                        for($c=0; $c<count($salesOrderItemDb); $c++) $salesOrderItemId[$c] = $salesOrderItemDb[$c]->id;

                                        // Cari DO Item
                                        $packingOrderItemDb = PackingORderItem::whereIn('so_item_id', $salesOrderItemId)->get();
                                        for($c=0; $c<count($packingOrderItemDb); $c++) $ids[$c] = $packingOrderItemDb[$c]->do_id;

                                        $fieldDb = 'id';
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
                            })
                            //->whereIn('status', [1, 2, 3])
                            ->orderBy('id','DESC')
                            ->paginate(10);
        $table->withPath('packing_order?field='.$field.'&search='.$search);
        
        $customer = Customer::all();
        $data = [
            'table' => $table,
            'customer' => $customer,
            
        ];
        return view($this->view."index",$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createOld()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $warehouse = Warehouse::all();
        $customer = Customer::all();
        $vendor = Vendor::all();
        $data = [
            'warehouse' => $warehouse,
            'customer' => $customer,
            'vendor' => $vendor
        ];
        return view($this->view."create",$data);
    }
    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $warehouse = Warehouse::all();
        $customer = Customer::all();
        $other_adress = CustomerOtherAddress::all();
        $vendor = Vendor::where('type', 1)->get();
        $data = [
            'warehouse' => $warehouse,
            'customer' => $customer,
            'other_adress' => $other_adress,
            'vendor' => $vendor
        ];
        return view($this->view."create_new",$data);
    }

    public function select_so($id)
    {
        $detail_po  = PackingOrder::where('id',$id)->first();
        if(empty($detail_po)){
            abort(404);
        }
        $customer_id = $detail_po->customer_id;
        $result = SalesOrderItem::whereHas('so',function($query2) use($customer_id,$detail_po){
                                    $query2->where('so_for',1);
                                    $query2->where('customer_id','!=',null);
                                    $query2->where('customer_id',$customer_id);
                                    $query2->where('origin_warehouse_id',$detail_po->warehouse_id);
                                    $query2->where('qty','!=',0);
                                    $query2->where('type_transaction',$detail_po->type_transaction);
                                    $query2->orderBy('id','DESC');

                                })
                                ->get();
        $data = [
            'result' => $result,
            'detail_po' => $detail_po
        ];
        return view($this->view."select_so",$data);
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
            DB::beginTransaction();
            try{
                if(empty($post["warehouse_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Warehouse wajib dipilih";
                    goto ResultData;
                }
                if(empty($post["customer_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Customer wajib dipilih";
                    goto ResultData;
                }
                if(empty($post["idr_rate"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "IDR Rate tidak boleh kosong";
                    goto ResultData;
                }

                if(empty($post["type_transaction"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Type transaction wajib dipilih";
                    goto ResultData;
                }
                
                $data = [
                    'code' => CodeRepo::generatePO(),
                    'customer_id' => trim(htmlentities($post["customer_id"])),
                    'warehouse_id' => trim(htmlentities($post["warehouse_id"])),
                    'customer_other_address_id' => (empty($post["customer_other_address_id"])) ? null : $post["customer_other_address_id"],
                    'other_address' => trim(htmlentities($post["other_address"])),
                    'type_transaction' => trim(htmlentities($post["type_transaction"])),
                    'idr_rate' => trim(htmlentities($post["idr_rate"])),
                    'note' => trim(htmlentities($post["note"])),
                    'created_by' => Auth::id(),
                    'status' => 1,
                    'ekspedisi_id' => (empty($post["ekspedisi_id"])) ? null : $post["ekspedisi_id"],
                ];

                $insert = PackingOrder::create($data);
                $insert_detail = PackingOrderDetail::create(['do_id' => $insert->id,'created_by' => Auth::id()]);

                DB::commit();
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Packing Order Berhasil Ditambahkan";
                goto ResultData;
                
            }catch(\Throwable $e){
                DB::rollback();
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
    public function store_so(Request $request)
    {
        
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            if(empty($post["do_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "DO ID tidak boleh kosong";
                goto ResultData;
            }
            if(count($post["repeater"]) == 0){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Not item sales order are ready";
                goto ResultData;
            }
            $data = [];
            DB::beginTransaction();
            try{
                foreach ($post["repeater"] as $key => $value) {
                    if(!empty($value["checkbox"]) && $value["checkbox"] == "on"){
                        $so_item_id = $value["so_item_id"];
                        $price = $value["price"];
                        $so_qty = $value["so_qty"];
                        $do_qty = $value["do_qty"];
                        $rej_qty = $value["rej_qty"];
                        $usd_disc = (floatval($value["usd_disc"]));
                        $percent_disc = (floatval($value["percent_disc"]));
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
                        $sisa = $so_qty - $qty_total;

                        if($so_qty < $qty_total){
                            $data_json["IsError"] = TRUE;
                            $data_json["Message"] = "Jumlah DO,REJ melebihi SO Qty";
                            goto ResultData;
                        }

                        $result = SalesOrderItem::where('id',$value["so_item_id"])->first();

                        if($do_qty == 0 && $rej_qty == 0){
                            $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                'qty' => 0
                            ]);
                        }
                        
                        if($do_qty > 0){
                            $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc/100))) * $do_qty);
                            $data[] = [
                                'do_id' => $post["do_id"],
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
                                'qty' => $sisa
                            ]);

                            
                        }

                        if(empty($do_qty) && $rej_qty > 0){
                            $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                'qty' => $sisa
                            ]);
                        }
                    }
                }
                foreach ($data as $key => $value) {
                    $insert = PackingOrderItem::create($data[$key]);
                }
                $this->reset_cost($post["do_id"]);
                DB::commit();
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Berhasil ditambakan ke Packing List";
                goto ResultData;

            }catch(\Throwable $e){
                DB::rollback();
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
    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $warehouse = Warehouse::all();
        $customer = Customer::all();
        $other_adress = CustomerOtherAddress::all();
        $vendor = Vendor::where('type', 1)->get();
        $dokumen = Dokumen::all();
        $data = [
            'warehouse' => $warehouse,
            'customer' => $customer,
            'other_adress' => $other_adress,
            'vendor' => $vendor,
            'result' => $result
        ];
        return view($this->view."edit_new",$data);
    }

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $data = [
            'result' => $result
        ];
        return view($this->view."detail",$data);
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
            DB::beginTransaction();
            try{
                if(empty($post["id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "ID PO tidak boleh kosong";
                    goto ResultData;
                }

                if(empty($post["idr_rate"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "IDR Rate tidak boleh kosong";
                    goto ResultData;
                }
                $idr_rate = str_replace('.', '', $post["idr_rate"]);
                $get = PackingOrder::where('id',$post["id"])->first();
                
                $this->reset_cost_if_change_idr_rate($post["id"],$idr_rate);

                $data = [
                    'customer_other_address_id' => (empty($post["customer_other_address_id"])) ? null : $post["customer_other_address_id"],
                    'other_address' => trim(htmlentities($post["other_address"])),
                    'note' => trim(htmlentities($post["note"])),
                    'idr_rate' => $idr_rate,
                    'updated_by' => Auth::id(),
                    'ekspedisi_id' => (empty($post["ekspedisi_id"])) ? null : $post["ekspedisi_id"],
                ];

                $update = PackingOrder::where('id',$post["id"])->update($data);

                // dd($data);
                DB::commit();
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Packing Order Berhasil diubah";
                goto ResultData;
              
            }catch(\Throwable $e){
                DB::rollback();

                // dd($data);
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
    public function update_cost(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                if(empty($post["id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "ID Cost tidak boleh kosong";
                    goto ResultData;
                }

                $check_cost = PackingOrderDetail::where('id',$post["id"])->first();
                $check_po_item = PackingOrderItem::where('do_id',$check_cost->do_id)->get();
                $detail_po = PackingOrder::where('id',$check_cost->do_id)->first();
                $detail_po_item = PackingOrderItem::where('do_id',$check_cost->do_id)->get();
                

                $idr_total = 0;
                foreach ($detail_po_item as $key => $row) {
                    $idr_total += ceil((($row->price * $detail_po->idr_rate) * $row->qty) - ($row->total_disc * $detail_po->idr_rate)); 
                }

                if(count($check_po_item) <= 0){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Item Packing Order tidak ditemukan";
                    goto ResultData;
                }

                $discount_1 = empty($post["discount_1"]) ? 0 : $post["discount_1"] / 100;
                $discount_2 = empty($post["discount_2"]) ? 0 : $post["discount_2"] / 100;
                $discount_idr = empty($post["discount_idr"]) ? 0 : $post["discount_idr"];
                $ppn = empty($post["ppn"]) ? 0 : $post["ppn"];
                $voucher_idr = empty($post["voucher_idr"]) ? 0 : $post["voucher_idr"];
                $cashback_idr = empty($post["cashback_idr"]) ? 0 : $post["cashback_idr"];
                $delivery_cost_idr = empty($post["delivery_cost_idr"]) ? 0 : $post["delivery_cost_idr"];
                $other_cost_idr = empty($post["other_cost_idr"]) ? 0 : $post["other_cost_idr"];

                $discount_idr = str_replace('.', '', $discount_idr);
                $ppn = str_replace('.', '', $ppn);
                $voucher_idr = str_replace('.', '', $voucher_idr);
                $cashback_idr = str_replace('.', '', $cashback_idr);
                $delivery_cost_idr = str_replace('.', '', $delivery_cost_idr);
                $other_cost_idr = str_replace('.', '', $other_cost_idr);

                $total_discount_idr = ceil(( $idr_total * $discount_1 ) + (($idr_total - ($idr_total * $discount_1)) * $discount_2) + $discount_idr);

                if($ppn > 0){
                    $ppn = ceil(($idr_total - $total_discount_idr ) * (10/100));
                }
                else{
                    $ppn = 0;
                }
                
                $purchase_total_idr = ceil($idr_total - $total_discount_idr - $voucher_idr - $cashback_idr + $ppn);
                $grand_total_idr = ceil($purchase_total_idr + $delivery_cost_idr + $other_cost_idr);

                if($total_discount_idr > $grand_total_idr){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Total Discount melebihi IDR total item pembelian";
                    goto ResultData;
                }

                

                $data = [
                    'discount_1' => trim(htmlentities($post["discount_1"])),
                    'discount_2' => trim(htmlentities($post["discount_2"])),
                    'discount_idr' => trim(htmlentities($discount_idr)),
                    'total_discount_idr' => trim(htmlentities($total_discount_idr)),
                    'ppn' => trim(htmlentities($ppn)),
                    'voucher_idr' => trim(htmlentities($voucher_idr)),
                    'cashback_idr' => trim(htmlentities($cashback_idr)),
                    'purchase_total_idr' => trim(htmlentities($purchase_total_idr)),
                    'delivery_cost_idr' => trim(htmlentities($delivery_cost_idr)),
                    'delivery_cost_note' => trim(htmlentities($post["delivery_cost_note"])),
                    'other_cost_idr' => trim(htmlentities($other_cost_idr)),
                    'other_cost_note' => trim(htmlentities($post["other_cost_note"])),
                    'grand_total_idr' => trim(htmlentities($grand_total_idr)),
                    'updated_by' => Auth::id()
                ];

                $update = PackingOrderDetail::where('id',$post["id"])->update($data);

                if($update){
                    $data_json["IsError"] = FALSE;
                    $data_json["Message"] = "Packing Cost berhasil diubah";
                    goto ResultData;
                }
                else{
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Packing Cost gagal diubah";
                    goto ResultData;
                }

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

    public function update_new(Request $request) {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            DB::beginTransaction();
            try{
                if(empty($post["id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "ID PO tidak boleh kosong";
                    goto ResultData;
                }

                if(empty($post["idr_rate"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "IDR Rate tidak boleh kosong";
                    goto ResultData;
                }
                $idr_rate = str_replace('.', '', $post["idr_rate"]);
                $get = PackingOrder::where('id',$post["id"])->first();
                
                $this->reset_cost_if_change_idr_rate($post["id"],$idr_rate);

                $data = [
                    'customer_other_address_id' => (empty($post["customer_other_address_id"])) ? null : $post["customer_other_address_id"],
                    //'note' => trim(htmlentities($post["note"])),
                    'other_address' => trim(htmlentities($post["other_address"])),
                    'idr_rate' => $idr_rate,
                    'status' => 2,
                    'updated_by' => Auth::id(),
                    'ekspedisi_id' => (empty($post["ekspedisi_id"])) ? null : $post["ekspedisi_id"],
                ];

                if(empty($get->do_code)){
                    $data['do_code'] = CodeRepo::generateDO();
                }

                $update = PackingOrder::where('id',$post["id"])->update($data);
                $get = PackingOrder::where('id',$post["id"])->first();
                
                if(empty($post["cost_id"])){
                    DB::rollback();
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "ID Cost tidak boleh kosong";
                    goto ResultData;
                }

                $check_cost = PackingOrderDetail::where('id',$post["cost_id"])->first();
                $check_po_item = PackingOrderItem::where('do_id',$check_cost->do_id)->get();

                if(count($check_po_item) <= 0){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Item Packing Order tidak ditemukan";
                    goto ResultData;
                }

                foreach ($post["do_details"] as $key => $value) {
                    if (empty($value["id"]) || empty($value["usd_disc"])) {
                        continue;
                    }

                    $packingOrderItem = PackingOrderItem::where('id',$value["id"])->first();
                    if (empty($packingOrderItem) || !isset($packingOrderItem)) continue;

                    if ($packingOrderItem->percent_disc > 0) {
                        $total_disc = floatval(($value["usd_disc"] + (($packingOrderItem->price - $value["usd_disc"]) * ($packingOrderItem->percent_disc/100))) * $packingOrderItem->qty);
                    } else {
                        $total_disc = floatval($value["usd_disc"] * $packingOrderItem->qty);
                    }
                    $data = [
                        'usd_disc' => $value["usd_disc"],
                        'total_disc' => $total_disc,
                        'total' => ($packingOrderItem->price * $packingOrderItem->qty) - $total_disc,
                    ];
                    $update = PackingOrderItem::where('id',$value["id"])->update($data);
                }

                $detail_po = PackingOrder::where('id',$check_cost->do_id)->first();
                $detail_po_item = PackingOrderItem::where('do_id',$check_cost->do_id)->get();

                $idr_total = 0;
                foreach ($detail_po_item as $key => $row) {
                    $idr_total += ceil((($row->price * $detail_po->idr_rate) * $row->qty) - ($row->total_disc * $detail_po->idr_rate)); 
                }

                $discount_1 = empty($post["discount_1"]) ? 0 : $post["discount_1"] / 100;
                $discount_2 = empty($post["discount_2"]) ? 0 : $post["discount_2"] / 100;
                $discount_idr = empty($post["discount_idr"]) ? 0 : $post["discount_idr"];
                $ppn = empty($post["ppn"]) ? 0 : $post["ppn"];
                $voucher_idr = empty($post["voucher_idr"]) ? 0 : $post["voucher_idr"];
                $cashback_idr = empty($post["cashback_idr"]) ? 0 : $post["cashback_idr"];
                $delivery_cost_idr = empty($post["delivery_cost_idr"]) ? 0 : $post["delivery_cost_idr"];
                $other_cost_idr = empty($post["other_cost_idr"]) ? 0 : $post["other_cost_idr"];

                $discount_idr = str_replace('.', '', $discount_idr);
                $ppn = str_replace('.', '', $ppn);
                $voucher_idr = str_replace('.', '', $voucher_idr);
                $cashback_idr = str_replace('.', '', $cashback_idr);
                $delivery_cost_idr = str_replace('.', '', $delivery_cost_idr);
                $other_cost_idr = str_replace('.', '', $other_cost_idr);

                $total_discount_idr = ceil(( $idr_total * $discount_1 ) + (($idr_total - ($idr_total * $discount_1)) * $discount_2) + $discount_idr);

                if($ppn > 0){
                    $ppn = ceil(($idr_total - $total_discount_idr ) * (10/100));
                }
                else{
                    $ppn = 0;
                }
                
                $purchase_total_idr = ceil($idr_total - $total_discount_idr - $voucher_idr - $cashback_idr + $ppn);
                $grand_total_idr = ceil($purchase_total_idr + $delivery_cost_idr);
                
                if($total_discount_idr > $grand_total_idr){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Total Discount melebihi IDR total item pembelian";
                    goto ResultData;
                }

                $data = [
                    'discount_1' => trim(htmlentities($post["discount_1"])),
                    'discount_2' => trim(htmlentities($post["discount_2"])),
                    'discount_idr' => trim(htmlentities($discount_idr)),
                    'total_discount_idr' => trim(htmlentities($total_discount_idr)),
                    'ppn' => trim(htmlentities($ppn)),
                    'voucher_idr' => trim(htmlentities($voucher_idr)),
                    'cashback_idr' => trim(htmlentities($cashback_idr)),
                    'purchase_total_idr' => trim(htmlentities($purchase_total_idr)),
                    'delivery_cost_idr' => trim(htmlentities($delivery_cost_idr)),
                    'delivery_cost_note' => trim(htmlentities($post["delivery_cost_note"])),
                    'other_cost_idr' => trim(htmlentities($other_cost_idr)),
                    //'other_cost_note' => trim(htmlentities($post["other_cost_note"])),
                    'grand_total_idr' => trim(htmlentities($grand_total_idr)),
                    'updated_by' => Auth::id()
                ];

                $update = PackingOrderDetail::where('do_id',$post["id"])->update($data);
                
                //create invoicing disini
                if (empty($detail_po->invoicing)) {
                    $data = [
                        'code' => CodeRepo::generateInvoicing($get->do_code),
                        'do_id' =>trim(htmlentities($post["id"])),
                        'grand_total_idr' => $grand_total_idr,
                        'created_by' => Auth::id()
                    ];
    
                    $insert = Invoicing::create($data);
                }

                DB::commit();

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Packing Order Berhasil diubah";
                goto ResultData;
            } catch(\Throwable $e){
                // dd($e);
                DB::rollback();
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
            $result = PackingOrder::where('id',$post["id"])->first();
            $result_item = PackingOrderItem::where('do_id',$post["id"])->get();

            $update = PackingOrder::where('id',$post["id"])->update(['deleted_by' => Auth::id()]);
            $destroy = PackingOrder::where('id',$post["id"])->delete();

            if($result->status != 1){
                return redirect()->route('superuser.penjualan.packing_order.index')->with('error','Hanya packing order yang berstatus draft yang bisa di hapus');
            }

            foreach ($result_item as $key => $value) {
                $update_so = SalesOrderItem::where('id',$value->so_item_id)->increment('qty',$value->qty);
            }

            $this->reset_cost($post["id"]);

            DB::commit();
            return redirect()->back()->with('success','Packing Order berhasil dihapus');
            
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

            $result = PackingOrderItem::where('id',$post["id"])->first();

            $update = PackingOrderItem::where('id',$post["id"])->update(['deleted_by' => Auth::id()]);
            $destroy = PackingOrderItem::where('id',$post["id"])->delete();

            $update_so = SalesOrderItem::where('id',$result->so_item_id)->increment('qty',$result->qty);

            $this->reset_cost($result->do_id);

            DB::commit();
            return redirect()->back()->with('success','Item berhasil dihapus');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
    // public function ready(Request $request)
    // {
    //     // Access
    //     if(Auth::user()->is_superuser == 0){
    //         if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
    //             return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
    //         }
    //     }

    //     try{
    //         $request->validate([
    //             'id' => 'required'
    //         ]);
    //         $post = $request->all();

    //         //Cek Pembayaran

    //         $proforma = SoProforma::where('do_id', $post["id"])->first();

    //         if($proforma->type_transaction == 1){
    //             if($proforma->status == 3){
    //                 $update = PackingOrder::where('id', $post["id"])->update(['status' => 3]);
                    
    //                 return redirect()->back()->with('success','SO packed berhasil di proses'); 
    //             }else{
    //                 return redirect()->back()->with('error','SO packed gagal di proses! Cek pembayaran');
    //             }
    //         }elseif($proforma->type_transaction == 2 && $proforma->type_transaction == 3){
    //             $update = PackingOrder::where('id', $post["id"])->update(['status' => 3]);

    //             return redirect()->back()->with('success','SO packed berhasil di proses');
    //         }
            
    //     }catch(\Throwable $e){
    //         return redirect()->back()->with('error',$e->getMessage());
    //     }
    // }

    public function ready(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            
            $getDo = PackingOrder::where('id', $post["id"])->first();

            if(empty($getDo->do_code)){
                PackingOrder::where('id',$getDo->id)->update([
                    'do_code' => CodeRepo::generateDO()
                ]);
            }

            $update = PackingOrder::where('id',$post["id"])->update(['status' => 3]);

            if($update){
                return redirect()->back()->with('success','SO berhasil berhasil diproses ke DO');    
            }
            else{
                return redirect()->back()->with('error','SO gagal diproses ke DO');
            }
            
        }catch(\Throwable $e){
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function packed(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $result = PackingOrder::where('id',$post["id"])->first();

            if(empty($result->do_code)){
                PackingOrder::where('id',$result->id)->update([
                    'do_code' => CodeRepo::generateDO()
                ]);
            }

            $update = PackingOrder::where('id',$post["id"])->update(['status' => 4]);

            if($update){
                return redirect()->back()->with('success','Packing Order berhasil diubah ke Packed');    
            }
            else{
                return redirect()->back()->with('error','Packing Order gagal diubah ke Packed');
            }
            
        }catch(\Throwable $e){
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
    public function order(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();

            $update = PackingOrder::where('id',$post["id"])->update(['status' => 3]);

            if($update){
                return redirect()->back()->with('success','Packing Order berhasil diubah ke ordered');    
            }
            else{
                return redirect()->back()->with('error','Packing Order gagal diubah ke ordered');
            }
            
        }catch(\Throwable $e){
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
    public function prepare(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $result = PackingOrder::where('id',$post["id"])->first();

            if($result->status != 1){
                return redirect()->route('superuser.penjualan.packing_order.index')->with('error','Hanya packing order yang berstatus draft yang bisa diubah ke prepare');
            }
            if(count($result->do_detail) == 0){
                return redirect()->route('superuser.penjualan.packing_order.index')->with('error','Tidak ada item sama sekali');
            }
            if($result->do_cost->grand_total_idr == 0){
                return redirect()->route('superuser.penjualan.packing_order.index')->with('error','Harga didalam packing list belum di set');
            }
            $update = PackingOrder::where('id',$post["id"])->update(['status' => 2]);

            if($update){
                return redirect()->back()->with('success','Packing Order berhasil diubah ke prepare');    
            }
            else{
                return redirect()->back()->with('error','Packing Order gagal diubah ke prepare');
            }
            
            
            
        }catch(\Throwable $e){
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function revisi(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();

            $result = PackingOrder::where('id', $post["id"])->first();

            //Kembalikan SO ke step lanjutan

            if($result->status == 2 OR $result->status == 3 OR $result->status == 4){
                $update_so = SalesOrder::where('id', $result->so_id)->update(['status' => 2, 'count_rev' => 1]);

                $update_po = PackingOrder::where('id', $result->id)->update(['status' => 7]);

                //Delete packing order item
                $del_po_item = PackingOrderItem::where('do_id', $result->id)->delete();

                //Delete proforma
                $get_pro = Soproforma::where('do_id', $request->id)->first();

                $del_proforma_item = SoProformaDetail::where('so_proforma_id', $get_pro->id)->delete();

                return redirect()->back()->with('success','SO Packed berhasil di kembalikan ke SO!');  
            }elseif($result->status == 5 OR $result->status == 6){
                return redirect()->back()->with('error','Gagal di Kembalikan status saat ini DO dalam proses KIRIM!');
            }
                        
        }catch(\Throwable $e){
            return redirect()->back()->with('error',$e->getMessage());
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
    public function ajax_customer_other_address(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                $get = CustomerOtherAddress::where('customer_id',$post["customer_id"])
                                            ->get();

                $data_json["IsError"] = FALSE;
                $data_json["Data"] = $get;
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
    public function ajax_customer_other_address_detail(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                $result = CustomerOtherAddress::where('id',$post["id"])
                                            ->first();

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
    public function print_proforma($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();

        // CR
        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\invoice\\invoice_new.rpt"; 
        $my_pdf = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\invoice\\export\\invoice.pdf";

        //- Variables - Server Information 
        $my_server = "DEV-PPIDIST"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";


        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        // to refresh data before

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        // $creport->RecordSelectionFormula = "{F_DOCLIGNE.DO_Piece}='$id'";

        // $zz = $creport->ParameterFields(1)->SetCurrentValue("2022-08-05");    

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

        $file = "C:\\xampp\\htdocs\\ppi-manage\\report\\export\\sales_report.pdf"; 

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 

        readfile ($file);
        exit();
    }

    private function reset_cost_if_change_idr_rate($do_id,$idr_rate){
        $do = PackingOrder::where('id',$do_id)->first();
        $result = PackingOrderDetail::where('do_id',$do_id)->first();

        $check_po = PackingOrderItem::where('do_id',$result->do_id)->get();
        $idr_total = 0;

        foreach ($check_po as $key => $row) {
            $idr_total += ceil(((($row->price * $do->idr_rate) * $row->qty ) - ($row->total_disc * $do->idr_rate))); 
        }

        $discount_1 = floatval($result->discount_1) / 100;
        $discount_2 = floatval($result->discount_2) / 100;
        $discount_idr = ($result->discount_idr);

        $total_discount_idr = ceil(( $idr_total * $discount_1 ) + (($idr_total - ($idr_total * $discount_1)) * $discount_2) + $discount_idr);

        if($result->ppn > 0){
            $ppn = ceil(($idr_total - $total_discount_idr ) * (10/100));
        }
        else{
            $ppn = 0;
        }

        $voucher_idr = $result->voucher_idr;
        $cashback_idr = $result->cashback_idr;
        $purchase_total_idr = ceil($idr_total - $total_discount_idr - $voucher_idr - $cashback_idr + $ppn);
        $delivery_cost_idr = $result->delivery_cost_idr;
        $other_cost_idr = $result->other_cost_idr;
        $grand_total_idr = ceil($purchase_total_idr + $delivery_cost_idr + $other_cost_idr);

        $data = [
            'ppn' => $ppn,
            'total_discount_idr' => trim(htmlentities($total_discount_idr)),
            'purchase_total_idr' => trim(htmlentities($purchase_total_idr)),
            'grand_total_idr' => trim(htmlentities($grand_total_idr)),
            'updated_by' => Auth::id()
        ];

        $update = PackingOrderDetail::where('do_id',$do_id)->update($data);
        return true;        
    }
    public function reset_cost($id){
      $data = [
          'discount_1' => 0,
          'discount_2' => 0,
          'discount_idr' => 0,
          'total_discount_idr' => 0,
          'ppn' => 0,
          'voucher_idr' => 0,
          'cashback_idr' => 0,
          'purchase_total_idr' => 0,
          'delivery_cost_idr' => 0,
          'other_cost_idr' => 0,
          'grand_total_idr' => 0,
          'updated_by' => Auth::id()
      ];

      $update = PackingOrderDetail::where('do_id',$id)->update($data);
      return true;
    }
}
