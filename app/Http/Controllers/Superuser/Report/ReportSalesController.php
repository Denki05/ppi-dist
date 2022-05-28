<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Finance\Invoicing;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Master\Sales;
use App\Entities\Master\Customer;
use App\Entities\Master\Company;
use App\Entities\Setting\UserMenu;
use Auth;
use PDF;

class ReportSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.report.sales.";
        $this->route = "superuser.report.sales";
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

        $customer_id = $request->input('customer_id');
        $sales_senior_id = $request->input('sales_senior_id');
        $sales_id = $request->input('sales_id');
        $period_from = $request->input('period_from');
        $period_to = $request->input('period_to');

        $sales = Sales::get();
        $customer = Customer::get();

        $order = PackingOrder::whereHas('invoicing',function($query2) use($period_from,$period_to){
                                if($period_from){
                                    $query2->whereDate('created_at','>=',$period_from);
                                }
                                if($period_to){
                                    $query2->whereDate('created_at','<=',$period_to);
                                }
                            })
                            ->where(function($query2) use($customer_id){
                                if(!empty($customer_id)){
                                    $query2->where('customer_id',$customer_id);
                                }
                            })
                            ->get();       
        
        $final_array = [];
        foreach ($order as $key => $value) {
            $order_item = PackingOrderItem::where('do_id',$value->id)->get();
            foreach ($order_item as $k => $v) {
                $sales_order_item = SalesOrderItem::where('id',$v->so_item_id)->first();
                $sales_order = SalesOrder::where('id',$sales_order_item->so_id)->first();

                if(isset($final_array[$v->do_id])){
                    foreach ($final_array[$v->do_id]["sales_senior"] as $x => $y) {
                        if($y["id"] != $sales_order->sales_senior_id){
                            $final_array[$v->do_id]["sales_senior"][] = $sales_order->sales_senior->toArray() ?? null;
                        }
                    }

                    foreach ($final_array[$v->do_id]["sales"] as $x => $y) {
                        if($y["id"] != $sales_order->sales_id){
                            $final_array[$v->do_id]["sales"][] = $sales_order->sales->toArray() ?? null;
                        }
                    }
                }
                else{
                    $final_array[$v->do_id]["sales_senior"][] = $sales_order->sales_senior->toArray() ?? null;
                    $final_array[$v->do_id]["sales"][] = $sales_order->sales->toArray();
                    $final_array[$v->do_id]["do"] = $order[$key]->toArray();
                    $final_array[$v->do_id]["invoice"] = $value->invoicing->toArray() ?? null;
                    $final_array[$v->do_id]["payable"] = $value->invoicing->payable_detail()->get()->toArray() ?? null;    
                }

                
            }
        }


        $final_array_backup = $final_array;

        $final_array_sementara_senior = [];
        if(!empty($sales_senior_id)){
            foreach ($final_array as $key => $value) {
                foreach ($value["sales_senior"] as $k => $v) {
                    if($v["id"] == $sales_senior_id){
                        $final_array_sementara_senior[] = $final_array[$key];
                    }
                }
            }
            $final_array = $final_array_sementara_senior;
        }

        $final_array_sementara = [];
        if(!empty($sales_id)){
            foreach ($final_array as $key => $value) {
                foreach ($value["sales"] as $k => $v) {
                    if($v["id"] == $sales_id){
                        $final_array_sementara[] = $final_array[$key];
                    }
                }
            }
            $final_array = $final_array_sementara;
        }


        $final_array_sementara_final = [];
        if(!empty($sales_senior_id) && !empty($sales_id)){
            foreach ($final_array_backup as $key => $value) {
                $filter_do_id_sales_senior = "";
                foreach ($value["sales_senior"] as $k => $v) {
                    if($v["id"] == $sales_senior_id){
                        $filter_do_id_sales_senior = $value["do"]["id"];
                    }
                     
                }
                $filter_do_id_sales = "";
                foreach ($value["sales"] as $k => $v) {
                    if($v["id"] == $sales_id){
                        $filter_do_id_sales = $value["do"]["id"];
                    }
                }

                if($filter_do_id_sales == $filter_do_id_sales_senior){
                    $final_array_sementara_final[] = $final_array_backup[$key];
                }
            }
            $final_array = $final_array_sementara_final;
        }

        $customer_filter = Customer::where('id',$customer_id)->first();
        $sales_filter = Sales::where('id',$sales_id)->first();
        $sales_senior_filter = Sales::where('id',$sales_senior_id)->first();

        $data = [
            'sales' => $sales,
            'customer' => $customer,
            'invoice' => $final_array,
            'customer_filter' => $customer_filter,
            'sales_filter' => $sales_filter,
            'sales_senior_filter' => $sales_senior_filter
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

        $customer_id = $request->input('customer_id');
        $sales_senior_id = $request->input('sales_senior_id');
        $sales_id = $request->input('sales_id');
        $period_from = $request->input('period_from');
        $period_to = $request->input('period_to');

        $sales = Sales::get();
        $customer = Customer::get();

        $order = PackingOrder::whereHas('invoicing',function($query2) use($period_from,$period_to){
                                if($period_from){
                                    $query2->whereDate('created_at','>=',$period_from);
                                }
                                if($period_to){
                                    $query2->whereDate('created_at','<=',$period_to);
                                }
                            })
                            ->where(function($query2) use($customer_id){
                                if(!empty($customer_id)){
                                    $query2->where('customer_id',$customer_id);
                                }
                            })
                            ->get();       
        
        $final_array = [];
        foreach ($order as $key => $value) {
            $order_item = PackingOrderItem::where('do_id',$value->id)->get();
            foreach ($order_item as $k => $v) {
                $sales_order_item = SalesOrderItem::where('id',$v->so_item_id)->first();
                $sales_order = SalesOrder::where('id',$sales_order_item->so_id)->first();

                if(isset($final_array[$v->do_id])){
                    foreach ($final_array[$v->do_id]["sales_senior"] as $x => $y) {
                        if($y["id"] != $sales_order->sales_senior_id){
                            $final_array[$v->do_id]["sales_senior"][] = $sales_order->sales_senior->toArray() ?? null;
                        }
                    }

                    foreach ($final_array[$v->do_id]["sales"] as $x => $y) {
                        if($y["id"] != $sales_order->sales_id){
                            $final_array[$v->do_id]["sales"][] = $sales_order->sales->toArray() ?? null;
                        }
                    }
                }
                else{
                    $final_array[$v->do_id]["sales_senior"][] = $sales_order->sales_senior->toArray() ?? null;
                    $final_array[$v->do_id]["sales"][] = $sales_order->sales->toArray();
                    $final_array[$v->do_id]["do"] = $order[$key]->toArray();
                    $final_array[$v->do_id]["invoice"] = $value->invoicing->toArray() ?? null;
                    $final_array[$v->do_id]["payable"] = $value->invoicing->payable_detail()->get()->toArray() ?? null;    
                }

                
            }
        }


        $final_array_backup = $final_array;

        $final_array_sementara_senior = [];
        if(!empty($sales_senior_id)){
            foreach ($final_array as $key => $value) {
                foreach ($value["sales_senior"] as $k => $v) {
                    if($v["id"] == $sales_senior_id){
                        $final_array_sementara_senior[] = $final_array[$key];
                    }
                }
            }
            $final_array = $final_array_sementara_senior;
        }

        $final_array_sementara = [];
        if(!empty($sales_id)){
            foreach ($final_array as $key => $value) {
                foreach ($value["sales"] as $k => $v) {
                    if($v["id"] == $sales_id){
                        $final_array_sementara[] = $final_array[$key];
                    }
                }
            }
            $final_array = $final_array_sementara;
        }


        $final_array_sementara_final = [];
        if(!empty($sales_senior_id) && !empty($sales_id)){
            foreach ($final_array_backup as $key => $value) {
                $filter_do_id_sales_senior = "";
                foreach ($value["sales_senior"] as $k => $v) {
                    if($v["id"] == $sales_senior_id){
                        $filter_do_id_sales_senior = $value["do"]["id"];
                    }
                     
                }
                $filter_do_id_sales = "";
                foreach ($value["sales"] as $k => $v) {
                    if($v["id"] == $sales_id){
                        $filter_do_id_sales = $value["do"]["id"];
                    }
                }

                if($filter_do_id_sales == $filter_do_id_sales_senior){
                    $final_array_sementara_final[] = $final_array_backup[$key];
                }
            }
            $final_array = $final_array_sementara_final;
        }

        $customer_filter = Customer::where('id',$customer_id)->first();
        $sales_filter = Sales::where('id',$sales_id)->first();
        $sales_senior_filter = Sales::where('id',$sales_senior_id)->first();
        $company = Company::first();

        $data = [
            'sales' => $sales,
            'customer' => $customer,
            'invoice' => $final_array,
            'customer_filter' => $customer_filter,
            'sales_filter' => $sales_filter,
            'sales_senior_filter' => $sales_senior_filter,
            'company' => $company
        ];

        $pdf = PDF::loadview($this->view."print",$data)->setPaper('a4','potrait');
        return $pdf->stream('Sales Report');
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
