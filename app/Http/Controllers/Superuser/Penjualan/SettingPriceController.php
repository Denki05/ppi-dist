<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SettingPrice;
use App\Entities\Penjualan\SettingPriceLog;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\Exports\Penjualan\SettingPriceImportTemplate;
use App\Imports\Penjualan\SettingPriceImport;
use App\Entities\Master\Company;
use App\Entities\Master\BrandReference;
use App\Entities\Setting\UserMenu;
use Validator;
use Excel;
use PDF;
use DB;
use Auth;

class SettingPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.penjualan.price_setting.";
        $this->route = "superuser.penjualan.price_setting";
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
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $get_packaging = $request->input('id_packaging');
        $get_product = $request->input('id_product');

        $product = Product::get();
        $packaging = Packaging::get();
        $result = ProductPack::where(function($query2) use($get_product, $get_packaging){
                                if(!empty($get_product)){
                                    $query2->whereHas('product',function($query3) use($get_product){
                                        $query3->where('product_id', $get_product);
                                    });
                                }
                                if(!empty($get_packaging)){
                                    $query2->where(function($query3) use($get_packaging){
                                        $query3->where('packaging_id', $get_packaging); 
                                    });
                                }
                            })
                            ->get();

        $data = [
            'product' => $product,
            'packaging' => $packaging,
            'result' => $result,
        ];

        return view($this->view."index",$data);
    }

    public function history($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $decode = base64_decode($id);
        $result = ProductPack::where('id', $decode)->first();

        if(!$result){
            abort(404);
        }
        
        $data = [
            'result' => $result
        ];
        return view($this->view."log",$data);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $decode = base64_decode($id);

        $get = ProductPack::where('id', $decode)->first();

        if(empty($get)){
            abort(404);
        }

        $result = $get;

        $data = [
            'result' => $result
        ];

        return view($this->view."edit",$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try {
                DB::beginTransaction();
                if(empty($post["id"]) && !isset($post["id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "ID tidak boleh kosong";
                    goto ResultData;
                }
                if(empty($post["selling_price"]) && !isset($post["selling_price"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Harga jual tidak boleh kosong";
                    goto ResultData;
                }

                $get = ProductPack::where('id',$post["id"])->first();

                $data = [
                    'price' => trim(htmlentities($post["selling_price"])),
                    'updated_by' => Auth::id()
                ];

                 $update = ProductPack::where('id',$post["id"])->update($data);
                 $insert = SettingPriceLog::create([
                                            'product_packaging_id' => trim(htmlentities($post["id"])),
                                            'price' => $get->price,
                                        ]);
            
                  DB::commit();

                  $data_json["IsError"] = FALSE;
                  $data_json["Message"] = "Setting Price Berhasil Diubah";
                  goto ResultData;
             } catch (\Throwable $e) {

                 DB::rollback();
                 $data_json["IsError"] = TRUE;
                 $data_json["Message"] = $e->getMessage();
                 goto ResultData;
            }
              

            
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
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
    public function print_product(Request $request){

        ini_set('max_execution_time', 1200);
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $post = $request->all();
        $company = Company::first();
        $table = Product::where(function($query2) use($post){
                    if(!empty($post["checkbox"]) && count($post["checkbox"]) > 0 && is_array($post["checkbox"])){
                        $query2->whereIn('master_products.id',$post["checkbox"]);
                    }
                    if(!empty($post["id_product"])){
                        $query2->where('master_products.id',$post["id_product"]);
                    }
                    if(!empty($post["id_type"])){
                        $query2->where('master_products.type_id',$post["id_type"]);
                    }
                    if(!empty($post["id_category"])){
                        $query2->where('master_products.category_id',$post["id_category"]);
                    }
                })
                ->leftJoin('master_brand_references','master_products.brand_reference_id','=','master_brand_references.id')
                ->select('master_products.*')
                ->orderBy('master_brand_references.name','ASC')
                ->get();

        $brand_reference = $table->pluck('brand_reference_id')->unique();
        $brand_reference = BrandReference::whereIn('id',$brand_reference)
                                        ->orderBy('name','ASC')
                                        ->get();

        $header = null;

        if(!empty($post["id_category"])){
            $category = new ProductCategory;
            $category = $category->where('id',$post["id_category"]);
            $category = $category->first();
            if(!empty($category->image_header_list)){
                $header = base_path('public/'.$category->image_header_list);
            }
        }

        $data = [
            'table' => $table,
            'company' => $company,
            'brand_reference' => $brand_reference,
            'request' => $post,
            'header' => $header
        ];

        $pdf = PDF::loadview($this->view."print_product",$data)->setPaper('a4','potrait');
        return $pdf->stream();
    }
    public function print_product_price(Request $request){
        // Access
        ini_set('max_execution_time', 1200);
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $post = $request->all();
        $company = Company::first();
        $table = Product::where(function($query2) use($post){
                    if(!empty($post["checkbox"]) && count($post["checkbox"]) > 0 && is_array($post["checkbox"])){
                        $query2->whereIn('master_products.id',$post["checkbox"]);
                    }
                    if(!empty($post["id_product"])){
                        $query2->where('id',$post["id_product"]);
                    }
                    if(!empty($post["id_type"])){
                        $query2->where('type_id',$post["id_type"]);
                    }
                    if(!empty($post["id_category"])){
                        $query2->where('category_id',$post["id_category"]);
                    }
                })
                ->orderBy('name','ASC')
                ->get();
        $header = null;

        if(!empty($post["id_category"])){
            $category = new ProductCategory;
            $category = $category->where('id',$post["id_category"]);
            $category = $category->first();
            if(!empty($category->image_header_price)){
                $header = base_path('public/'.$category->image_header_price);
            }
        }
        $data = [
            'table' => $table,
            'company' => $company,
            'header' => $header
        ];

        $pdf = PDF::loadview($this->view."print_product_price",$data)->setPaper('a4','potrait');
        return $pdf->stream();
    }

    public function import_template()
    {
        $filename = 'setting-price-import-template.xlsx';
        return Excel::download(new SettingPriceImportTemplate, $filename);
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
            $import = new SettingPriceImport();
            Excel::import($import, $request->import_file);
        
            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }
}
