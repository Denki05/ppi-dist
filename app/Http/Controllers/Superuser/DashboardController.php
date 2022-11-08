<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Finance\Invoicing;
use App\Entities\Master\Customer;
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

		$label         = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
        for($bulan=1;$bulan < 13;$bulan++){
			$chartsales     = collect(DB::SELECT("SELECT count(id) AS jumlah from penjualan_so where month(created_at)='$bulan' AND penjualan_so.created_at BETWEEN '2022-01-01' AND '2022-12-31'"))->first();
			$chartpay     = collect(DB::SELECT("SELECT sum(prev_account_receivable) AS jumlah from finance_payable_detail where month(created_at)='$bulan'"))->first();
        $jumlah_so[] = $chartsales->jumlah;
        $jumlah_pay[] = $chartpay->jumlah;
        }

		$search = $request->input('search');
    	$customer_id = $request->input('customer_id');
    	$province = $request->input('province');
    	$invoice = Invoicing::where(function($query2) use($search){
    							if(!empty($search)){
    								$query2->where('code','like','%'.$search.'%');
    								$query2->orWhere(function($query3) use($search){
    									$query3->whereHas('do',function($query4) use($search){
    										$query4->whereHas('customer',function($query5) use($search){
    											$query5->where('name','like','%'.$search.'%');
    										});
    									});
    								});
    							}
    						})
    						->where(function($query2) use($customer_id,$province){
    							if(!empty($customer_id)){
    								$query2->whereHas('do',function($query3) use($customer_id){
    									$query3->where('customer_id',$customer_id);
    								});
    							}
    							if(!empty($province)){
    								$query2->where(function($query3) use($province){
    									$query3->whereHas('do',function($query4) use($province){
    										$query4->whereHas('customer',function($query5) use($province){
    											$query5->where('province',$province);
    										});
    									});
    								});
    							}
    						})
    						->orderBy('id','ASC')
    						->get();
    	$customer = Customer::get();

    	$data =[
            'invoice' => $invoice,
            'customer' => $customer,
            'is_see' => $is_see,
			'label' => $label,
			'jumlah_so' => $jumlah_so,
			'jumlah_pay' => $jumlah_pay,
        ];
        return view($this->view,$data);
    }
}
