<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SalesOrder;
use App\DataTables\Report\SalesmanReportTable;
use App\Entities\Setting\UserMenu;
use Auth;
use PDF;
use Validator;

class ReportSalesmanController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.salesman.";
        $this->route = "superuser.report.salesman";
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

    public function json(Request $request, SalesmanReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // $salesman = SalesOrder::SALES_REPORT;

        // $data = [
        //     'salesman' => $salesman,
        // ];

        return view($this->view."index");
    }


}