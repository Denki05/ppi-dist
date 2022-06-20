<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Product;
use App\Entities\Master\ProductMinStock;
use App\Entities\Penjualan\DeliveryOrderMutation;
use App\Entities\Penjualan\DeliveryOrderMutationItem;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Gudang\StockMove;
use App\Entities\Setting\UserMenu;
use App\Entities\Master\Company;
use App\Repositories\CodeRepo;
use Auth;
use DB;
use PDF;

class DeliveryOrderMutationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.penjualan.delivery_order_mutation.";
        $this->route = "superuser.penjualan.delivery_order_mutation";
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
        return view("superuser.coming-soon");
        
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $search = $request->input('search');
        $origin_warehouse_id = $request->input('origin_warehouse_id');
        $destination_warehouse_id = $request->input('destination_warehouse_id');
        $table = DeliveryOrderMutation::where(function($query2) use($search){
                                            if(!empty($search)){
                                                $query2->where('code','like','%'.$search.'%');
                                            }
                                        })
                                        ->whereHas('origin_warehouse',function($query2) use($origin_warehouse_id){
                                            if(!empty($origin_warehouse_id)){
                                                $query2->where('id',$origin_warehouse_id);
                                            }
                                        })
                                        ->whereHas('destination_warehouse',function($query2) use($destination_warehouse_id){
                                            if(!empty($destination_warehouse_id)){
                                                $query2->where('id',$destination_warehouse_id);
                                            }
                                        })
                                        ->orderBy('id','DESC')
                                        ->paginate(10);

        $table->withPath('delivery_order_mutation?search='.$search."&origin_warehouse_id=".$origin_warehouse_id."&destination_warehouse_id=".$destination_warehouse_id);
        $warehouse = Warehouse::all();
        $data = [
            'table' => $table,
            'warehouse' => $warehouse
        ];
        return view($this->view."index",$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $warehouse = Warehouse::all();
        $data =[
            'warehouse' => $warehouse
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
            if(empty($post["origin_warehouse_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Origin warehouse wajib dipilih";
                goto ResultData;
            }
            if(empty($post["destination_warehouse_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Destination warehouse wajib dipilih";
                goto ResultData;
            }

            if(empty($post["address"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Alamat tidak boleh kosong";
                goto ResultData;
            }

            $data = [
                'code' => CodeRepo::generateDOM(),
                'origin_warehouse_id' => trim(htmlentities($post["origin_warehouse_id"])),
                'destination_warehouse_id' => trim(htmlentities($post["destination_warehouse_id"])),
                'address' => trim(htmlentities($post["address"])),
                'created_by' => Auth::id(),
                'status' => 1
            ];

            $insert = DeliveryOrderMutation::create($data);

            if($insert){
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Delivery Order Mutation Berhasil Ditambahkan";
                goto ResultData;
            }
            else{
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Delivery Order Mutation Gagal Ditambahkan";
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
            if(empty($post["do_mutation_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "DO Mutation ID tidak boleh kosong";
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
                        $so_qty = (int)$value["so_qty"];
                        $do_qty = (int)$value["do_qty"];
                        $rej_qty = (int)$value["rej_qty"];
                     
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
                            $data[] = [
                                'do_mutation_id' => $post["do_mutation_id"],
                                'product_id' => $value["product_id"],
                                'so_item_id' => $value["so_item_id"],
                                'packaging' => $result->packaging,
                                'qty' => $do_qty,
                                'price' => $price,
                                'total' => floatval($do_qty * $price) ,
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
                    $insert = DeliveryOrderMutationItem::create($data[$key]);
                }
                DB::commit();
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Berhasil ditambakan ke Delivery Order Mutation";
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

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = DeliveryOrderMutation::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $data = [
            'result' => $result
        ];
        return view($this->view."detail",$data);
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

        $result = DeliveryOrderMutation::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $product = Product::all();
        $data =[
            'result' => $result
        ];
        return view($this->view."edit",$data);
    }

    public function select_so($id)
    {
        $detail_po  = DeliveryOrderMutation::where('id',$id)->first();
        if(empty($detail_po)){
            abort(404);
        }
        $result = SalesOrderItem::whereHas('so',function($query2) use($detail_po){
                                    $query2->where('so_for',2);
                                    $query2->where('destination_warehouse_id','!=',null);
                                    $query2->where('destination_warehouse_id',$detail_po->destination_warehouse_id);
                                    $query2->where('origin_warehouse_id',$detail_po->origin_warehouse_id);
                                    $query2->where('qty','!=',0);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

            $result = DeliveryOrderMutation::where('id',$post["id"])->first();
            $result_item = DeliveryOrderMutationItem::where('do_mutation_id',$post["id"])->get();

            if($result->status > 1){
                return redirect()->back()->with('error','DO Mutation hanya bisa dihapus jika statusnya draft');
            }

            $update = DeliveryOrderMutation::where('id',$post["id"])->update(['deleted_by' => Auth::id()]);
            $destroy = DeliveryOrderMutation::where('id',$post["id"])->delete();
            $destroy_item = DeliveryOrderMutationItem::where('do_mutation_id',$post["id"])->delete();

            foreach ($result_item as $key => $value) {
                $update_so = SalesOrderItem::where('id',$value->so_item_id)->increment('qty',$value->qty);
            }

            DB::commit();
            return redirect()->back()->with('success','Delivery Order Mutation berhasil dihapus');
            
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

            $result = DeliveryOrderMutationItem::where('id',$post["id"])->first();

            $update = DeliveryOrderMutationItem::where('id',$post["id"])->update(['deleted_by' => Auth::id()]);
            $destroy = DeliveryOrderMutationItem::where('id',$post["id"])->delete();

            $update_so = SalesOrderItem::where('id',$result->so_item_id)->increment('qty',$result->qty);

            DB::commit();
            return redirect()->back()->with('success','Item berhasil dihapus');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function sent(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        try{
            DB::beginTransaction();
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();

            $result = DeliveryOrderMutation::where('id',$post["id"])->first();

            if($result->do_mutation_item->count() <= 0){
               return redirect()->back()->with('error','Item tidak ada'); 
            }

            $update = DeliveryOrderMutation::where('id',$post["id"])->update([
                    'status' => 2,
                    'updated_by' => Auth::id()
            ]);

            $detail_item = DeliveryOrderMutationItem::where('do_mutation_id',$result->id)->get();

            foreach ($detail_item as $key => $value) {
                $stock_product = ProductMinStock::where('product_id',$value->product_id)
                                                ->where('warehouse_id',$result->origin_warehouse_id)
                                                ->sum('quantity');

                $stock_product_destination = ProductMinStock::where('product_id',$value->product_id)
                                                ->where('warehouse_id',$result->destination_warehouse_id)
                                                ->sum('quantity');


                $move = StockMove::where('product_id',$value->product_id)
                                    ->where('warehouse_id',$result->origin_warehouse_id)->get();

                $move_destination = StockMove::where('product_id',$value->product_id)
                                    ->where('warehouse_id',$result->destination_warehouse_id)->get();

                $sisa = (int)$stock_product + $move->sum('stock_in') - $move->sum('stock_out') - $value->qty;
                $increment = (int)$stock_product_destination + $move_destination->sum('stock_in') - $move_destination->sum('stock_out') + $value->qty;


                $insert_stock_move = StockMove::create([
                    'code_transaction' => $result->code,
                    'warehouse_id' => $result->origin_warehouse_id,
                    'product_id' => $value->product_id,
                    'stock_out' => $value->qty,
                    'stock_balance' => $sisa,
                    'created_by' => Auth::id()
                ]);

                $insert_stock_move_destination = StockMove::create([
                    'code_transaction' => $result->code,
                    'warehouse_id' => $result->destination_warehouse_id,
                    'product_id' => $value->product_id,
                    'stock_in' => $value->qty,
                    'stock_balance' => $increment,
                    'created_by' => Auth::id()
                ]);
            }
            DB::commit();
            return redirect()->route('superuser.penjualan.delivery_order_mutation.index')->with('success','Delivery Order Mutation berhasil di ubah ke sent');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->route('superuser.penjualan.delivery_order_mutation.index')->with('error',$e->getMessage());
        }
    }

    public function print($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = DeliveryOrderMutation::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $company = Company::first();
        $data = [
            'result' => $result,
            'company' => $company
        ];

        $pdf = PDF::loadview($this->view."print",$data)->setPaper('a4','potrait');
        return $pdf->stream($result->code ?? '');
    }
}
