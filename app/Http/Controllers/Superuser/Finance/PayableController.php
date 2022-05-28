<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Customer;
use App\Entities\Master\Company;
use App\Entities\Finance\Invoicing;
use App\Entities\Finance\Payable;
use App\Entities\Finance\PayableDetail;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use DB;
use Auth;
use PDF;

class PayableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.finance.payable.";
        $this->route = "superuser.finance.payable";
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
        $customer_id = $request->input('customer_id');
        $table = Payable::where(function($query2) use($search){
                            if(!empty($search)){
                                $query2->where('code','like','%'.$search.'%');
                            }
                        })
                        ->where(function($query2) use($customer_id){
                            if(!empty($customer_id)){
                                $query2->where('customer_id',$customer_id);
                            }
                        })
                        ->orderBy('id','DESC')
                        ->paginate(10);
        $customer = Customer::get();
        $data = [
            'customer' => $customer,
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

        if(empty($request->input('customer_id'))){
            return redirect()->route('superuser.finance.payable.index')->with('error','Tidak ada customer yang dipilih');
        }
        $customer = Customer::where('id',$request->input('customer_id'))->first();
        if(empty($customer)){
            return redirect()->route('superuser.finance.payable.index')->with('error','Customer tidak ditemukan');
        }
        $data = [
            'customer' => $customer
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
            DB::beginTransaction();
            try{
                if(empty($post["customer_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Customer ID tidak boleh kosong";
                    goto ResultData;
                }
                if(!isset($post["repeater"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Tidak ada invoice terkait";
                    goto ResultData;
                }
                if(count($post["repeater"]) == 0){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Tidak ada invoice terkait";
                    goto ResultData;
                }
                $insert = Payable::create([
                    'code' => CodeRepo::generatePayable(),
                    'customer_id' => trim(htmlentities($post["customer_id"])),
                    'created_by' => Auth::id(),
                    'total' => 0
                ]);
                $total_payable = 0;
                foreach ($post["repeater"] as $index => $value) {
                    if(empty($value["invoice_id"])){
                        $data_json["IsError"] = TRUE;
                        $data_json["Message"] = "Invoice ID tidak boleh kosong";
                        goto ResultData;
                    }
                    if(!empty($value["payable"])){
                        $input_payable = floatval(str_replace(".", "", $value["payable"]));
                        $get_invoice = Invoicing::where('id',$value["invoice_id"])->first();
                        $payable = $get_invoice->payable_detail->sum('total');
                        $sisa = $get_invoice->grand_total_idr - $payable;
                        
                        if($payable >= $get_invoice->grand_total_idr){
                            $data_json["IsError"] = TRUE;
                            $data_json["Message"] = "Invoice ".$get_invoice->code. " sudah lunas";
                            goto ResultData;
                        }
                        $data = [
                            'payable_id' => $insert->id,
                            'invoice_id' => $value["invoice_id"],
                            'total' => $input_payable,
                            'prev_account_receivable' => $get_invoice->grand_total_idr - $payable,
                            'created_by' => Auth::id(),
                        ];

                        $insert_detail = PayableDetail::create($data);
                        $total_payable += $input_payable;
                    }
                }
                if($total_payable == 0){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Tidak bisa melakukan payable.Tidak ada payable yang diinput";
                    goto ResultData;
                }
                $update = Payable::where('id',$insert->id)->update([
                    'total' => $total_payable
                ]);

                DB::commit();

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Payable berhasil dibuat";
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

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Payable::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $data = [
            'result' => $result
        ];
        return view($this->view."detail",$data);
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
    public function print($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Payable::where('id',$id)->first();
        $company = Company::first();
        if(empty($result)){
            abort(404);
        }

        $data = [
            'result' => $result,
            'company' => $company
        ];

        $pdf = PDF::loadview($this->view."print",$data)->setPaper('a4','potrait');
        return $pdf->stream($result->code ?? '');
    }
}
