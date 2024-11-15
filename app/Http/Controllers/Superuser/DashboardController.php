<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Reports\CustomerTypeBrandReports;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\ProductPack;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\Session;
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
    
    public function index(Request $request) 
    {
        $is_see = true;
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access)) {
                $is_see = false;
            }
        }
    
        $sales = SalesOrder::leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
                    ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
                    ->leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
                    ->selectRaw('
                        master_products.brand_name as brand,
                        DATE_FORMAT(penjualan_so.so_date, "%M") as month_name, 
                        DATE_FORMAT(penjualan_so.so_date, "%Y") as year,
                        SUM(penjualan_so_item.qty_worked) as total_qty
                    ')
                    ->whereYear('penjualan_so.so_date', date('Y'))
                    ->where('penjualan_so.status', 4)
                    ->groupBy('brand', 'month_name', 'year') // Grouping by brand, month, and year
                    ->orderByRaw('MONTH(penjualan_so.so_date)')
                    ->get();
        
        $revenue = CustomerTypeBrandReports::selectRaw('
                        id as id, 
                        DATE_FORMAT(invoice_date, "%M") as month_name, 
                        DATE_FORMAT(invoice_date, "%Y") as year,
                        SUM(invoice_purchase) as total_purchase
                    ')
                    ->whereYear('invoice_date', date('Y'))
                    ->groupBy('month_name', 'year') // Grouping by month and year
                    ->orderByRaw('MONTH(invoice_date)')
                    ->get();

        $selectedMonth = request('month', now()->month); // Default to current month if no month is selected

        $top_sell_variant = ProductPack::leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                                    ->leftJoin('penjualan_do_item', 'master_products_packaging.id', '=', 'penjualan_do_item.product_packaging_id')
                                    ->leftJoin('penjualan_do', 'penjualan_do_item.do_id', '=', 'penjualan_do.id')
                                    ->selectRaw('
                                        master_products_packaging.id,
                                        CONCAT(master_products_packaging.`code`, " - ", master_products_packaging.`name`) AS product,
                                        master_packaging.pack_name AS kemasan,
                                        SUM(penjualan_do_item.qty) AS total_qty,
                                        penjualan_do.created_at AS tanggal_buat
                                    ')
                                    ->where('penjualan_do.status', 6)
                                    ->whereMonth('penjualan_do.created_at', $selectedMonth) // Use selected month
                                    ->whereYear('penjualan_do.created_at', now()->year)
                                    ->groupBy('master_products_packaging.name')
                                    ->orderBy('total_qty', 'DESC')
                                    ->get();

        $data = [
            'sales' => $sales,
            'revenue' => $revenue,
            'top_sell_variant' => $top_sell_variant,
			'is_see' => $is_see,
            'selectedMonth' => $selectedMonth
        ];

    
        return view($this->view, $data);
    }
}
