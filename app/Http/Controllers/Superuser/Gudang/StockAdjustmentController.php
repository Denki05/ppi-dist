<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Product;
use App\Entities\Gudang\StockAdjustment;
use App\Entities\Gudang\StockSalesOrder;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use Auth;
use DB;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.gudang.stock_adjustment.";
        $this->route = "superuser.gudang.stock_adjustment";
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
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $search = $request->input('search');
        $warehouse_id = $request->input('warehouse_id');
        $table = StockAdjustment::where(function($query2) use($search){
                                    if(!empty($search)){
                                        $query2->where('prev','like','%'.$search.'%');
                                        $query2->orWhere('min','like','%'.$search.'%');
                                        $query2->orWhere('plus','like','%'.$search.'%');
                                        $query2->orWhere('update','like','%'.$search.'%');
                                    }
                                })
                                ->orWhereHas('product',function($query2) use($search){
                                    if(!empty($search)){
                                        $query2->where('code','like','%'.$search.'%');
                                        $query2->where('name','like','%'.$search.'%');
                                    }
                                })
                                ->where(function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('warehouse_id',$warehouse_id);
                                    }
                                })
                                ->orderBy('id','DESC')
                                ->paginate(10);
        $table->withPath('stock_adjustment?search='.$search."&warehouse_id=".$warehouse_id);
        $warehouse = Warehouse::get();
        $data = [
            'warehouse' => $warehouse,
            'table' => $table
        ];
        return view($this->view."index",$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        
        if(empty($request->input('warehouse_id'))){
            abort(404);
        }
        $warehouse = Warehouse::where('id',$request->input('warehouse_id'))->first();
        if(empty($warehouse)){
            return redirect()->route('superuser.gudang.stock_adjustment.index')->with('error','Gudang tidak ditemukan');
        }
        $product = Product::where('default_warehouse_id',$warehouse->id)->get();
        $data = [
            'warehouse' => $warehouse,
            'product' => $product
        ];

        // DD($data);

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
            DB::beginTransaction();
            try{
                if(empty($post["warehouse_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Warehouse ID tidak boleh kosong";
                    goto ResultData;
                }
                if(empty($post["product_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Product wajib dipilih";
                    goto ResultData;
                }
                $product_warehouse = StockSalesOrder::where('warehouse_id',$post["warehouse_id"])
                                                    ->where('product_id',$post["product_id"])
                                                    ->first();

                $plus_stock = (empty($post["plus"])) ? 0 : $post["plus"];
                $min_stock = (empty($post["min"])) ? 0 : $post["min"];
                $prev_stock = $product_warehouse->quantity ?? 0;
                $update_stock = (int)$prev_stock + $plus_stock - $min_stock;

                $data = [
                    'code' => CodeRepo::generateStockAdjustment(),
                    'warehouse_id' => trim(htmlentities($post["warehouse_id"])),
                    'product_id' => trim(htmlentities($post["product_id"])),
                    'plus' => trim(htmlentities($plus_stock)),
                    'min' => trim(htmlentities($min_stock)),
                    'prev' => trim(htmlentities($prev_stock)),
                    'update' => trim(htmlentities($update_stock)),
                    'note' => trim(htmlentities($post["note"])),
                    'created_by' => Auth::id()
                ];

                $update_stock = StockSalesOrder::where('warehouse_id',$post["warehouse_id"])
                                                ->where('product_id',$post["product_id"])
                                                ->update([
                                                    'quantity' => $update_stock
                                                ]);

                $insert = StockAdjustment::create($data);
                DB::commit();

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Penyesuaian stock berhasil";
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
        //
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
    public function destroy($id)
    {
        //
    }
    public function check_product_warehouse(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                if(empty($post["warehouse_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Warehouse ID tidak boleh kosong";
                    goto ResultData;
                }
                if(empty($post["product_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Product ID tidak boleh kosong";
                    goto ResultData;
                }

                $get = StockSalesOrder::where('warehouse_id',$post["warehouse_id"])
                                        ->where('product_id',$post["product_id"])
                                        ->first();

                $data_json["IsError"] = FALSE;
                $data_json["Data"] = $get;

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
}
