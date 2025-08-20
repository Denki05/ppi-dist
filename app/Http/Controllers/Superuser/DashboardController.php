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

        $selectedMonthYear = $request->input('month_year'); // Ini akan menjadi 'YYYY-MM'
        $selectedMonthYearFirst = null; // Deklarasi variabel baru dan inisialisasi

        if ($selectedMonthYear) {
            list($year, $month) = explode('-', $selectedMonthYear);
            $selectedYear = (int)$year;
            $selectedMonth = (int)$month;

            $selectedMonthYearFirst = Carbon::createFromDate($selectedYear, 1, 1)->format('Y-m');

        } else {
            $selectedYear = Carbon::now()->year;
            $selectedMonth = Carbon::now()->month; // Tetap bulan saat ini untuk selectedMonthYear
            $selectedMonthYear = Carbon::now()->format('Y-m'); // Tetap format bulan saat ini

            // 👇 Bagian untuk selectedMonthYearFirst saja 👇
            $selectedMonthFirst = 1; // Selalu Januari
            $selectedMonthYearFirst = Carbon::createFromDate($selectedYear, $selectedMonthFirst, 1)->format('Y-m');
            // 👆 Bagian untuk selectedMonthYearFirst saja 👆
        }

        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth()->format('Y-m-d H:i:s');
        $endDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth()->format('Y-m-d H:i:s');

        // Query untuk data 'progress' (Omset)
        $progress = SalesOrder::leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('penjualan_do', 'penjualan_so.id', '=', 'penjualan_do.so_id')
            ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
            ->select(
                'master_customer_other_addresses.name AS customer_name',
                'master_customer_other_addresses.text_kota AS customer_city',
                'penjualan_so.so_code AS so_code',
                'penjualan_so.so_date AS so_date', // Pastikan kolom tanggal ini digunakan untuk filter
                'penjualan_do.id AS id',
                'penjualan_do.do_code AS invoice_code',
                'penjualan_so.brand_name AS invoice_brand',
                'penjualan_so.type_so AS invoice_type',
                DB::raw('SUM(CASE WHEN penjualan_do.type_transaction = "CASH" THEN IFNULL(penjualan_do_details.grand_total_idr - penjualan_do_details.delivery_cost_idr , 0) END) AS invoice_cash'),
                DB::raw('SUM(CASE WHEN penjualan_do.type_transaction IN ("TEMPO", "COD", "MARKETPLACE") THEN IFNULL(penjualan_do_details.grand_total_idr - penjualan_do_details.delivery_cost_idr, 0) END) AS invoice_tempo')
            )
            ->where('penjualan_so.status', 4)
            ->where('penjualan_so.status', '!=', 7)
            ->whereBetween('penjualan_so.so_date', [$startDate, $endDate]) // Filter berdasarkan tanggal awal dan akhir bulan
            ->groupBy('penjualan_do.id', 'master_customer_other_addresses.name', 'penjualan_do.do_code', 'penjualan_so.so_code', 'penjualan_so.so_date', 'penjualan_so.brand_name', 'penjualan_so.type_so') // Tambahkan kolom non-aggregate ke GROUP BY
            ->get();

        $vendor = Vendor::where('type', 2)->get();

        $data = [
            'is_see' => $is_see,
            'vendor' => $vendor,
            'progress' => $progress,
            'selectedMonthYear' => $selectedMonthYear,
            'selectedMonthYearFirst' => $selectedMonthYearFirst, // Kirimkan variabel baru ini ke view
        ];

        return view($this->view, $data);
    }
}