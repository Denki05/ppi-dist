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

        $top_sell_variant = ProductPack::leftJoin('penjualan_do_item', 'master_products_packaging.id', '=', 'penjualan_do_item.product_packaging_id')
                        ->leftJoin('penjualan_do', 'penjualan_do_item.do_id', '=', 'penjualan_do.id')
                        ->selectRaw('
                            master_products_packaging.id,
                            CONCAT(master_products_packaging.`code`, " - ", master_products_packaging.`name`) AS product,
                            SUM(penjualan_do_item.qty) AS total_qty,
                            penjualan_do.created_at AS tanggal_buat
                        ')
                        ->where('penjualan_do.status', 6)
                        ->whereMonth('penjualan_do.created_at', $selectedMonth) // Use selected month
                        ->whereYear('penjualan_do.created_at', now()->year)
                        ->groupBy('master_products_packaging.name')
                        ->orderBy('total_qty', 'DESC')
                        ->get();

        // Approval Sales Order Needs
        $approval_so = SalesOrder::leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                        ->selectRaw('
                            penjualan_so.id as id,
                            penjualan_so.so_code as so_code,
                            penjualan_so.so_date as so_date,
                            penjualan_so.brand_name as brand_name,
                            penjualan_so.approval_mou as approval_mou,
                            penjualan_so.approval_mou_status as approval_mou_status,
                            master_customer_other_addresses.name as customer_name,
                            master_customer_other_addresses.text_kota as customer_city
                        ')
                        ->whereIn('penjualan_so.status', [1, 2, 3, 4])
                        ->get();

        $data = [
            'sales' => $sales,
            'revenue' => $revenue,
            'top_sell_variant' => $top_sell_variant,
            'is_see' => $is_see,
            'selectedMonth' => $selectedMonth,
            'approval_so' => $approval_so,
        ];

    
        return view($this->view, $data);
    }
}