<?php

namespace App\Http\Controllers\Superuser\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Master\ProductFinance;
use App\Entities\Master\Mitra;
use App\Entities\Accounting\PriceLogFinance;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\Entities\Master\BrandLokal;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use App\Entities\Penjualan\PackingOrder;
use App\Exports\Finance\ProductFinanceExport;
use App\Imports\Accounting\ProductFinanceImport;
use App\Exports\Accounting\ProductFinanceImportTemplate;
use App\DataTables\Accounting\ProductFinanceTable;
use DB;
use Auth;
use PDF;
use Carbon\Carbon;
use Validator;
use Excel;

class ProductFinanceController extends Controller
{

    public function __construct(){
        $this->view = "superuser.accounting.product_finance.";
        $this->route = "superuser.accounting.product_finance";
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

    public function json(Request $request, ProductFinanceTable $datatable)
    {
        return $datatable->build($request);
    }

    
    public function index(Request $request)
    {   
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['mitra'] = Mitra::where('status', Mitra::STATUS['ACTIVE'])->get();

        return view($this->view."index", $data);
    }

    
    public function create(Request $request)
    {
        // Access control
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_create == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $mitra = Mitra::where('status', Mitra::STATUS['ACTIVE'])->get();
        $kemasan = Packaging::get();
        $brand = BrandLokal::get();

        
        $data = [
            'mitra' => $mitra,
            'kemasan' => $kemasan,
            'brand' => $brand,
        ];

        return view($this->view . "create", $data);
    }

   
    public function store(Request $request)
    {
        // dd($request->product);
        if ($request->ajax()) {
            DB::beginTransaction();

            try {
                // Validasi Input
                $validator = Validator::make($request->all(), [
                    'brand' => 'required|string',
                    'product' => 'required|string|exists:master_products_packaging,id',
                    'packaging_code' => 'required|string',
                    'mitra_id' => 'required|integer|exists:master_mitra,id',
                    'harga_beli_satuan' => 'required|numeric',
                    'harga_jual_satuan' => 'required|numeric',
                ]);

                // Jika validasi gagal, kirimkan response error
                if ($validator->fails()) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $validator->errors()->all(),
                    ];
      
                    return $this->response(400, $response);
                }

                // Ambil data produk berdasarkan ID yang dipilih dari dropdown
                $product_pack = ProductPack::where('id', $request->product)->first();
                if (!$product_pack) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $validator->errors()->all(),
                    ];
      
                    return $this->response(400, $response);
                }


                // Cek apakah master_product ada
                $master_product = ProductPack::where('id', $product_pack->id)->first();
                if (!$master_product) {
                    return response()->json([
                        'status' => 400,
                        'errors' => 'Master Product belum ada, silahkan input dahulu!'
                    ], 400);
                }

                // Cek apakah produk finance sudah ada
                $existing_product_finance = ProductFinance::where('id', $request->product)->first();
                if ($existing_product_finance) {
                    return response()->json([
                        'status' => 400,
                        'errors' => 'Product Finance sudah ada!'
                    ], 400);
                }

                // Simpan ke tabel ProductFinance
                $product_finance = new ProductFinance();
                $product_finance->id = $request->product;
                $product_finance->brand_name = $request->brand;
                $product_finance->code_product = $product_pack->code;
                $product_finance->name_product = $product_pack->name;
                $product_finance->product_id = $master_product->product_id;
                $product_finance->packaging_id = $request->packaging_code;
                $product_finance->mitra_id = $request->mitra_id;
                $product_finance->selling_price_usd_unit = $request->harga_jual_satuan;
                $product_finance->buying_price_usd_unit = $request->harga_beli_satuan;
                $product_finance->status = 1;
                $product_finance->save();

                // Commit transaksi
                DB::commit();

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.accounting.product_finance.index');

                return $this->response(200, $response);

            } catch (\Exception $e) {
                DB::rollback();

                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => "Internal Server Error",
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function show(Request $request)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function export()
    {
        $filename = 'master-product-finance' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new ProductFinanceExport, $filename);
    }

    public function import_template()
    {
        $filename = 'product-tax-import-template.xlsx';
        return Excel::download(new ProductFinanceImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new ProductFinanceImport();
            Excel::import($import, $request->import_file);
        
            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }

    public function get_product(Request $request)
    {
        $products = ProductPack::leftJoin('master_products', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->leftJoin('master_packaging', 'master_packaging.id', '=', 'master_products_packaging.packaging_id')
            ->select(
                'master_products_packaging.id', 
                'master_products_packaging.code', 
                'master_products_packaging.name', 
                'master_packaging.id as packaging_id',
                'master_packaging.pack_name as packaging_name'
            )
            ->where('master_products.brand_name', $request->brand_name)
            ->get(); 

        // Debugging
        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found for this brand'], 404);
        }

        return response()->json($products);
    }

    public function updatePrice(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:master_product_finance,id',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction(); // Mulai transaksi

        try {
            // Ambil data harga lama sebelum perubahan
            $old_price = ProductFinance::find($request->id);

            if (!$old_price) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Produk tidak ditemukan!'
                ], 404);
            }

            // Perbarui harga produk
            $product = ProductFinance::findOrFail($request->id);
            $product->buying_price_usd_unit = $request->buying_price;
            $product->selling_price_usd_unit = $request->selling_price;
            $product->save(); // Simpan perubahan harga

            // Simpan log harga sebelum perubahan
            $price_log = new PriceLogFinance;
            $price_log->product_finance_id = $product->id;
            $price_log->selling_price_usd_unit = $old_price->selling_price_usd_unit;
            $price_log->buying_price_usd_unit = $old_price->buying_price_usd_unit;
            $price_log->created_by = Auth::id();
            $price_log->save();

            DB::commit(); // Commit transaksi jika semua berhasil

            return response()->json([
                'status' => 200,
                'message' => 'Harga berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollback(); // Rollback transaksi jika ada error

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan saat memperbarui harga!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}