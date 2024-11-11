<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\Report\DeliveryCostReportTable;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Vendor;
use App\Entities\Setting\UserMenu;
use DB;
use COM;
use Auth;

class ReportDeliveryCostController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.report_delivery_cost.";
        $this->route = "superuser.report.report_delivery_cost";
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

    public function json(Request $request, DeliveryCostReportTable $datatable)
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

        $data['customer'] = CustomerOtherAddress::get();
        $data['do'] = PackingOrder::get();

        return view($this->view. "index", $data);
    }
}
