<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderCost;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Master\Customer;
use App\Entities\Master\Company;
use App\Entities\Master\Ekspedisi;
use App\Entities\Finance\Invoicing;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use DB;
use Auth;
use PDF;

class InvoicingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.finance.invoicing.";
        $this->route = "superuser.finance.invoicing";
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
        $do_id = $request->input('do_id');
        $customer_id = $request->input('customer_id');
        $order = PackingOrder::where('status', '>=', 4)
                            ->doesntHave('invoicing')
                            ->orderBy('id','DESC')
                            ->get();
        $table = Invoicing::whereHas('do',function($query2) use($do_id,$customer_id){
                                if(!empty($do_id)){
                                    $query2->where('id',$do_id);
                                }
                                if(!empty($customer_id)){
                                    $query2->where('customer_id',$customer_id);
                                }
                            })
                            ->where(function($query2) use($search){
                                if(!empty($search)){
                                    $query2->where('code','like','%'.$search.'%');
                                }
                            })
                            ->orderBy('id','DESC')
                            ->paginate(10);
        $table->withPath('invoicing?search='.$search."&do_id=".$do_id."&customer_id=".$customer_id);
        $customer = Customer::get();
        $data = [
            'order' => $order,
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
    public function create(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $post = $request->all();
        if(empty($post["id"])){
            return redirect()->route('superuser.finance.invoicing.index')->with('error','Tidak ada delivery order yang dipilih');
        }
        $result = PackingOrder::where('id',$post["id"])->first();
        if($result->status < 4){
            return redirect()->route('superuser.finance.invoicing.index')->with('error','Order belum bisa di dieksekusi');
        }
        if(!empty($result->invoicing ?? 0)){
            return redirect()->route('superuser.finance.invoicing.index')->with('error','Invoice sudah dibuat');
        }
        $ekspedisi = Ekspedisi::all();
        $data = [
            'ekspedisi' => $ekspedisi,
            'result' => $result
        ];
        return view($this->view."create",$data);
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
        $data = [
            'result' => $result
        ];
        return view($this->view."detail",$data);
    }

    public function history_payable($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Invoicing::where('id',$id)->first();
        $data = [
            'result' => $result
        ];
        return view($this->view."history_payable",$data);
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
    public function update_other_cost(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            DB::beginTransaction();
            try{
                if(count($post["repeater"]) == 0){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Form tidak boleh kosong";
                    goto ResultData;
                }
                foreach ($post["repeater"] as $index => $value) {
                    if(empty($value["id"])){
                        $data_json["IsError"] = TRUE;
                        $data_json["Message"] = "ID Other Cost tidak boleh kosong";
                        goto ResultData;   
                    }
                    if(empty($value["note"])){
                        $data_json["IsError"] = TRUE;
                        $data_json["Message"] = "Note wajib diisi";
                        goto ResultData;   
                    }
                    if(empty($value["cost_idr"])){
                        $data_json["IsError"] = TRUE;
                        $data_json["Message"] = "Cost IDR wajib diisi";
                        goto ResultData;   
                    }

                    $data = [
                        'note' => trim(htmlentities($value["note"])),
                        'cost_idr' => trim(htmlentities($value["cost_idr"])),
                        'updated_by' => Auth::id(),
                    ];

                    $update = PackingOrderCost::where('id',$value["id"])->update($data);
                }
                DB::commit();

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Cost Berhasil Diubah";
                goto ResultData;  

            }catch(\Throwable $e){
                DB::rollback();
                $data_json["IsError"] = TRUE;
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
    public function update_pemesan(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            DB::beginTransaction();
            try{
                if(empty($post["idr_rate"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "IDR Rate tidak boleh kosong";
                    goto ResultData;
                }
                if($post["idr_rate"] == 1){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Harap masukkan IDR Rate yang direkomendasikan";
                    goto ResultData;
                }
                $get = PackingOrder::where('id',$post["id"])->first();
                $idr_rate = str_replace('.', '', $post["idr_rate"]);
                
                $this->reset_cost_if_change_idr_rate($post["id"],$idr_rate);

                $data = [
                    'idr_rate' => trim(htmlentities($idr_rate)),
                    'note' => trim(htmlentities($post["note"])),
                    'updated_by' => Auth::id(),
                ];

                $update = PackingOrder::where('id',$post["id"])->update($data);

                DB::commit();
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Data Pemesan Berhasil diubah";
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
                $detail_po = PackingOrder::where('id',$check_cost->do_id)->first();
                $detail_po_item = PackingOrderItem::where('do_id',$check_cost->do_id)->get();

                $idr_total = 0;
                foreach ($detail_po_item as $key => $row) {
                    $idr_total += ceil((($row->price * $detail_po->idr_rate) * $row->qty) - ($row->total_disc * $detail_po->idr_rate)); 
                }

                $discount_1 = empty($post["discount_1"]) ? 0 : $post["discount_1"] / 100;
                $discount_2 = empty($post["discount_2"]) ? 0 : $post["discount_2"] / 100;
                $discount_idr = empty($post["discount_idr"]) ? 0 : $post["discount_idr"];
                $voucher_idr = empty($post["voucher_idr"]) ? 0 : $post["voucher_idr"];
                $cashback_idr = empty($post["cashback_idr"]) ? 0 : $post["cashback_idr"];
                $delivery_cost_idr = empty($post["delivery_cost_idr"]) ? 0 : $post["delivery_cost_idr"];
                $other_cost_idr = empty($post["other_cost_idr"]) ? 0 : $post["other_cost_idr"];

                $discount_idr = str_replace('.', '', $discount_idr);
                $voucher_idr = str_replace('.', '', $voucher_idr);
                $cashback_idr = str_replace('.', '', $cashback_idr);
                $delivery_cost_idr = str_replace('.', '', $delivery_cost_idr);
                $other_cost_idr = str_replace('.', '', $other_cost_idr);

                $total_discount_idr = ceil(( $idr_total * $discount_1 ) + (($idr_total - ($idr_total * $discount_1)) * $discount_2) + $discount_idr);

                $ppn = $check_cost->ppn;
                
                $purchase_total_idr = ceil($idr_total - $total_discount_idr - $voucher_idr - $cashback_idr + $ppn);
                $grand_total_idr = ceil($purchase_total_idr + $delivery_cost_idr + $other_cost_idr);


                if($total_discount_idr > $grand_total_idr){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Total Discount melebihi IDR total item pembelian";
                    goto ResultData;
                }
                if($purchase_total_idr > $grand_total_idr){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Total Purchase melebihi IDR total item pembelian";
                    goto ResultData;
                }

                $data = [
                    'discount_1' => trim(htmlentities($post["discount_1"])),
                    'discount_2' => trim(htmlentities($post["discount_2"])),
                    'discount_idr' => trim(htmlentities($discount_idr)),
                    'total_discount_idr' => trim(htmlentities($total_discount_idr)),
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
                    $data_json["Message"] = "Cost berhasil diubah";
                    goto ResultData;
                }
                else{
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Cost gagal diubah";
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
    public function store_invoicing(Request $request){
        $post = $request->all();
        $request->validate([
            'do_id' => 'required'
        ]);
        try{
        
            $cek = Invoicing::where('do_id',$post["do_id"])->first();
            $cost = PackingOrderCost::where('do_id',$post["do_id"])->sum('cost_idr');
            $do = PackingOrder::where('id',$post["do_id"])->first();
            $do_detail = PackingOrderDetail::where('do_id',$do->id)->first();
            $total = ceil($do_detail->grand_total_idr);

            $image = $request->file('image');

            if($do_detail->grand_total_idr <= 0){
                return redirect()->route('superuser.finance.invoicing.index')->with('error',"Harga keseluruhan belum disimpan");
            }
            
            if($cek){
                return redirect()->route('superuser.finance.invoicing.index')->with('error',"Invoicing untuk Order ".$do->do_code." sudah dibuat");
            }
            if($do->status < 4){
                return redirect()->route('superuser.finance.invoicing.index')->with('error',"Delivery order belum bisa ditambahkan ke invoicing");
            }

            if(!empty($image)){
                $extension = $image->getClientOriginalExtension();
                $valid_ext = ['jpeg','png','jpg','gif'];

                if(!in_array(strtolower($extension), $valid_ext)){
                    return redirect()->route('superuser.finance.invoicing.index')->with('error',"Format image diperbolehkan yaitu jpeg,jpg,png,gif");
                }
            }

            $data = [
                'code' => CodeRepo::generateInvoicing($do->do_code),
                'do_id' =>trim(htmlentities($post["do_id"])),
                'grand_total_idr' => $total,
                'image' => (empty($image)) ? null : $image->store('images/finance/invoicing', 'public'),
                'created_by' => Auth::id()
            ];


            $insert = Invoicing::create($data);

            if($insert){
                return redirect()->route('superuser.finance.invoicing.index')->with('success',"Invoice berhasil dibuat"); 
            }
            else{
                return redirect()->route('superuser.finance.invoicing.index')->with('error',"Invoice gagal dibuat"); 
            }

        }catch(\Throwable $e){
           return redirect()->route('superuser.finance.invoicing.index')->with('error',$e->getMessage()); 
        }
        

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

    public function print($id, $paid = null, $type = null){

        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Invoicing::where('id',$id)->first();
        $company = Company::first();
        if(empty($result)){
            abort(404);
        }

        $data = [
            'result' => $result,
            'company' => $company,
            'watermark' => $result->do->type_transaction === 1 ? 'Paid' : 'Unpaid'
        ];
        
        $pdf = PDF::loadview($this->view."print_new",$data)->setPaper('a4','portait');
        return $pdf->stream($result->code ?? '');
    }

    public function print_paid($id, $type = null){
        return $this->print($id, true, $type);
    }

    public function print_portait($id){

        return $this->print($id, true, 2);
    }

    public function print_proforma($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Invoicing::where('id',$id)->first();
        $company = Company::first();
        if(empty($result)){
            abort(404);
        }

        $data = [
            'result' => $result,
            'company' => $company
        ];

        $pdf = PDF::loadview($this->view."print_proforma_new",$data)->setPaper('a4','portait');
        return $pdf->stream($result->do->do_code ?? '');
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
    private function reset_cost($id){
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
