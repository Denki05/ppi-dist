<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Product;
use App\Entities\Master\BrandLokal;
use DB;
use COM;

class ApiCustomerController extends Controller
{
    public function getApiDataCustomer()
    {
        $results = Customer::all(); // Fetch all customer records
        return response()->json($results); // Return data as JSON
    }

    public function getApiDataProduct()
    {

        $results = Product::whereIn('brand_name', ['Senses', 'GCF'])            
            ->get();


        return response()->json($results); // Return data as JSON
    }

    public function getApiDataBrand()
    {

        $results = BrandLokal::whereIn('brand_name', ['Senses', 'GCF'])            
            ->get();


        return response()->json($results); // Return data as JSON
    }

    public function getApiMember()
    {
        $results = DB::table('provinsi')
            ->leftJoin('kabupaten', 'provinsi.prov_id', '=', 'kabupaten.prov_id')
            ->leftJoin('master_customer_other_addresses', 'kabupaten.city_id', '=', 'master_customer_other_addresses.kota')
            ->select(
                'provinsi.prov_name AS provinsi',
                'kabupaten.city_name AS kota',
                'master_customer_other_addresses.name',
                'master_customer_other_addresses.officer',
            )
            ->get();

        return response()->json($results); // Return data as JSON
    }

    // 🔹 Tambahan fungsi generate report
    public function generateReportApi(Request $request)
    {
        $officerSearch = $request->input('officerSearch') ?? $request->input('pic_id');
        $startDate     = $request->input('start_date') ?? $request->input('date_start');
        $endDate       = $request->input('end_date') ?? $request->input('date_end');

        // Lokasi file RPT
        $reportPath = public_path('cr/report/management/report_employee_performance/new/sales_performence_v2.rpt');
        $outputPath = public_path('cr/report/management/report_employee_performance/new/export/sales_performence_v2.pdf');

        $my_server = "LOCAL_3";
        $my_user = "root";
        $my_password = "";
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";

        try {
            // Inisialisasi Crystal Report
            $crApp = new COM($COM_Object) or die("Unable to Create Object");

            // Load report
            $report = $crApp->OpenReport($reportPath, 1);

            $report->ParameterFields(3)->SetCurrentValue($startDate);
            $report->ParameterFields(4)->SetCurrentValue($endDate);

            $formula = "{penjualan_so1.so_date}>=#$startDate# AND {penjualan_so1.so_date}<=#$endDate#";
           
            $report->RecordSelectionFormula = $formula;

            // Export ke PDF
            $report->ExportOptions->DiskFileName = $outputPath;
            $report->ExportOptions->FormatType   = 31; // PDF
            $report->ExportOptions->DestinationType = 1; // Disk
            $report->Export(false);

            return response()->file($outputPath);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getApiFileDoctor(Request $request)
    {
        $wilayah = $request->query('q');

        if (!$wilayah) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter pencarian (q) tidak ditemukan.'
            ], 400);
        }

        // Ambil data pelanggan existing
        $existingCustomers = DB::table('master_customers')
            ->join('master_customer_other_addresses', 'master_customers.id', '=', 'master_customer_other_addresses.customer_id')
            ->join('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
            ->where('master_customer_other_addresses.text_kota', 'like', "%{$wilayah}%")
            ->select(
                'master_customer_other_addresses.id',
                'master_customer_other_addresses.name as nama_customer_existing',
                'master_customer_categories.name as nama_kategori_existing',
                'master_customers.pic as pic_existing'
            )
            ->get();
        
        // Ambil data pelanggan prospek
        $prospekCustomers = DB::table('master_customers_prospek')
            ->join('master_customer_other_addresses_prospek', 'master_customers_prospek.id', '=', 'master_customer_other_addresses_prospek.customer_id')
            ->join('master_customer_categories', 'master_customers_prospek.category_id', '=', 'master_customer_categories.id')
            ->where('master_customer_other_addresses_prospek.text_kota', 'like', "%{$wilayah}%")
            ->select(
                'master_customer_other_addresses_prospek.id',
                'master_customer_other_addresses_prospek.name as nama_customer_prospek',
                'master_customer_categories.name as nama_kategori_prospek',
                'master_customers.pic as pic_prospek'
            )
            ->get();

        // Gabungkan kedua koleksi data ke dalam format yang seragam
        $allCustomers = $existingCustomers->map(function ($customer) {
            return [
                'id' => $customer->id, // Beri prefix agar unik
                'nama' => $customer->nama_customer_existing,
                'kategori' => $this->getCategoryName($customer->nama_kategori_existing),
                'pic' => $customer->pic_existing,
                'jenis' => 'EXISTING',
            ];
        })->merge($prospekCustomers->map(function ($customer) {
            return [
                'id' => $customer->id, // Beri prefix agar unik
                'nama' => $customer->nama_customer_prospek,
                'kategori' => $this->getCategoryName($customer->nama_kategori_prospek),
                'pic' => $customer->pic_prospek,
                'jenis' => 'PROSPEK',
            ];
        }));

        return response()->json([
            'success' => true,
            'customers' => $allCustomers->sortBy('nama')->values()
        ], 200);
    }
    
    /**
     * Mengonversi nama kategori dari DB ke nama yang diinginkan.
     *
     * @param string $dbCategory
     * @return string
     */
    private function getCategoryName($dbCategory)
    {
        $categoryMap = [
            'Agen - perfumery trusted' => 'BRAND',
            'Bigreseller' => 'BRAND',
            'Smreseller' => 'BRAND',
            'Bigperfumery (toko/multicabang)' => 'BRAND',
            'Smperfumery (toko/multicabang)' => 'BRAND',
            'Home industri kosmetik' => 'H. INDUSTRI',
            'Home industri pkrt' => 'H. INDUSTRI',
            'Industri kosmetik (PPN)' => 'INDUSTRI',
            'Industri pkrt (PPN)' => 'INDUSTRI',
        ];
        
        return $categoryMap[$dbCategory] ?? 'Lain-lain';
    }
}
