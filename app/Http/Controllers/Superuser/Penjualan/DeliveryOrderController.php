<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Customer;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Company;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderCost;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderLogPrint;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Gudang\StockMove;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use Illuminate\Support\Collection;
use PDF;
use DB;
use Auth;


class DeliveryOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.penjualan.delivery_order.";
        $this->route = "superuser.penjualan.delivery_order";
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

        $field = $request->input('field');
        $search = $request->input('search');
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
                            ->whereIn('status', [3, 4, 5, 6])
                            ->orderBy('id','DESC')
                            ->paginate(10);
        /*$table = PackingOrder::where(function($query2) use($customer_id){
                                if(!empty($customer_id)){
                                    $query2->where('customer_id',$customer_id);
                                }
                            })
                            ->where(function($query2) use($search){
                                if(!empty($search)){
                                    $query2->where('code','like','%'.$search.'%');
                                    $query2->orWhere('do_code','like','%'.$search.'%');
                                }
                            })
                            ->whereIn('status', [3, 4, 5, 6])
                            ->orderBy('id','DESC')
                            ->paginate(10);*/
        $table->withPath('delivery_order?field='.$field.'&search='.$search);
        $customer = Customer::all();
        $data = [
            'table' => $table,
            'customer' => $customer
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
        return view($this->view."detail_new",$data);
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
    public function print($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();
        $company = Company::first();
        if(empty($result)){
            abort(404);
        }
        if($result->status <= 3){
            return redirect()->back()->with('error','Tidak bisa print delivery order. Status delivery order belum memenuhi syarat');
        }
        $data = [
            'result' => $result,
            'company' => $company
        ];
        $log = PackingOrderLogPrint::create([
            'do_id' => $id,
            'created_by' => Auth::id()
        ]);
        
        $width = 21;
        $height = 14.8;
        $customPaper = array(0,0,($width/2.54*72),($height/2.54*72));
        $pdf = PDF::loadview($this->view."print_new",$data)->setPaper($customPaper,'portait');
        if(!empty($result->do_code)){
            return $pdf->stream($result->do_code ?? '');    
        }
        return $pdf->stream($result->do_code ?? '');
    }
    public function packed(Request $request)
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
            $result = PackingOrder::where('id',$post["id"])->first();

            if($result->status == 1){
                return redirect()->route('superuser.penjualan.packing_order.index')->with('error','Tidak bisa mengirim packing order yang masih baru dibuat');
            }
            if(count($result->do_detail) == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Tidak ada item sama sekali');
            }
            if($result->do_cost->grand_total_idr == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Harga didalam packing list belum di set');
            }
            $update = PackingOrder::where('id',$post["id"])->update(['status' => 4]);

            DB::commit();
            return redirect()->back()->with('success','Delivery Order berhasil diubah ke packed');  
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
    public function sending(Request $request)
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
            $result = PackingOrder::where('id',$post["id"])->first();

            PackingOrder::where('id',$result->id)->update([
                'date_sent' => date('Y-m-d')
            ]);

            if($result->status == 1){
                return redirect()->route('superuser.penjualan.packing_order.index')->with('error','Tidak bisa mengirim packing order yang masih baru dibuat');
            }
            if(count($result->do_detail) == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Tidak ada item sama sekali');
            }
            if($result->do_cost->grand_total_idr == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Harga didalam packing list belum di set');
            }
            $update = PackingOrder::where('id',$post["id"])->update(['status' => 5]);

            DB::commit();
            return redirect()->back()->with('success','Delivery Order berhasil diubah ke delivery');  
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function upload_image(Request $request) {
        $post = $request->all();
        $request->validate([
            'do_id' => 'required'
        ]);

        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->back()->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        if($request->method() == "POST"){
            DB::beginTransaction();

            try {
                $image = $request->file('image');

                if(!empty($image)){
                    $extension = $image->getClientOriginalExtension();
                    $valid_ext = ['jpeg','png','jpg','gif'];
    
                    if(!in_array(strtolower($extension), $valid_ext)){
                        return redirect()->route('superuser.penjualan.delivery_order.detail')->with('error',"Format image diperbolehkan yaitu jpeg,jpg,png,gif");
                    }
                
                    $data = [
                        'image' => (empty($image)) ? null : $image->store('images/delivery_order/expedition_receipt', 'public'),
                        'updated_by' => Auth::id(),
                    ];

                    $update = PackingOrder::where('id',$post["do_id"])->update($data);
                    
                    DB::commit();

                    return redirect()->route('superuser.penjualan.delivery_order.detail', $post['do_id'])->with('success','Image berhasil diupload');
                }
            }   catch(\Throwable $e){
                DB::rollback();
                return redirect()->back()->with('error',$e->getMessage());
            }
        }

        ResultData:
        return response()->json($data_json,200);
    }

    public function sent(Request $request){
        $data_json = [];
        $post = $request->all();

        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = 'Anda tidak punya akses untuk membuka menu terkait';
                goto ResultData;
            }
        }

        if($request->method() == "POST"){
            DB::beginTransaction();
            try{
                $image = $request->file('image');

                if(!empty($image)){
                    $extension = $image->getClientOriginalExtension();
                    $valid_ext = ['jpeg','png','jpg','gif'];
    
                    if(!in_array(strtolower($extension), $valid_ext)){
                        return redirect()->route('superuser.penjualan.delivery_order.detail')->with('error',"Format image diperbolehkan yaitu jpeg,jpg,png,gif");
                    }
                
                    $data = [
                        'image' => (empty($image)) ? null : $image->store('images/delivery_order/expedition_receipt', 'public'),
                        'updated_by' => Auth::id(),
                    ];

                    $update = PackingOrder::where('id',$post["do_id"])->update($data);
                }

                $delivery_cost_idr = (empty($post["delivery_cost_idr"])) ? 0 : $post["delivery_cost_idr"]; 
                $other_cost_idr = (empty($post["other_cost_idr"])) ? 0 : $post["other_cost_idr"];

                $result_cost = PackingOrderDetail::where('do_id',$post["do_id"])->first();

                $grand_total_idr = ceil($result_cost->grand_total_idr - $result_cost->delivery_cost_idr - $result_cost->other_cost_idr + $delivery_cost_idr + $other_cost_idr);

                $update_cost = PackingOrderDetail::where('do_id',$post["do_id"])->update([
                    'delivery_cost_note' => trim(htmlentities($post["delivery_cost_note"])),
                    'delivery_cost_idr' => $delivery_cost_idr,
                    'other_cost_note' => trim(htmlentities($post["other_cost_note"])),
                    'other_cost_idr' => $other_cost_idr,
                    'grand_total_idr' => $grand_total_idr,
                    'updated_by' => Auth::id(),
                ]);

                $detail_do = PackingOrder::where('id',$post["do_id"])->first();
                $detail_item = PackingOrderItem::where('do_id',$post["do_id"])->get();

                if(empty($detail_do->do_code)){
                    $detail_do->update([
                        'do_code' => CodeRepo::generateDO(),
                        'date_sent' => date('Y-m-d')
                    ]);
                }

                $detail_do = PackingOrder::where('id',$post["do_id"])->first();

                foreach ($detail_item as $key => $value) {
                    // Definisi stock sebelum pemotongan
                    $stock_product = ProductMinStock::where('product_id',$value->product_id)
                                                    ->where('warehouse_id',$detail_do->warehouse_id)
                                                    ->sum('quantity');
                    // Definisi membaca product dan warehouse
                    $move = StockMove::where('product_id',$value->product_id)
                                        ->where('warehouse_id',$detail_do->warehouse_id)->get();
                    // defisini stock in atau out
                    $move_in = $move->sum('stock_in');
                    $move_out = $move->sum('stock_out');
                    // Pemotongan stock
                    $sisa = (int)$stock_product + $move_in - $move_out - $value->qty;
                    // Pencatatan stock setelah di potong
                    $insert_stock_move = StockMove::create([
                        'code_transaction' => $detail_do->do_code,
                        'warehouse_id' => $detail_do->warehouse_id,
                        'product_id' => $value->product_id,
                        'stock_out' => $value->qty,
                        'stock_balance' => $sisa,
                        'created_by' => Auth::id()
                    ]);
                }


                $update = PackingOrder::where('id',$post["do_id"])->update(['status' => 6]);
              
                DB::commit();

                return redirect()->route('superuser.penjualan.delivery_order.detail', $post["do_id"]);

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
    // public function sent(Request $request){
    //     $data_json = [];
    //     $post = $request->all();
    //     if($request->method() == "POST"){
    //         DB::beginTransaction();
    //         try{
    //             if(empty($post["do_id"])){
    //                 $data_json["IsError"] = TRUE;
    //                 $data_json["Message"] = "Order ID tidak boleh kosong";
    //                 goto ResultData;
    //             }

    //             if(count($post["repeater"]) == 0){
    //                 $data_json["IsError"] = TRUE;
    //                 $data_json["Message"] = "Form tidak boleh kosong";
    //                 goto ResultData;
    //             }
    //             $delete_all = PackingOrderCost::where('do_id',$post["do_id"])->delete();
    //             foreach ($post["repeater"] as $index => $value) {
    //                 if(empty($value["note"])){
    //                     $data_json["IsError"] = TRUE;
    //                     $data_json["Message"] = "Note harus diisi";
    //                     goto ResultData;
    //                 }

    //                 if(empty($value["cost_idr"])){
    //                     $data_json["IsError"] = TRUE;
    //                     $data_json["Message"] = "Cost harus diisi";
    //                     goto ResultData;
    //                 }

                   
    //                $data = [
    //                    'do_id' => trim(htmlentities($post["do_id"])),
    //                    'note' => trim(htmlentities($value["note"])),
    //                    'cost_idr' => trim(htmlentities($value["cost_idr"])),
    //                    'created_by' => Auth::id(),
    //                    'updated_by' => Auth::id(),
    //                ];

    //                $insert = PackingOrderCost::create($data);
                    

                    
    //             }

    //             $update = PackingOrder::where('id',$post["do_id"])->update(['status' => 4]);
              

    //             DB::commit();

    //             $data_json["IsError"] = FALSE;
    //             $data_json["Message"] = "Cost berhasil diubah";
    //             goto ResultData;

    //         }catch(\Throwable $e){
    //             DB::rollback();
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = $e->getMessage();
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
    public function get_cost(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                if(empty($post["do_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Order ID tidak boleh kosong";
                    goto ResultData;
                }

                $get = PackingOrderCost::where('do_id',$post["do_id"])->get();

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
    public function print_proforma($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();
        $company = Company::first();
        if(empty($result)){
            abort(404);
        }

        $data = [
            'result' => $result,
            'company' => $company
        ];

        $pdf = PDF::loadview($this->view."print_proforma",$data)->setPaper('a4','potrait');
        return $pdf->stream($result->code ?? '');
    }
    public function print_manifest($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();

        $do_item = PackingOrderItem::where('do_id',$result->id)->with('product')
                                    ->get()
                                    ->sortBy(function($value) {
                                        return $value->product->name;
                                    });

        $company = Company::first();
        if(empty($result)){
            abort(404);
        }
        if($result->status == 2){
            return redirect()->back()->with('error','Tidak bisa print manifest.Status delivery order belum memenuhi syarat');
        }
        $data = [
            'result' => $result,
            'company' => $company,
            'result_item' => $do_item
        ];
        
        $pdf = PDF::loadview($this->view."print_manifest",$data)->setPaper('a4','landscape');
        
        return $pdf->stream($result->code ?? '');
    }

}
