<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Finance\Invoicing;
use App\Entities\Master\Customer;
use App\Entities\Master\Product;
use App\Entities\Setting\UserMenu;
use Auth;
use DB;

class DashboardController extends Controller
{
	public function __construct(){
		$this->view = "superuser.dashboard";
        $this->route = "superuser.dashboard.index";
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
    public function index(Request $request) {
        $is_see = true;
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                $is_see == false;
            }
        }

    	$customer = Customer::get();
    	$product = Product::get();
    	$invoice = Invoicing::get();

    	$data =[
            'customer' => $customer,
            'product' => $product,
            'invoice' => $invoice,
            'is_see' => $is_see,
        ];
        return view($this->view,$data);
    }
}
