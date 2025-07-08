<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Reports\CustomerTypeBrandReports;
use App\Exports\Reports\CustomerTypeBrandReportExport;
Use App\Entities\Penjualan\SalesOrder;
use Maatwebsite\Excel\Facades\Excel;
use App\Entities\Setting\UserMenu;
use Carbon\Carbon;
use Exception;
use PDF;
use Auth;
use COM;
use DB;


class ReportCustomerTypeBrandController extends Controller
{
    public function __construct(){
        $this->view = "superuser.report.customer_type_brand.";
        $this->route = "superuser.report.customer_type_brand";
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
        // Check if the user is a superuser and has access
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access)) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Retrieve the data
        $data['data'] = CustomerTypeBrandReports::first();

        // Get the current year
        $currentYear = Carbon::now()->year;

        // Array with month names
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Prepare month options array
        $monthOptions = [];
        foreach ($months as $index => $month) {
            $monthOptions[] = [
                'value' => $index + 1, // month index (1 to 12)
                'label' => $month // Month name with year
            ];
        }

        // Pass month options to the view along with other data
        $data['monthOptions'] = $monthOptions;

        // Return the view with the data
        return view($this->view . "index", $data);
    }


    public function postData(Request $request)
    {
        try {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $query = DB::table('penjualan_do')
                // Subquery untuk jumlah qty dari penjualan_do_item
                ->leftJoin(DB::raw('(
                    SELECT do_id, SUM(qty) AS total_qty
                    FROM penjualan_do_item
                    GROUP BY do_id
                ) AS do_items'), 'do_items.do_id', '=', 'penjualan_do.id')

                ->leftJoin('penjualan_do_details', 'penjualan_do_details.do_id', '=', 'penjualan_do.id')
                ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
                ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
                ->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')

                ->select(
                    'do_items.total_qty AS invoice_qty',
                    'master_customers.id AS customerID',
                    'master_customer_other_addresses.id AS otherAddressID',
                    'master_customer_other_addresses.name AS customer_name',
                    'master_customer_categories.name AS customer_type',
                    'master_customer_other_addresses.text_kota AS customer_kota',
                    'master_customer_other_addresses.text_provinsi AS customer_provinsi',
                    'master_customer_other_addresses.zone AS customer_zone',
                    'penjualan_do.do_code AS invoice_code',
                    'penjualan_so.so_date AS invoice_date',
                    'penjualan_so.brand_name AS invoice_brand',
                    'penjualan_so.type_so AS invoice_type',
                    'penjualan_do_details.purchase_total_idr AS invoice_purchase',
                    'penjualan_do_details.delivery_cost_idr AS invoice_delivery_order_cost',
                    'penjualan_do_details.ppn_idr as invoice_ppn_idr',
                )

                ->where('penjualan_do.status', 6)
                ->whereMonth('penjualan_so.so_date', $currentMonth)
                ->whereYear('penjualan_so.so_date', $currentYear)
                ->where(function ($query) {
                    $query->where('master_customers.status', 1)
                        ->orWhere('master_customers.existence', 1);
                })
                ->groupBy(
                    'penjualan_do.do_code',
                    'master_customers.id',
                    'master_customer_other_addresses.id',
                    'master_customer_other_addresses.name',
                    'master_customer_categories.name',
                    'master_customer_other_addresses.text_kota',
                    'master_customer_other_addresses.text_provinsi',
                    'master_customer_other_addresses.zone',
                    'penjualan_so.so_date',
                    'penjualan_so.brand_name',
                    'penjualan_so.type_so',
                    'penjualan_do_details.purchase_total_idr',
                    'penjualan_do_details.delivery_cost_idr',
                    'do_items.total_qty',
                    'penjualan_do_details.ppn_idr',
                )
                ->orderBy('penjualan_do.do_code');

            // Eksekusi per-chunk untuk menghindari memory overload
            $query->chunk(100, function ($results) {
                foreach ($results as $row) {
                    $attributes = [
                        'invoice_code' => $row->invoice_code,
                    ];

                    $values = [
                        'customer_id'                   => $row->customerID,
                        'other_address_id'              => $row->otherAddressID,
                        'customer_name'                 => $row->customer_name,
                        'customer_type'                 => $row->customer_type,
                        'customer_kota'                 => $row->customer_kota,
                        'customer_provinsi'             => $row->customer_provinsi,
                        'customer_zone'                 => $row->customer_zone,
                        'invoice_date'                  => $row->invoice_date,
                        'invoice_brand'                 => $row->invoice_brand,
                        'invoice_type'                  => $row->invoice_type,
                        'invoice_qty'                   => $row->invoice_qty ?? 0,
                        'invoice_purchase'              => ($row->invoice_purchase ?? 0) - ($row->invoice_ppn_idr ?? 0),
                        'invoice_delivery_order_cost'   => $row->invoice_delivery_order_cost ?? 0,
                        'created_at'                    => now(),
                        'updated_at'                    => now(),
                    ];

                    // perhitungan ulang untuk pengecekan hasil valid purchase
                    // $penjualan_do = DB::table('penjualan_do')
                    //     ->where('do_code', $row->invoice_code)
                    //     ->first();

                    // if ($penjualan_do) {
                    //     $penjualan_do_details = DB::table('penjualan_do_details')
                    //         ->where('do_id', $penjualan_do->id)
                    //         ->first();

                    //     $penjualan_do_items = DB::table('penjualan_do_item')
                    //         ->where('do_id', $penjualan_do->id)
                    //         ->get();

                    //     if ($penjualan_do_details && $penjualan_do_items->isNotEmpty()) {
                    //         $subtotal_item = $penjualan_do_items->sum(function ($item) use ($penjualan_do) {
                    //             return (($item->price - $item->usd_disc) * $item->qty) * $penjualan_do->idr_rate;
                    //         });

                    //         $purchase_total = $subtotal_item
                    //             - ($penjualan_do_details->discount_1_idr ?? 0)
                    //             - ($penjualan_do_details->discount_2_idr ?? 0)
                    //             - ($penjualan_do_details->discount_idr ?? 0)
                    //             - ($penjualan_do_details->voucher_idr ?? 0)
                    //             - ($penjualan_do_details->ppn_idr ?? 0);

                    //         if (abs($values['invoice_purchase'] - $purchase_total) > 2) {
                    //             throw new Exception("Mismatch in purchase total for DO code: {$row->invoice_code}. Calculated: {$purchase_total}, Expected: {$values['invoice_purchase']}");
                    //         }
                    //     }
                    // }

                    // handle jika data sudah ada
                    $report = CustomerTypeBrandReports::firstOrNew($attributes);
                    $report->fill($values);
                    $report->save();
                }
            });
            return redirect()->back()->with('message', 'Berhasil Sync data!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function print_report(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        // Parsing tanggal dengan format yang lebih aman
        $startDate = Carbon::createFromFormat('Y-m-d', $validated['start'])->format('Y-m-d');
        $endDate = Carbon::createFromFormat('Y-m-d', $validated['end'])->format('Y-m-d');

        // Query data dari database
        $query = CustomerTypeBrandReports::selectRaw('
                customer_type, 
                customer_name, 
                customer_kota, 
                invoice_brand, 
                SUM(invoice_qty) as total_qty, 
                SUM(invoice_purchase) as total_purchase
            ')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('customer_type', 'customer_name', 'customer_kota', 'invoice_brand')
            ->orderBy('customer_type')
            ->orderBy('customer_name');

        // Eksekusi query utama
        $data = $query->get();

        // List brand yang digunakan untuk menghindari duplikasi kode
        $brands = ['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'];

        // Group data berdasarkan customer_type
        $groupedData = $data->groupBy('customer_type')->map(function ($items) use ($brands) {
            $totals = [];

            // Hitung total per kategori
            foreach ($brands as $brand) {
                $totals["total_{$brand}_qty"] = $items->where('invoice_brand', $brand)->sum('total_qty') ?: 0;
                $totals["total_{$brand}_purchase"] = $items->where('invoice_brand', $brand)->sum('total_purchase') ?: 0;
            }

            $totals['total_customer_qty'] = $items->sum('total_qty') ?: 0;
            $totals['total_customer_purchase'] = $items->sum('total_purchase') ?: 0;

            return ['items' => $items, 'totals' => $totals];
        });

        // Hitung total global dengan query yang sudah dikloning
        $globalTotals = [];
        foreach ($brands as $brand) {
            $globalTotals["total_{$brand}_qty"] = $data->where('invoice_brand', $brand)->sum('total_qty') ?: 0;
            $globalTotals["total_{$brand}_purchase"] = $data->where('invoice_brand', $brand)->sum('total_purchase') ?: 0;
        }

        $globalTotals['total_customer_qty'] = $data->sum('total_qty') ?: 0;
        $globalTotals['total_customer_purchase'] = $data->sum('total_purchase') ?: 0;

        // Data untuk dikirim ke PDF
        $pdfData = compact('groupedData', 'globalTotals', 'startDate', 'endDate');

        // Generate PDF
        $pdf = PDF::loadView('superuser.report.customer_type_brand.pdf_export', $pdfData)
                ->setPaper('a3', 'landscape');

        // Jika ingin di-download
        if ($request->has('download')) {
            return $pdf->download("Laporan-Customer-Type-Brand.pdf");
        }

        return $pdf->stream("Laporan-Customer-Type-Brand.pdf");
    }

    public function removeDt(Request $request)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        DB::table('report_customer_type_brand')
            ->whereMonth('invoice_date', $currentMonth)
            ->whereYear('invoice_date', $currentYear)
            ->delete();

        return redirect()->back()->with('message', 'Berhasil remove data!');
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->start;
        $endDate = $request->end;

        // Ambil daftar brand unik
        $brands = DB::table('report_customer_type_brand')
            ->distinct()
            ->pluck('invoice_brand')
            ->toArray();

        // Ambil data utama
        $data = DB::table('report_customer_type_brand')
            ->select(
                'customer_type',
                'customer_name',
                'customer_kota',
                'invoice_brand',
                DB::raw('SUM(invoice_qty) as total_qty'),
                DB::raw('SUM(invoice_purchase) as total_purchase')
            )
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('customer_type', 'customer_name', 'customer_kota', 'invoice_brand')
            ->get();

        // Inisialisasi struktur data
        $groupedData = [];
        $globalTotals = [];

        foreach ($brands as $brand) {
            $globalTotals["total_{$brand}_qty"] = 0;
            $globalTotals["total_{$brand}_purchase"] = 0;
        }
        $globalTotals['total_customer_qty'] = 0;
        $globalTotals['total_customer_purchase'] = 0;

        foreach ($data as $row) {
            $kategori = $row->customer_type;

            if (!isset($groupedData[$kategori])) {
                $groupedData[$kategori] = [
                    'items' => collect(),
                    'totals' => [],
                ];

                foreach ($brands as $brand) {
                    $groupedData[$kategori]['totals']["total_{$brand}_qty"] = 0;
                    $groupedData[$kategori]['totals']["total_{$brand}_purchase"] = 0;
                }
                $groupedData[$kategori]['totals']['total_customer_qty'] = 0;
                $groupedData[$kategori]['totals']['total_customer_purchase'] = 0;
            }

            // Tambahkan item ke kategori
            $groupedData[$kategori]['items']->push($row);

            $qty = $row->total_qty;
            $purchase = $row->total_purchase;
            $brand = $row->invoice_brand;

            // Tambahkan subtotal per kategori
            $groupedData[$kategori]['totals']["total_{$brand}_qty"] += $qty;
            $groupedData[$kategori]['totals']["total_{$brand}_purchase"] += $purchase;
            $groupedData[$kategori]['totals']['total_customer_qty'] += $qty;
            $groupedData[$kategori]['totals']['total_customer_purchase'] += $purchase;

            // Tambahkan ke total keseluruhan
            $globalTotals["total_{$brand}_qty"] += $qty;
            $globalTotals["total_{$brand}_purchase"] += $purchase;
            $globalTotals['total_customer_qty'] += $qty;
            $globalTotals['total_customer_purchase'] += $purchase;
        }

        return Excel::download(
            new CustomerTypeBrandReportExport($groupedData, $globalTotals, $brands),
            'laporan_customer_type_brand.xlsx'
        );
    }
}