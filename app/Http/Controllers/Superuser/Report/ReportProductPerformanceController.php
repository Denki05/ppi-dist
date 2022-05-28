<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\BrandReference;
use App\Entities\Master\Product;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Customer;
use App\Entities\Master\Company;
use App\Entities\Master\Warehouse;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\DeliveryOrderMutationItem;
use App\Entities\Penjualan\CanvasingItem;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Gudang\StockMove;
use App\Entities\Setting\UserMenu;
use DB;
use Auth;
use PDF;

class ReportProductPerformanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.report.product_performance.";
        $this->route = "superuser.report.product_performance";
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

        $brand_reference_id = $request->input('brand_reference_id');
        $product_id = $request->input('product_id');
        $period_from = $request->input('period_from');
        $period_to = $request->input('period_to');
        $filter_by = $request->input('filter_by');
        $customer_id = $request->input('customer_id');
        $warehouse_id = $request->input('warehouse_id');

        $brand_reference = BrandReference::get();
        $product = Product::get();
        $customer = Customer::get();
        $warehouse = Warehouse::get();

        $table = Product::orderBy('id','DESC')
                        ->where(function($query2) use($brand_reference_id){
                            if(!empty($brand_reference_id)){
                                $query2->where('brand_reference_id',$brand_reference_id);
                            }
                        })
                        ->where(function($query2) use($product_id){
                            if(!empty($product_id)){
                                $query2->where('id',$product_id);
                            }
                        })
                        ->where(function($query2) use($filter_by){
                            if(!empty($filter_by) && $filter_by == "sales_order"){
                                $query2->whereHas('so_item',function($query3){
                                    $query3->where('qty','>',0);
                                });
                            }
                            if(!empty($filter_by) && $filter_by == "delivery_order"){
                                $query2->whereHas('do_item',function($query3){
                                    $query3->where('qty','>',0);
                                });
                            }
                        })
                        ->where(function($query2) use($customer_id){
                            if(!empty($customer_id)){
                                $query2->whereHas('so_item',function($query3) use($customer_id){
                                    $query3->whereHas('so',function($query4) use($customer_id){
                                        $query4->where('customer_id',$customer_id);
                                    });
                                });
                                $query2->whereHas('do_item',function($query3) use($customer_id){
                                    $query3->whereHas('do',function($query4) use($customer_id){
                                        $query4->where('customer_id',$customer_id);
                                    });
                                });
                            }
                        })
                        ->paginate(10);

        foreach ($table as $index => $row) {
            $product_min_stock = ProductMinStock::select(DB::raw('SUM(master_product_min_stocks.quantity) as stock_in'),'master_product_min_stocks.product_id')
                                ->groupBy('product_id')
                                ->where('product_id',$row->id)
                                ->where(function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('warehouse_id',$warehouse_id);
                                    }
                                })
                                ->first();
            

            $stock = 0;
            $stock_in = 0;
            $stock_out = 0;
            $effective = 0;
            $do = 0;
            $so = 0;
            $move_in = 0;

            if(!empty($product_min_stock)){
                $stock_in = floatval($product_min_stock->stock_in);    
            }
         
            $so = SalesOrderItem::where('product_id',$row->id)
                                ->where(function($query2) use($period_from,$period_to){
                                    if($period_from){
                                        $query2->where('created_at','>=',$period_from);
                                    }
                                    if($period_to){
                                        $query2->where('created_at','<=',$period_to);
                                    }
                                })
                                ->whereHas('so',function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('origin_warehouse_id',$warehouse_id);
                                    }
                                })
                                ->whereHas('so')->sum('qty');

            $do = PackingOrderItem::where('product_id',$row->id)
                                ->where(function($query2) use($period_from,$period_to){
                                    if($period_from){
                                        $query2->where('created_at','>=',$period_from);
                                    }
                                    if($period_to){
                                        $query2->where('created_at','<=',$period_to);
                                    }
                                })
                                ->whereHas('do',function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('warehouse_id',$warehouse_id);
                                    }
                                })
                                ->whereHas('do')->sum('qty');

            $move = StockMove::where('product_id',$row->id)
                                ->where(function($query2) use($period_from,$period_to){
                                    if($period_from){
                                        $query2->where('created_at','>=',$period_from);
                                    }
                                    if($period_to){
                                        $query2->where('created_at','<=',$period_to);
                                    }
                                })
                                ->where(function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('warehouse_id',$warehouse_id);
                                    }
                                })
                                ->get();

            $move_in = $move->sum('stock_in');
            $move_out = $move->sum('stock_out');

            $stock_out = $move_out;
            $stock  = floatval($stock_in + $move_in - $move_out);
            $effective = $stock - $so;
            

            $row->stock = $stock;
            $row->stock_in = $move_in;
            $row->stock_out = $stock_out;
            $row->so = floatval($so);
            $row->do = floatval($do);
            $row->effective = $effective;
        }

        if(!empty($filter_by)){
            if($filter_by == "inventory"){
                $table->setCollection(
                    collect(
                        collect($table->items())->sortByDesc('stock')
                    )->values()
                );
            }
            if($filter_by == "sales_order"){
                $table->setCollection(
                    collect(
                        collect($table->items())->sortByDesc('so')
                    )->values()
                );
            }
            if($filter_by == "delivery_order"){
                $table->setCollection(
                    collect(
                        collect($table->items())->sortByDesc('do')
                    )->values()
                );
            }
        }
        

        $customer_detail = Customer::where('id',$customer_id)->first();
        $warehouse_detail = Warehouse::where('id',$warehouse_id)->first();

        $data = [
            'brand_reference' => $brand_reference,
            'product' => $product,
            'customer' => $customer,
            'customer_detail' => $customer_detail,
            'warehouse_detail' => $warehouse_detail,
            'warehouse' => $warehouse,
            'table' => $table
        ];
        return view($this->view."index",$data);
    }

    public function print(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $brand_reference_id = $request->input('brand_reference_id');
        $product_id = $request->input('product_id');
        $period_from = $request->input('period_from');
        $period_to = $request->input('period_to');
        $filter_by = $request->input('filter_by');
        $customer_id = $request->input('customer_id');
        $warehouse_id = $request->input('warehouse_id');

        $brand_reference = BrandReference::get();
        $product = Product::get();
        $customer = Customer::get();

        $table = Product::orderBy('id','DESC')
                        ->where(function($query2) use($brand_reference_id){
                            if(!empty($brand_reference_id)){
                                $query2->where('brand_reference_id',$brand_reference_id);
                            }
                        })
                        ->where(function($query2) use($product_id){
                            if(!empty($product_id)){
                                $query2->where('id',$product_id);
                            }
                        })
                        ->where(function($query2) use($filter_by){
                            if(!empty($filter_by) && $filter_by == "sales_order"){
                                $query2->whereHas('so_item',function($query3){
                                    $query3->where('qty','>',0);
                                });
                            }
                            if(!empty($filter_by) && $filter_by == "delivery_order"){
                                $query2->whereHas('do_item',function($query3){
                                    $query3->where('qty','>',0);
                                });
                            }
                        })
                        ->where(function($query2) use($customer_id){
                            if(!empty($customer_id)){
                                $query2->whereHas('so_item',function($query3) use($customer_id){
                                    $query3->whereHas('so',function($query4) use($customer_id){
                                        $query4->where('customer_id',$customer_id);
                                    });
                                });
                                $query2->whereHas('do_item',function($query3) use($customer_id){
                                    $query3->whereHas('do',function($query4) use($customer_id){
                                        $query4->where('customer_id',$customer_id);
                                    });
                                });
                            }
                        })
                        ->limit(10)
                        ->get();

        foreach ($table as $index => $row) {
            $product_min_stock = ProductMinStock::select(DB::raw('SUM(master_product_min_stocks.quantity) as stock_in'),'master_product_min_stocks.product_id')
                                ->groupBy('product_id')
                                ->where('product_id',$row->id)
                                ->where(function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('warehouse_id',$warehouse_id);
                                    }
                                })
                                ->first();
            

            $stock = 0;
            $stock_in = 0;
            $stock_out = 0;
            $effective = 0;
            $do = 0;
            $so = 0;
            $move_in = 0;

            if(!empty($product_min_stock)){
                $stock_in = floatval($product_min_stock->stock_in);    
            }
         
            $so = SalesOrderItem::where('product_id',$row->id)
                                ->where(function($query2) use($period_from,$period_to){
                                    if($period_from){
                                        $query2->where('created_at','>=',$period_from);
                                    }
                                    if($period_to){
                                        $query2->where('created_at','<=',$period_to);
                                    }
                                })
                                ->whereHas('so',function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('origin_warehouse_id',$warehouse_id);
                                    }
                                })
                                ->whereHas('so')->sum('qty');

            $do = PackingOrderItem::where('product_id',$row->id)
                                ->where(function($query2) use($period_from,$period_to){
                                    if($period_from){
                                        $query2->where('created_at','>=',$period_from);
                                    }
                                    if($period_to){
                                        $query2->where('created_at','<=',$period_to);
                                    }
                                })
                                ->whereHas('do',function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('warehouse_id',$warehouse_id);
                                    }
                                })
                                ->whereHas('do')->sum('qty');

            $move = StockMove::where('product_id',$row->id)
                                ->where(function($query2) use($period_from,$period_to){
                                    if($period_from){
                                        $query2->where('created_at','>=',$period_from);
                                    }
                                    if($period_to){
                                        $query2->where('created_at','<=',$period_to);
                                    }
                                })
                                ->where(function($query2) use($warehouse_id){
                                    if(!empty($warehouse_id)){
                                        $query2->where('warehouse_id',$warehouse_id);
                                    }
                                })
                                ->get();

            $move_in = $move->sum('stock_in');
            $move_out = $move->sum('stock_out');

            $stock_out = $move_out;
            $stock  = floatval($stock_in + $move_in - $move_out);
            $effective = $stock - $so;
            

            $row->stock = $stock;
            $row->stock_in = $move_in;
            $row->stock_out = $stock_out;
            $row->so = floatval($so);
            $row->do = floatval($do);
            $row->effective = $effective;
        }

        if(!empty($filter_by)){
            if($filter_by == "inventory"){
                $table = $table->sortByDesc('stock');
            }
            if($filter_by == "delivery_order"){
                $table = $table->sortByDesc('do');
            }
            if($filter_by == "sales_order"){
                $table = $table->sortByDesc('so');
            }
        }

        $customer_detail = Customer::where('id',$customer_id)->first();
        $warehouse_detail = Warehouse::where('id',$warehouse_id)->first();
        $product_detail = Product::where('id',$product_id)->first();
        $brand_reference_detail = BrandReference::where('id',$brand_reference_id)->first();
        $company = Company::first();

        $data = [
            'brand_reference' => $brand_reference,
            'product' => $product,
            'customer' => $customer,
            'customer_detail' => $customer_detail,
            'warehouse_detail' => $warehouse_detail,
            'product_detail' => $product_detail,
            'brand_reference_detail' => $brand_reference_detail,
            'company' => $company,
            'table' => $table
        ];


        $pdf = PDF::loadview($this->view."print",$data)->setPaper('a4','potrait');
        return $pdf->stream('Report Product Performance');
        

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
