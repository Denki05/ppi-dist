<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Reports\CustomerTypeBrandReports;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Vendor;
use App\Entities\Master\ProductPack;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
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

        $selectedMonthYear = $request->input('month_year');
        $selectedMonthYearFirst = null;

        if ($selectedMonthYear) {
            list($year, $month) = explode('-', $selectedMonthYear);
            $selectedYear = (int)$year;
            $selectedMonth = (int)$month;
            $selectedMonthYearFirst = Carbon::createFromDate($selectedYear, 1, 1)->format('Y-m');
        } else {
            $selectedYear = Carbon::now()->year;
            $selectedMonth = Carbon::now()->month;
            $selectedMonthYear = Carbon::now()->format('Y-m');
            $selectedMonthFirst = 1;
            $selectedMonthYearFirst = Carbon::createFromDate($selectedYear, $selectedMonthFirst, 1)->format('Y-m');
        }

        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth()->format('Y-m-d H:i:s');
        $endDate   = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth()->format('Y-m-d H:i:s');

        // ==========================================================
        // Sub-query 1: hitung ulang subtotal_item per DO dari item asli
        // ==========================================================
        $subtotalSub = DB::table('penjualan_do_item')
            ->join('penjualan_do', 'penjualan_do.id', '=', 'penjualan_do_item.do_id')
            ->select(
                'penjualan_do_item.do_id',
                DB::raw('SUM((penjualan_do_item.price - penjualan_do_item.usd_disc) * penjualan_do_item.qty) * MAX(penjualan_do.idr_rate) AS subtotal_item')
            )
            ->groupBy('penjualan_do_item.do_id');

        // ==========================================================
        // Sub-query 2: purchase_total per DO (subtotal - semua diskon & voucher, TANPA PPN & delivery)
        // Aman karena penjualan_do_details 1:1 dengan penjualan_do
        // ==========================================================
        $purchaseSub = DB::table('penjualan_do')
            ->joinSub($subtotalSub, 'calc', function ($join) {
                $join->on('calc.do_id', '=', 'penjualan_do.id');
            })
            ->leftJoin('penjualan_do_details', 'penjualan_do_details.do_id', '=', 'penjualan_do.id')
            ->select(
                'penjualan_do.id AS do_id',
                DB::raw('
                    (calc.subtotal_item
                        - IFNULL(penjualan_do_details.discount_1_idr, 0)
                        - IFNULL(penjualan_do_details.discount_2_idr, 0)
                        - IFNULL(penjualan_do_details.discount_idr, 0)
                        - IFNULL(penjualan_do_details.voucher_idr, 0)
                    ) AS purchase_total
                ')
            );

        // Query untuk data 'progress' (Omset) — sekarang dihitung ulang, tanpa PPN & delivery
        $progress = SalesOrder::leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('penjualan_do', 'penjualan_so.id', '=', 'penjualan_do.so_id')
            ->leftJoinSub($purchaseSub, 'purchase', function ($join) {
                $join->on('purchase.do_id', '=', 'penjualan_do.id');
            })
            ->select(
                'master_customer_other_addresses.name AS customer_name',
                'master_customer_other_addresses.text_kota AS customer_city',
                'penjualan_so.so_code AS so_code',
                'penjualan_so.so_date AS so_date',
                'penjualan_do.id AS id',
                'penjualan_do.do_code AS invoice_code',
                'penjualan_so.brand_name AS invoice_brand',
                'penjualan_so.type_so AS invoice_type',
                DB::raw('SUM(CASE WHEN penjualan_do.type_transaction = "CASH" THEN IFNULL(purchase.purchase_total, 0) ELSE 0 END) AS invoice_cash'),
                DB::raw('SUM(CASE WHEN penjualan_do.type_transaction IN ("TEMPO", "COD", "MARKETPLACE") THEN IFNULL(purchase.purchase_total, 0) ELSE 0 END) AS invoice_tempo')
            )
            ->where('penjualan_so.status', 4)
            ->where('penjualan_so.status', '!=', 7)
            ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
            ->groupBy('penjualan_do.id', 'master_customer_other_addresses.name', 'penjualan_do.do_code', 'penjualan_so.so_code', 'penjualan_so.so_date', 'penjualan_so.brand_name', 'penjualan_so.type_so')
            ->get();

        $vendor = Vendor::where('type', 2)->get();

        $data = [
            'is_see' => $is_see,
            'vendor' => $vendor,
            'progress' => $progress,
            'selectedMonthYear' => $selectedMonthYear,
            'selectedMonthYearFirst' => $selectedMonthYearFirst,
        ];

        return view($this->view, $data);
    }
}