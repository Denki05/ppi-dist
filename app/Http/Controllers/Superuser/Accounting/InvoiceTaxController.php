<?php

namespace App\Http\Controllers\Superuser\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Accounting\InvoiceTax;
use App\Entities\Accounting\InvoiceTaxDetail;
use App\Entities\Master\Mitra;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\ProductFinance;
use App\DataTables\Report\InvoiceTaxReportTable;
use App\DataTables\Report\InvoiceTaxJualReportTable;
use App\DataTables\Accounting\InvoiceTaxBeliTable;
use App\DataTables\Accounting\InvoiceTaxJualTable;
use App\Entities\Setting\UserMenu;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Validator;
use Carbon\Carbon;
use Auth;
use COM;
use DB;

class InvoiceTaxController extends Controller
{
    public function __construct(){
        $this->view = "superuser.accounting.invoice_tax.";
        $this->route = "superuser.accounting.invoice_tax";
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

    public function json(Request $request, InvoiceTaxReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function json2(Request $request, InvoiceTaxJualReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function json3(Request $request, InvoiceTaxBeliTable $datatable)
    {
        return $datatable->build($request);
    }

    public function json4(Request $request, InvoiceTaxJualTable $datatable)
    {
        return $datatable->build($request);
    }

    public function search_invreal_jual(Request $request)
    {
        $monthInvoice = $request->input('month_invoice'); // Get the month_invoice value

        $invoice = PackingOrder::leftJoin('master_customer_other_addresses', 'master_customer_other_addresses.id', '=', 'penjualan_do.customer_other_address_id')
            ->where(function ($query) use ($monthInvoice) {
                // Add a nested condition for customer filtering
                $query->whereNull('penjualan_do.customer_other_address_id');

                if ($monthInvoice) {
                    $query->orWhereMonth('penjualan_do.created_at', $monthInvoice);
                }
            })
            ->where('penjualan_do.status', 6)
            // ->where('penjualan_do.cashback_status', 1)
            ->where('penjualan_do.tax_jual', 0)
            ->where('penjualan_do.tax_beli', 1)
            ->where('penjualan_do.do_code', 'LIKE', $request->input('q', '') . '%')
            ->select(
                'penjualan_do.id AS id',
                'penjualan_do.do_code AS invoiceReal',
                'penjualan_do.tax_jual AS taxJual',
                'penjualan_do.tax_beli AS taxBeli',
                'penjualan_do.cashback_status AS cashbackStatus',
                'master_customer_other_addresses.name AS customerName',
                'master_customer_other_addresses.text_kota AS customerCity'
            )
            ->get();

        $results = [];

        foreach ($invoice as $item) {
            if($item->taxJual == 0){
                $results[] = [
                    'id' => $item->id,
                    'text' => $item->invoiceReal . ' - ' . $item->customerName . '  '. $item->customerCity,
                ];
            }
        }

        return ['results' => $results];
    } 

    public function search_invreal_beli(Request $request)
    {
        $monthInvoice = $request->input('month_invoice'); // Get the month_invoice value

        $invoice = PackingOrder::leftJoin('master_customer_other_addresses', 'master_customer_other_addresses.id', '=', 'penjualan_do.customer_other_address_id')
            ->where(function ($query) use ($monthInvoice) {
                // Add a nested condition for customer filtering
                $query->whereNull('penjualan_do.customer_other_address_id');

                if ($monthInvoice) {
                    $query->orWhereMonth('penjualan_do.created_at', $monthInvoice);
                }
            })
            ->where('penjualan_do.status', 6)
            ->where('penjualan_do.cashback_status', 1)
            ->where('penjualan_do.tax_beli', 0)
            ->where('penjualan_do.do_code', 'LIKE', $request->input('q', '') . '%')
            ->select(
                'penjualan_do.id AS id',
                'penjualan_do.do_code AS invoiceReal',
                'penjualan_do.cashback_status AS cashbackStatus',
                'master_customer_other_addresses.name AS customerName',
                'master_customer_other_addresses.text_kota AS customerCity'
            )
            ->get();

        $results = [];

        foreach ($invoice as $item) {
            if($item->cashbackStatus == 1){
                $results[] = [
                    'id' => $item->id,
                    'text' => $item->invoiceReal . ' - ' . $item->customerName . '  '. $item->customerCity,
                ];
            }
        }

        return ['results' => $results];
    } 

    public function index_jual(Request $request)
    {
        // Access
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Define the range of years for filtering
        $currentYear = now()->year;
        $years = range($currentYear, $currentYear - 10); // Generate a range of 10 years back

        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->endOfYear();
        $invoice_tax = InvoiceTax::get();

        $months = [];
        for ($date = $start; $date <= $end; $date->addMonth()) {
            $months[] = [
                'id' => $date->format('n'),
                'monthName' => $date->format('F'),
            ];
        }

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Get the selected month, default to the current month
        $selectedBulan = $request->bulan ?? now()->month;
        $selectedTahun = $request->tahun ?? $currentYear;

        $data = [
            'invoice_tax' => $invoice_tax,
            'months' => $months,
            'bulan' => $bulan,
            'selectedBulan' => $selectedBulan,
            'selectedTahun' => $selectedTahun,
        ];

        return view($this->view . "index_jual", $data);
    }

    public function index_beli(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Define the range of years for filtering
        $currentYear = now()->year;
        $years = range($currentYear, $currentYear - 10); // Generate a range of 10 years back

        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->endOfYear();
        $invoice_tax = InvoiceTax::get();

        $months = [];
        for ($date = $start; $date <= $end; $date->addMonth()) {
            $months[] = [
                'id' => $date->format('n'),
                'monthName' => $date->format('F'),
            ];
        }

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Get the selected month, default to the current month
        $selectedBulan = $request->bulan ?? now()->month;
        $selectedTahun = $request->tahun ?? $currentYear;

        $data = [
            'invoice_tax' => $invoice_tax,
            'months' => $months,
            'bulan' => $bulan,
            'selectedBulan' => $selectedBulan,
            'selectedTahun' => $selectedTahun,
        ];

        return view($this->view."index_beli", $data);
    }

    public function getLastCode()
    {
        // Ambil 5 kode terakhir dari database, urutkan dari yang terbaru
        $lastCodes = InvoiceTax::orderBy('id', 'desc')->take(5)->pluck('code');

        return response()->json(['lastCodes' => $lastCodes]);
    }

    public function create(Request $request)
    {
        // Access check for superuser
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_create == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Retrieve the selected invoice ID from the request
        $id = $request->input('addInvoice');
        $type = $request->input('type_invoice');

        // Optional: Check if the invoice ID is not empty
        if (empty($id)) {
            return redirect()->back()->with('error', 'Invoice ID is required');
        }

        $mitra = Mitra::where('status', 1)->get();
        $invoice = PackingOrder::find($id);
        $type = $type;

        // Fetch products associated with the packing order
        $products = [];
        if ($invoice) {
            $mpfinance = ProductFinance::get();
            foreach ($invoice->do_detail as $value) {
                foreach ($mpfinance as $item) {
                    if ($item->id === $value->product_packaging_id) {
                        $products[] = [
                            'id' => $item->id,
                            'name' => $item->name_product,
                            'code' => $item->code_product,
                            'kemasan' => $item->packaging->pack_name,
                            'qty' => $value->qty,
                            'selling_price_usd_drum' => $item->selling_price_usd_drum,
                            'buying_price_usd_drum' => $item->buying_price_usd_drum,
                            'selling_price_usd_unit' => $item->selling_price_usd_unit,
                            'buying_price_usd_unit' => $item->buying_price_usd_unit,
                            'free' => $value->so_item->free_product,
                        ];
                    }
                }
            }
        }

        $data = [
            'mitra' => $mitra,
            'invoice' => $invoice,
            'type' => $type,
            'products' => $products,
        ];
        
        return view($this->view . "create", $data);
    }


    /**
     * Fungsi untuk membuat kode baru berdasarkan kode terakhir
     */
    private function generateNewCode($lastCode)
    {
        $prefix = 'TP'; // Sesuaikan dengan prefix
        preg_match('/-(\d+)$/', $lastCode, $matches);
        $suffix = isset($matches[1]) ? (int)$matches[1] + 1 : 1;

        return $prefix . '-' . $suffix;
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'products' => 'required|array',
                'products.*.id' => 'required',
                'products.*.qty' => 'required',
                'products.*.price' => 'required|numeric|min:0.01', // Ensures price is greater than 0
                'products.*.total' => 'required|numeric|min:0',
                'code' => 'required',
                'delivery' => 'required|integer',
                'type' => 'required|integer',
                'mitra' => 'required|integer',
                'date' => 'required|date',
                'subtotal' => 'required|numeric|min:0',
                'ppn_percent' => 'required|numeric|min:0',
                'ppn_idr' => 'required|numeric|min:0',
                'grand_total' => 'required|numeric|min:0',
            ]);

            // Proceed with business logic only if validation passes
            $invoiceTax = InvoiceTax::create([
                'code' => $validatedData['code'],
                'do_id' => $validatedData['delivery'],
                'mitra_id' => $validatedData['mitra'],
                'type' => $validatedData['type'] == 0 ? 1 : 2, // Remove the extra semicolon here
                'date' => $validatedData['date'],
                // 'note' => $validatedData['note'],
                'ppn_percent' => $validatedData['ppn_percent'],
                'ppn_idr' => $validatedData['ppn_idr'],
                'sub_total' => $validatedData['subtotal'],
                'grand_total' => $validatedData['grand_total'],
                'created_by' => Auth::id(),
                'status' => 1,
            ]);

            foreach ($validatedData['products'] as $productData) {
                InvoiceTaxDetail::create([
                    'invoice_tax_id' => $invoiceTax->id,
                    'product_finance_id' => $productData['id'],
                    'qty' => $productData['qty'],
                    'price' => $productData['price'],
                    'sub_total' => $productData['total'],
                ]);
            }

            if ($validatedData['type'] == 0) {
                PackingOrder::where('id', $validatedData['delivery'])->update(['tax_jual' => 1]);
            } elseif ($validatedData['type'] == 1) {
                PackingOrder::where('id', $validatedData['delivery'])->update(['tax_beli' => 1]);
            }

            // Respon After Save
            if ($validatedData['type'] == 0) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('superuser.accounting.invoice_tax.index_jual')
                ]);
            } elseif ($validatedData['type'] == 1) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('superuser.accounting.invoice_tax.index_beli')
                ]);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }


    public function destroy($id)
    {
        
    }

    public function destroy_beli($id)
    {
        $invoiceTax = InvoiceTax::findOrFail($id);

        try {
            // Update status to 'deleted'
            $invoiceTax->status = InvoiceTax::STATUS['DELETED'];

            if ($invoiceTax->save()) {
                // Soft delete the invoice tax record
                $invoiceTax->delete();
                PackingOrder::where('id', $invoiceTax->do_id)->update(['tax_beli' => 0]);
            }

            return redirect()->route('superuser.accounting.invoice_tax.index_beli')->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Delete Error: ' . $e->getMessage());
            return redirect()->route('superuser.accounting.invoice_tax.index_beli')->with('error', 'Failed to delete Invoice.');
        }
    }

    public function destroy_jual($id)
    {
        $invoiceTax = InvoiceTax::findOrFail($id);

        try {
            // Update status to 'deleted'
            $invoiceTax->status = InvoiceTax::STATUS['DELETED'];

            if ($invoiceTax->save()) {
                // Soft delete the invoice tax record
                $invoiceTax->delete();
                PackingOrder::where('id', $invoiceTax->do_id)->update(['tax_jual' => 0]);
            }

            return redirect()->route('superuser.accounting.invoice_tax.index_jual')->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Delete Error: ' . $e->getMessage());
            return redirect()->route('superuser.accounting.invoice_tax.index_jual')->with('error', 'Failed to delete Invoice.');
        }
    }

    public function print_invoice($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = InvoiceTax::where('id',$id)->first();

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\invoice\\invoice_tax.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\invoice\\export\\'.$result->code.'-UNIFRA'.'.pdf';
       
        //- Variables - Server Information 
        $my_server = "LOCAL"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi_araya";
        $COM_Object = "CrystalDesignRunTime.Application";


        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{finance_invoice_mitra.id}= $result->id";


        //export to PDF process
        $creport->ExportOptions->DiskFileName=$my_pdf; //export to pdf
        $creport->ExportOptions->PDFExportAllPages=true;
        $creport->ExportOptions->DestinationType=1; // export to file
        $creport->ExportOptions->FormatType=31; // PDF type
        $creport->Export(false);

        //------ Release the variables ------
        $creport = null;
        $crapp = null;
        $ObjectFactory = null;

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\invoice\\export\\'.$result->code.'-UNIFRA'.'.pdf';

        if (file_exists($file)) {
            // Set headers for file download
            header("Content-Description: File Transfer");
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
            header("Content-Transfer-Encoding: binary");
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Pragma: public");
            header("Content-Length: " . filesize($file));

            // Clear the output buffer and read the file
            ob_clean();
            flush();
            readfile($file);

            // Delete the file after download
            unlink($file);

            exit();
        } else {
            return redirect()->route('superuser.index')->with('error', 'File not found.');
        }
    }

    public function pageReportBeli(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $customer = CustomerOtherAddress::get();
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Get the selected month, default to the current month
        $selectedBulan = $request->bulan ?? now()->month;

        $data = [
            'customer' => $customer,
            'bulan' => $bulan,
            'selectedBulan' => $selectedBulan
        ];
        
        return view($this->view."report_beli",$data);
    }

    public function pageReportJual(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $customer = CustomerOtherAddress::get();
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Get the selected month, default to the current month
        $selectedBulan = $request->bulan ?? now()->month;

        $data = [
            'customer' => $customer,
            'bulan' => $bulan,
            'selectedBulan' => $selectedBulan
        ];
        
        return view($this->view."report_jual",$data);
    }
}