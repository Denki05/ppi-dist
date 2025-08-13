<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\DB;
use Auth;
use COM;

class ReportEmployeePerformanceProductController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.employee_performance_product.";
        $this->route = "superuser.report.employee_performance_product";
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

    public function index() 
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['sales'] = DB::table('penjualan_do')->select('officer')->distinct()->get();

        return view($this->view."index", $data);
    }
}