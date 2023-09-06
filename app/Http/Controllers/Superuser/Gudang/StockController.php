<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Product;
use App\Entities\Master\Warehouse;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\DeliveryOrderMutationItem;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\CanvasingItem;
use App\Entities\Gudang\StockMove;
use App\Entities\Setting\UserMenu;
use DB;
use Auth;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){

        $this->view = "superuser.gudang.stock.";
        $this->route = "superuser.gudang.stock";
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

        $warehouse_id = $request->input('warehouse_id');
        $search = $request->input('search');

        $warehouse = Warehouse::get();
        $table = ProductMinStock::select(DB::raw('SUM(master_product_min_stocks.quantity) as stock_in'),'master_product_min_stocks.product_packaging_id','master_product_min_stocks.warehouse_id')
                                ->groupBy('warehouse_id')
                                ->groupBy('product_packaging_id')
                                ->where(function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('warehouse_id',$warehouse_id);
                                    }
                                })
                                ->whereHas('product_pack',function($query2) use($search){
                                    if(!empty($search)){
                                        $query2->where('name','like','%'.$search.'%');
                                        $query2->orWhere('code','like','%'.$search.'%');
                                    }
                                })
                                ->paginate(10);
  

        $table->withPath('stock?search='.$search."&warehouse_id=".$warehouse_id);

        foreach ($table as $key => $value) {
            $value->stock_in = floatval($value->stock_in);
        }
        foreach ($table as $key => $value) {
            $stock = 0;
            $stock_out = 0;
            $effective = 0;
            $so = SalesOrderItem::where('product_id',$value->product_packaging_id)
                                ->whereHas('so',function($query2) use($value){
                                    $query2->where('origin_warehouse_id',$value->warehouse_id);
                                })->sum('qty');

            $do = PackingOrderItem::where('product_id', $value->product_packaging_id)
                                ->whereHas('do',function($query2) use($value){
                                    $query2->where('status','>',1);
                                    $query2->where('warehouse_id', '=', $value->warehouse_id);
                                })->sum('qty');

            $do_mutation = DeliveryOrderMutationItem::where('product_id', $value->product_packaging_id)
                                ->whereHas('do_mutation',function($query2) use($value){
                                    $query2->where('status','>',1);
                                    $query2->where('origin_warehouse_id',$value->warehouse_id);
                                })->sum('qty');
            $canvasing = CanvasingItem::where('product_id', $value->product_packaging_id)
                                ->whereHas('canvasing',function($query2) use($value){
                                    $query2->where('status','>',1);
                                    $query2->where('warehouse_id',$value->warehouse_id);
                                })->sum('qty');
            $move = StockMove::where('product_id', $value->product_packaging_id)
                                ->where('warehouse_id',$value->warehouse_id)->get();


            $move_in = $move->sum('stock_in');
            $move_out = $move->sum('stock_out');

            $stock_out = $move_out;
            $stock  = floatval($value->stock_in + $move_in - $move_out);
            $effective = $stock;

            $value->stock = $stock;
            $value->stock_in = $move_in;
            $value->stock_out = $stock_out;
            $value->so = floatval($so);
            $value->effective = $effective;
        }
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
    public function create()
    {
        //
    }

    public function detail(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = ProductMinStock::select(DB::raw('SUM(master_product_min_stocks.quantity) as stock_in'),'master_product_min_stocks.product_packaging_id','master_product_min_stocks.warehouse_id')
                                   ->where('product_packaging_id', $request->input('product_packaging_id'))
                                   ->where('warehouse_id',$request->input('warehouse_id'))
                                   ->groupBy('warehouse_id')
                                   ->groupBy('product_packaging_id')
                                   ->first();
        if(empty($result)){
            abort(404);
        }

        $stock = 0;
        $stock_out = 0;
        $effective = 0;
        $so = SalesOrderItem::where('product_id',$result->product_id)
                            ->whereHas('so',function($query2) use($result){
                                $query2->where('origin_warehouse_id',$result->warehouse_id);
                            })->sum('qty');
        $do = PackingOrderItem::where('product_id',$result->product_id)
                            ->whereHas('do',function($query2) use($result){
                                $query2->where('status','>',1);
                                $query2->where('warehouse_id',$result->warehouse_id);
                            })->sum('qty');
        $do_mutation = DeliveryOrderMutationItem::where('product_id',$result->product_id)
                            ->whereHas('do_mutation',function($query2) use($result){
                                $query2->where('status','>',1);
                                $query2->where('origin_warehouse_id',$result->warehouse_id);
                            })->sum('qty');
        $canvasing = CanvasingItem::where('product_id',$result->product_id)
                            ->whereHas('canvasing',function($query2) use($result){
                                $query2->where('status','>',1);
                                $query2->where('warehouse_id',$result->warehouse_id);
                            })->sum('qty');
       
        $move = StockMove::where('product_id',$result->product_id)
                            ->where('warehouse_id',$result->warehouse_id)->get();


        $move_in = $move->sum('stock_in');
        $move_out = $move->sum('stock_out');


        $stock_out = $move_out ;
        $stock = floatval($result->stock_in + $move_in - $move_out);
        $effective = $stock - $so;

        $result->stock = $stock;
        $result->stock_in = $move_in;
        $result->stock_out = $stock_out;
        $result->so = floatval($so);
        $result->effective = $effective;


        // End result;
        $result_do = PackingOrderItem::where('product_id',$result->product_id)
                                      ->whereHas('do',function($query2) use($result){
                                        $query2->where('status','>',1);
                                        $query2->where('warehouse_id',$result->warehouse_id);
                                      })
                                      ->orderBy('id','ASC')
                                      ->get();

        $result_do_mutation = DeliveryOrderMutationItem::where('product_id',$result->product_id)
                                      ->whereHas('do_mutation',function($query2) use($result){
                                        $query2->where('status','>',1);
                                        $query2->where('origin_warehouse_id',$result->warehouse_id);
                                      })
                                      ->orderBy('id','ASC')
                                      ->get();

        $result_canvasing = CanvasingItem::where('product_id',$result->product_id)
                                      ->whereHas('canvasing',function($query2) use($result){
                                        $query2->where('status','>',1);
                                        $query2->where('warehouse_id',$result->warehouse_id);
                                      })
                                      ->orderBy('id','ASC')
                                      ->get();
        $stock_move = StockMove::where('product_id',$result->product_id)
                                ->where('warehouse_id',$result->warehouse_id)
                                ->orderBy('id','DESC')
                                ->get();

        $data = [
            'result' => $result,
            'result_do' => $result_do,
            'result_do_mutation' => $result_do_mutation,
            'result_canvasing' => $result_canvasing,
            'stock_move' => $stock_move

        ];
        return view($this->view."detail",$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
}
