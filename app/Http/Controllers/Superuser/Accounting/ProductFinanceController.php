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

    
    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view($this->view."index");
    }

    
    public function create(Request $request)
    {
        // Access control
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_create == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Get mitra id
        $mitra_id = $request->query('mitra_id');

        $mitra = Mitra::where('id', $mitra_id)->first();
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
        // Check if the request is an AJAX request
        if ($request->ajax()) {

            DB::beginTransaction();

            try {
                // Validation
                $validator = Validator::make($request->all(), [
                    'brand' => 'required|string',
                    'kode_produk' => 'required|string|max:50',
                    'nama_produk' => 'required|string|max:100',
                    'kemasan' => 'required|integer|exists:master_packaging,id',
                    'mitra_id' => 'required|integer|exists:master_mitra,id',
                    'harga_beli_satuan' => 'required|numeric',
                    'harga_jual_satuan' => 'required|numeric',
                ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 400,
                        'errors' => $validator->errors()
                    ], 400);
                }

                // Initialize $product_finance instance
                $product_finance = new ProductFinance();

                // Handle 'Senses' brand logic
                if ($request->brand == "Senses") {
                    $code = explode(" ", $request->kode_produk);
                    $product_finance->id = $code[1] . '-' . $request->kemasan;
                    $product_finance->code_product = $code[1];
                } else {
                    $product_finance->id = $request->kode_produk . '-' . $request->kemasan;
                    $product_finance->code_product = $request->kode_produk; // This should be defined or clarified
                }

                // Set common product finance attributes
                $product_finance->name_product = $request->nama_produk;
                $product_finance->mitra_id = $request->mitra_id;

                // Check if product exists in master product
                $master_product = ProductPack::where('id', $product_finance->id)->first();
                if ($master_product == null) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => "Master Product belum ada, Silahkan input dahulu!",
                    ];

                    return $this->response(400, $response);
                }

                // check existing product finance
                $check_product_finance = ProductFinance::where('id', $product_finance->id)->first();
                if ($check_product_finance !== null) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => "Product Sudah ada!",
                    ];

                    return $this->response(400, $response);
                }

                // Set remaining fields and save
                $product_finance->product_id = $master_product->product_id;
                $product_finance->packaging_id = $request->kemasan;
                $product_finance->selling_price_usd_unit = $request->harga_jual_satuan;
                $product_finance->buying_price_usd_unit = $request->harga_beli_satuan;
                $product_finance->save();

                // Commit transaction
                DB::commit();

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.accounting.product_finance.show', $request->mitra_id);

                return $this->response(200, $response);

            } catch (\Exception $e) {
                dd($e);
                // Rollback transaction on error
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
    
    public function show(Request $request, $mitra_id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        
        $search_product = $request->input('product');
        // DD($search_product);

        $mitra = Mitra::find($mitra_id);
        $product = ProductFinance::where('mitra_id', $mitra_id)->get();

        $table_data = ProductFinance::where(function($query2) use($search_product) {
                                            if(!empty($search_product)){
                                                $query2->where('id', $search_product);
                                            }
                                        })
                                        ->first();
                                
        $data = [
            'mitra' => $mitra,
            'product' => $product,
            'table_data' => $table_data,
        ];

        return view($this->view."show",$data);
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
        $filename = 'product-tax-' . date('d-m-Y_H-i-s') . '.xlsx';
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

        // DD($request->mitra_id);
        
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

    public function update_cost(Request $request , $product_finance)
    {
        if ($request->ajax()) {

            $decode = base64_decode($product_finance);
            $productFinance = ProductFinance::find($decode);

            // get old price
            $old_price = ProductFinance::where('id', $decode)->first();

            if ($productFinance == null) {
                abort(404);
            }

            // DD($productFinance->id);

            $validator = Validator::make($request->all(), [
                'buying_price_usd_drum' => 'required',
                'selling_price_usd_drum' => 'required',
                'buying_price_usd_unit' => 'required',
                'selling_price_usd_unit' => 'required',
                
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()]);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                $productFinance->selling_price_usd_drum = $request->selling_price_usd_drum;
                $productFinance->buying_price_usd_drum = $request->buying_price_usd_drum;
                $productFinance->selling_price_usd_unit = $request->selling_price_usd_unit;
                $productFinance->buying_price_usd_unit = $request->buying_price_usd_unit;
                $productFinance->updated_by = Auth::id();

                if ($productFinance->save()) {
                    // Insert log
                    $price_log = new PriceLogFinance;

                    $price_log->product_finance_id = $productFinance->id;
                    $price_log->selling_price_usd_drum = $old_price->selling_price_usd_drum;
                    $price_log->buying_price_usd_drum = $old_price->buying_price_usd_drum;
                    $price_log->selling_price_usd_unit = $old_price->selling_price_usd_unit;
                    $price_log->buying_price_usd_unit = $old_price->buying_price_usd_unit;
                    $price_log->created_by = Auth::id();

                    $price_log->save();

                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.accounting.product_finance.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function search_mitra(Request $request)
    {
        $mitra = Mitra::where('status', Mitra::STATUS['ACTIVE'])
                        ->get();

        $results = [];

        foreach ($mitra as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->name,
                ];
        }
                
        return ['results' => $results];
    }
}
