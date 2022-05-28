<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Sales;
use App\Entities\Master\Product;
use App\Entities\Master\Warehouse;
use App\Entities\Penjualan\Canvasing;
use App\Entities\Penjualan\CanvasingItem;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Entities\Setting\UserMenu;
use App\Entities\Master\Company;
use App\Repositories\CodeRepo;
use Auth;
use DB;
use PDF;

class CanvasingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.penjualan.canvasing.";
        $this->route = "superuser.penjualan.canvasing";
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
        $sales_id = $request->input('sales_id');
        $warehouse_id = $request->input('warehouse_id');
        $table = Canvasing::where(function($query2) use($search){
                                if(!empty($search)){
                                    $query2->where('code','like','%'.$search.'%');
                                }
                            })
                            ->where(function($query2) use($sales_id){
                                if(!empty($sales_id)){
                                    $query2->where('sales_id',$sales_id);
                                }
                            })
                            ->where(function($query2) use($warehouse_id){
                                if(!empty($warehouse_id)){
                                    $query2->where('warehouse_id',$warehouse_id);
                                }
                            })
                            ->orderBy('id','DESC')
                            ->paginate(10);
        $table->withPath('canvasing?search='.$search."&sales_id=".$sales_id."&warehouse_id=".$warehouse_id);
        $sales = Sales::get();
        $warehouse = Warehouse::get();
        $data = [
            'sales' => $sales,
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
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $sales = Sales::get();
        $warehouse = Warehouse::get();
        $data = [
            'sales' => $sales,
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
            try{

                if(empty($post["sales_id"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Sales wajib dipilih";
                    goto ResultData;
                }

                if(empty($post["warehouse_id"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Gudang wajib dipilih";
                    goto ResultData;
                }

                if(empty($post["address"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Alamat wajib diisi";
                    goto ResultData;
                }

                $data =[
                    'code' => CodeRepo::generateCanvasing(),
                    'sales_id' => trim(htmlentities($post["sales_id"])),
                    'warehouse_id' => trim(htmlentities($post["warehouse_id"])),
                    'address' => trim(htmlentities($post["address"])),
                    'status' => 1,
                    'created_by' => Auth::id()
                ];

                $insert = Canvasing::create($data);
                if($insert){
                    $data_json["IsError"]  = FALSE;
                    $data_json["Message"] = "Sales Mutation Berhasil Dibuat";
                    goto ResultData;
                }
                else{}
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Sales Mutation Gagal Dibuat";
                    goto ResultData;


            }catch(\Throwable $e){
                $data_json["IsError"]  = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultData;
            }

        }else{
            $data_json["IsError"]  = TRUE;
            $data_json["Message"] = "Invalid method";
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
            try{

                if(empty($post["canvasing_id"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Canvasing ID tidak boleh kosong";
                    goto ResultData;
                }

                if(empty($post["product_id"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Product wajib dipilih";
                    goto ResultData;
                }

                if(empty($post["qty"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Qty wajib dipilih";
                    goto ResultData;
                }

                $check_item = CanvasingItem::where('canvasing_id',$post["canvasing_id"])
                                            ->where('product_id',$post["product_id"])
                                            ->first();

                if($check_item){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Item sudah ditambahkan";
                    goto ResultData;
                }

                $data =[
                    'canvasing_id' => trim(htmlentities($post["canvasing_id"])),
                    'product_id' => trim(htmlentities($post["product_id"])),
                    'qty' => trim(htmlentities($post["qty"])),
                    'created_by' => Auth::id()
                ];

                $insert = CanvasingItem::create($data);
                if($insert){
                    $data_json["IsError"]  = FALSE;
                    $data_json["Message"] = "Item berhasil ditambahkan";
                    goto ResultData;
                }
                else{}
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Item gagal ditambahkan";
                    goto ResultData;


            }catch(\Throwable $e){
                $data_json["IsError"]  = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultData;
            }

        }else{
            $data_json["IsError"]  = TRUE;
            $data_json["Message"] = "Invalid method";
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

        $result = Canvasing::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        if($result->status > 1){
            return redirect()->back()->with('error','Hanya status draft yang bisa diedit');
        }
        $product = Product::get();
        $data = [
            'product' => $product,
            'result' => $result
        ];
        return view($this->view."edit",$data);
    }

    public function edit_item($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = CanvasingItem::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $product = Product::get();
        $data = [
            'product' => $product,
            'result' => $result
        ];
        return view($this->view."edit_item",$data);
    }

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Canvasing::where('id',$id)->first();
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
    public function update(Request $request, $id)
    {
        //
    }

    public function sent(Request $request)
    {
        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();

            $result = Canvasing::where('id',$post["id"])->first();

            if($result->canvasing_item->count() <= 0){
               return redirect()->back()->with('error','Item tidak ada'); 
            }

            $update = Canvasing::where('id',$post["id"])->update([
                    'status' => 2,
                    'updated_by' => Auth::id()
            ]);

            $detail_item = CanvasingItem::where('canvasing_id',$result->id)->get();

            foreach ($detail_item as $key => $value) {
                $stock_product = ProductMinStock::where('product_id',$value->product_id)
                                                ->where('warehouse_id',$result->warehouse_id)
                                                ->sum('quantity');
                $move = StockMove::where('product_id',$value->product_id)
                                    ->where('warehouse_id',$result->warehouse_id)->get();

                $move_in = $move->sum('stock_in');
                $move_out = $move->sum('stock_out');

                $sisa = (int)$stock_product + $move_in - $move_out - $value->qty;

                $insert_stock_move = StockMove::create([
                    'code_transaction' => $result->code,
                    'warehouse_id' => $result->warehouse_id,
                    'product_id' => $value->product_id,
                    'stock_out' => $value->qty,
                    'stock_balance' => $sisa,
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->route('superuser.penjualan.canvasing.index')->with('success','Sales Mutation berhasil di ubah ke sent');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->route('superuser.penjualan.canvasing.index')->with('error',$e->getMessage());
        }
    }

    public function update_item(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                if(empty($post["id"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Canvasing Item ID tidak boleh kosong";
                    goto ResultData;
                }

                if(empty($post["product_id"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Product wajib dipilih";
                    goto ResultData;
                }

                if(empty($post["qty"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Qty wajib dipilih";
                    goto ResultData;
                }

                $result = CanvasingItem::where('id',$post["id"])->first();

                $check_item = CanvasingItem::where('id','!=',$post["id"])
                                            ->where('canvasing_id',$result->canvasing_id)
                                            ->where('product_id',$post["product_id"])
                                            ->first();

                if($check_item){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Item sudah ditambahkan";
                    goto ResultData;
                }

                $data =[
                    'product_id' => trim(htmlentities($post["product_id"])),
                    'qty' => trim(htmlentities($post["qty"])),
                    'updated_by' => Auth::id()
                ];

                $update = CanvasingItem::where('id',$post["id"])->update($data);
                if($update){
                    $data_json["IsError"]  = FALSE;
                    $data_json["Message"] = "Item berhasil diubah";
                    goto ResultData;
                }
                else{}
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Item gagal diubah";
                    goto ResultData;


            }catch(\Throwable $e){
                $data_json["IsError"]  = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultData;
            }

        }else{
            $data_json["IsError"]  = TRUE;
            $data_json["Message"] = "Invalid method";
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
            $result = Canvasing::where('id',$post["id"])->first();
            if($result->status > 1){
               return redirect()->back()->with('danger','Sales Mutation hanya bisa dihapus jika status masih draft'); 
            }
            $update = Canvasing::where('id',$post["id"])->update(['deleted_by' => Auth::id()]);
            $destroy = Canvasing::where('id',$post["id"])->delete();
        
            DB::commit();
            return redirect()->back()->with('success','Sales Mutation berhasil dihapus');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('danger',$e->getMessage());
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
            $result = CanvasingItem::where('id',$post["id"])->first();
            if($result->status > 1){
               return redirect()->back()->with('danger','Sales Mutation hanya bisa dihapus jika status masih draft'); 
            }
            $update = CanvasingItem::where('id',$post["id"])->update(['deleted_by' => Auth::id()]);
            $destroy = CanvasingItem::where('id',$post["id"])->delete();
        
            DB::commit();
            return redirect()->back()->with('success','Sales Mutation berhasil dihapus');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('danger',$e->getMessage());
        }
    }

    public function print($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Canvasing::where('id',$id)->first();
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
