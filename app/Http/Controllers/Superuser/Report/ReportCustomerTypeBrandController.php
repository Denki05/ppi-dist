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
            DB::table('penjualan_do')
                ->leftJoin('penjualan_do_details', 'penjualan_do_details.do_id', '=', 'penjualan_do.id')
                ->leftJoin('penjualan_do_item', 'penjualan_do_item.do_id', '=', 'penjualan_do.id')
                ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
                ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
                ->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
                ->select(
                    DB::raw('SUM(penjualan_do_item.qty) AS invoice_qty'),
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
                    'penjualan_do_details.discount_idr AS discount_idr'
                )
                ->where('penjualan_do.status', 6)
                ->where(function ($query) {
                    $query->where('master_customers.status', 1)
                        ->orWhere('master_customers.existence', 1);
                })
                ->groupBy('penjualan_do.do_code')
                ->orderBy('penjualan_do.do_code')
                ->chunk(100, function ($results) {
                    foreach ($results as $row) {
                        // Define the attributes to find or create
                        $attributes = [
                            // 'customer_id' => $row->customerID,
                            // 'other_address_id' => $row->otherAddressID,
                            'invoice_code' => $row->invoice_code,
                        ];

                        // Define the values to be set or updated
                        $values = [
                            'customer_id' => $row->customerID,
                            'other_address_id' => $row->otherAddressID,
                            'customer_name' => $row->customer_name,
                            'customer_type' => $row->customer_type,
                            'customer_kota' => $row->customer_kota,
                            'customer_provinsi' => $row->customer_provinsi,
                            'customer_zone' => $row->customer_zone,
                            'invoice_date' => $row->invoice_date,
                            'invoice_brand' => $row->invoice_brand,
                            'invoice_type' => $row->invoice_type,
                            'invoice_qty' => $row->invoice_qty ?? 0,
                            'invoice_purchase' => ($row->invoice_purchase - $row->discount_idr ?? 0),
                            'invoice_delivery_order_cost' => $row->invoice_delivery_order_cost ?? 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        // Find the record or create a new one
                        $report = CustomerTypeBrandReports::firstOrNew($attributes);

                        // Set or update the additional values
                        $report->fill($values);

                        // Save the record to the database
                        $report->save();
                    }
                });

            return redirect()->back()->with('message', 'Berhasil Sync data!');
        } catch (\Exception $e) {
            Log::error('Sync data failed: ' . $e->getMessage());
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
        DB::table('report_customer_type_brand')->truncate();

        return redirect()->back()->with('message', 'Berhasil remove data!');
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->start;
        $endDate = $request->end;

        // Ambil daftar unik brand
        $brands = DB::table('report_customer_type_brand')
            ->distinct()
            ->pluck('invoice_brand')
            ->toArray();

        // Ambil data grouping berdasarkan kategori dan customer
        $data = DB::table('report_customer_type_brand')
            ->select(
                'customer_type', // Category
                'customer_name',
                'customer_kota',
                'invoice_brand',
                DB::raw('SUM(invoice_qty) as total_qty'),
                DB::raw('SUM(invoice_purchase) as total_purchase') // Menambahkan total_purchase
            )
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('customer_type', 'customer_name', 'invoice_brand')
            ->get();

        // Grouping data berdasarkan kategori dan customer
        $groupedData = [];
        foreach ($data as $row) {
            $category = $row->customer_type;
            $customerName = $row->customer_name;
            $customerKota = $row->customer_kota;
            $brand = $row->invoice_brand;
            $totalQty = $row->total_qty;
            $totalPurchase = $row->total_purchase; // Mengambil total_purchase

            if (!isset($groupedData[$category])) {
                $groupedData[$category] = [];
            }

            if (!isset($groupedData[$category][$customerName])) {
                $groupedData[$category][$customerName] = [
                    'name' => $customerName,
                    'kota' => $customerKota,
                    'brand_data' => array_fill_keys($brands, ['qty' => 0, 'purchase' => 0]), // Inisialisasi qty dan purchase semua brand ke 0
                ];
            }

            // Masukkan qty dan purchase ke dalam brand yang sesuai
            $groupedData[$category][$customerName]['brand_data'][$brand] = [
                'qty' => $totalQty,
                'purchase' => $totalPurchase
            ];
        }

        // Ekspor data ke Excel
        return Excel::download(new CustomerTypeBrandReportExport($groupedData, $brands), 'register_customer_type_brand.xlsx');   
    }
}