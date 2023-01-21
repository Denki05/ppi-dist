<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\ProductTable;
use App\Entities\Master\Product;
use App\Entities\Master\ProductCategory;
// use App\Entities\Master\ProductType;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Fragrantica;
use App\Exports\Master\ProductExport;
use App\Exports\Master\ProductImportTemplate;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use App\Imports\Master\ProductImport;
use App\Repositories\MasterRepo;
use DB;
use Excel;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;
use PDF;

class ProductController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.product";
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
    public function json(Request $request, ProductTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view('superuser.master.product.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['brand_ppi'] = BrandLokal::get();
        $data['category'] = ProductCategory::get();
        $data['sub_brand_references'] = MasterRepo::sub_brand_references();
        $data['units'] = MasterRepo::units();
        $data['warehouses'] = MasterRepo::warehouses();
        $data['product_notes'] = Product::NOTE;
        $data['fragrantica'] = Fragrantica::all();

        // dd($data['brand_ppi']);
        return view('superuser.master.product.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'brand_name' => 'required',
                'searah' => 'required|integer',
                'category' => 'required|integer',

                'name' => 'required|string',
                // 'material_code' => 'required|string',
                // 'material_name' => 'required|string',
                // 'alias' => 'required|string',
                'buying_price' => 'nullable|numeric|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'description' => 'nullable|string',
                'note' => 'nullable|string',

                'default_quantity' => 'required|numeric',
                'default_unit' => 'required|integer',
                // 'ratio' => 'required|numeric',
                'default_warehouse' => 'required|integer',

                // 'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                // 'image_hd' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];
  
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                $product = new Product;

                $product->code = $request->code;
                $product->brand_name = $request->brand_name;
                $product->sub_brand_reference_id = $request->searah;
                $product->category_id = $request->category;

                $product->name = $request->name;
                $product->material_code = $request->material_code;
                $product->material_name = $request->material_name;
                $product->alias = $request->alias;
                $product->buying_price = $request->buying_price;
                $product->selling_price = $request->selling_price;
                $product->description = $request->description;
                $product->note = $request->note;
                $product->gender = $request->gender;

                $product->default_quantity = $request->default_quantity;
                $product->default_unit_id = $request->default_unit;
                $product->ratio = $request->ratio;
                $product->default_warehouse_id = $request->default_warehouse;

                if (!empty($request->file('image'))) {
                    $product->image = UploadMedia::image($request->file('image'), Product::$directory_image);
                }

                if (!empty($request->file('image_hd'))) {
                    $product->image_hd = UploadMedia::image($request->file('image_hd'), Product::$directory_image);
                }

                $product->status = Product::STATUS['ACTIVE'];

                if ($product->save()) {
                    if($request->parfume_scent) {
                        foreach($request->parfume_scent as $key => $value){
                            if($request->parfume_scent[$key]) {

                                $frag = new Fragrantica;
                                $frag->product_id = $product->id;
                                $frag->brand_reference_id = $product->sub_brand_reference->brand_reference->id;
                                $frag->parfume_scent = $request->parfume_scent[$key];
                                $frag->scent_range = $request->scent_range[$key];
                                $frag->color_scent = $request->color_scent[$key];
                                $frag->save();
                            }
                        }
                    }

                    $stock = new ProductMinStock;
                    $stock->product_id = $product->id;
                    $stock->warehouse_id = $product->default_warehouse_id;
                    $stock->unit_id = $product->default_unit_id;
                    $stock->quantity = $product->default_quantity;
                    $stock->selling_price = $product->selling_price;

                    $stock->save();
                   

                    // dd($value);
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['product'] = Product::findOrFail($id);
        // $data['frag'] = Fragrantica::all();

        return view('superuser.master.product.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['product'] = Product::findOrFail($id);

        $data['brand_references'] = MasterRepo::brand_references();
        $data['sub_brand_references'] = MasterRepo::sub_brand_references();
        $data['product_categories'] = MasterRepo::product_categories();
        $data['product_types'] = MasterRepo::product_types();
        $data['units'] = MasterRepo::units();
        $data['warehouses'] = MasterRepo::warehouses();
        $data['product_notes'] = Product::NOTE;
        
        return view('superuser.master.product.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $product = Product::find($id);

            if ($product == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'brand_reference' => 'required|integer',
                'sub_brand_reference' => 'required|integer',
                'category' => 'required|integer',
                'type' => 'required|integer',

                'code' => 'required|string',
                'name' => 'required|string',
                'material_code' => 'required|string',
                'material_name' => 'required|string',
                // 'alias' => 'required|string',
                'buying_price' => 'nullable|numeric|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'description' => 'nullable|string',
                'note' => 'nullable|string',

                'default_quantity' => 'required|numeric',
                'default_unit' => 'required|integer',
                // 'ratio' => 'required|string',
                'default_warehouse' => 'required|integer',

                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image_hd' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];
  
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                $product->brand_reference_id = $request->brand_reference;
                $product->sub_brand_reference_id = $request->sub_brand_reference;
                $product->category_id = $request->category;
                $product->type_id = $request->type;

                $product->code = $request->code;
                $product->name = $request->name;
                $product->material_code = $request->material_code;
                $product->material_name = $request->material_name;
                $product->alias = $request->alias;
                $product->buying_price_under = $request->buying_price_under;
                $product->buying_price_high = $request->buying_price_high;
                $product->selling_price_under = $request->selling_price_under;
                $product->selling_price_high = $request->selling_price_high;
                $product->description = $request->description;
                $product->note = $request->note;

                $product->default_quantity = $request->default_quantity;
                $product->ratio = $request->ratio;
                $product->default_unit_id = $request->default_unit;
                $product->default_warehouse_id = $request->default_warehouse;
                $product->url = $request->url;

                if (!empty($request->file('image'))) {
                    if (is_file_exists(Product::$directory_image.$product->image)) {
                        remove_file(Product::$directory_image.$product->image);
                    }
                    $product->image = UploadMedia::image($request->file('image'), Product::$directory_image);
                }

                if (!empty($request->file('image_hd'))) {
                    if (is_file_exists(Product::$directory_image.$product->image_hd)) {
                        remove_file(Product::$directory_image.$product->image_hd);
                    }
                    $product->image_hd = UploadMedia::image($request->file('image_hd'), Product::$directory_image);
                }

                if ($product->save()) {
                        // $update_stock = ProductMinStock::where('product_id', $product->id)
                        //                     ->update([
                        //                         'warehouse_id' => $product->default_warehouse_id
                        //                     ]);

                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        if ($request->ajax()) {
            $product = Product::find($id);

            if ($product === null) {
                abort(404);
            }

            $product->status = Product::STATUS['DELETED'];

            if ($product->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function destroyMultiple(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        if ($request->ajax()) {
            if($request->method() == "POST"){
                $post = $request->all();
                DB::beginTransaction();
                try{
                    $ids = $post['ids'];

                    foreach ($ids as $id) {
                        $product = Product::find($id);

                        if ($product === null) {
                            abort(404);
                        }

                        $product->status = Product::STATUS['DELETED'];

                        if (!$product->save()) {
                            DB::rollback();
                            $data_json["IsError"] = TRUE;
                            $data_json["Message"] = $e->getMessage();
                            goto ResultData;
                        }
                    }

                    DB::commit();
                    $data_json["IsError"] = FALSE;
                    $data_json['redirect_to'] = '#datatable';
                    goto ResultData;
                }catch(\Throwable $e){
                    DB::rollback();
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = $e->getMessage();
                    goto ResultData;
                }
            }
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function disable(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $product = Product::find($id);

        if ($product === null) {
            abort(404);
        }

        $product->status = Product::STATUS['INACTIVE'];

        if ($product->save()) {
            return redirect()->route('superuser.master.product.show', $product->id);
        }
    }

    public function enable(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $product = Product::find($id);

        if ($product === null) {
            abort(404);
        }

        $product->status = Product::STATUS['ACTIVE'];

        if ($product->save()) {
            return redirect()->route('superuser.master.product.show', $product->id);
        }
    }

    public function import_template()
    {
        $filename = 'master-product-import-template.xlsx';
        return Excel::download(new ProductImportTemplate, $filename);
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
            Excel::import(new ProductImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-product-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new ProductExport, $filename);
    }

    public function cetakPdf(Request $request)
    {
        // Access
        /*if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }*/
        /*if (!Auth::guard('superuser')->user()->can('hpp produksi report-print')) {
            return abort(403);
        }*/

        $validator = Validator::make($request->all(), [
            'category' => 'required',
            'jenisReport' => 'required',
        ]);

        if ($validator->fails()) {
            abort(404);
        }

        $longdaCategoryId = ProductCategory::where('name', 'like', 'LONGDA')->get();
        if (isset($longdaCategoryId) && sizeof($longdaCategoryId) > 0) {
            $longdaCategoryId = $longdaCategoryId[0]->id;
        }

        /* senses PL urut nama produk maret
        SELECT CONCAT(master_product_types.name, ';', master_products.code) AS kode, master_products.name, CONCAT(master_brand_references.name, ';', master_sub_brand_references.name) AS searah, master_products.selling_price
        FROM `master_products` 
            JOIN master_product_categories ON master_product_categories.id = master_products.category_id
            JOIN master_product_category_types ON master_product_category_types.category_id = master_products.category_id
            JOIN master_product_types ON master_product_types.id = master_product_category_types.type_id
            JOIN master_brand_references ON master_brand_references.id = master_products.brand_reference_id
            JOIN master_sub_brand_references ON master_sub_brand_references.id = master_products.sub_brand_reference_id AND master_sub_brand_references.brand_reference_id = master_brand_references.id
        WHERE master_product_categories.id = 2 AND master_product_types.id = 7
        ORDER BY master_products.name ASC
        */

        $groupBy = 'tidak';
        if ($request->jenisReport == 'pl') {
            $groupBy = 'tidak';
        } else if ($request->jenisReport == 'pd') {
            $groupBy = 'searah';
        }

        // Overriden by non FF
        if ($request->type == 'nonFF-ALL') {
            $groupBy = 'searah';
        }

        $groupByField = '';
        if ('tidak' !== $groupBy) {
            if ('searah' == $groupBy) {
                $groupByField = 'master_brand_references.name';
            } else if ('type' == $groupBy) {
                $groupByField = 'master_product_types.name';
            }
        }

        $sortBy = 'namaProduk';
        if ($request->jenisReport == 'pl') {
            $sortBy = 'namaProduk';
        } else if ($request->jenisReport == 'pd') {
            $sortBy = 'brand';
        }
        // Overriden by non FF
        if ($request->type == 'nonFF-ALL') {
            $sortBy = 'type';
        }
        $sortByField = '';
        if ('namaProduk' == $sortBy) {
            $sortByField = 'master_products.name';
        } else if ('brand' == $sortBy) {
            $sortByField = 'master_brand_references.name';
        } else if ('type' == $sortBy) {
            $sortByField = 'master_product_types.name';
        }

        $categoryId = $request->category;
        $category = ProductCategory::findOrFail($categoryId);

        $type = null;
        $kodeField = "CONCAT(master_product_types.name, '<br />', master_products.code) AS kode";
        if ('all' !== $request->input('type') && $request->type !== 'nonFF-ALL') {
            $type = ProductType::findOrFail($request->type);
            $kodeField = 'master_products.code AS kode';
        }
        if ($groupByField == 'master_product_types.name' || $request->type == 'nonFF-ALL') {
            $kodeField = 'master_products.code AS kode';
        }

        $searahField = "CONCAT('<strong>', master_brand_references.name, '</strong><br />', master_sub_brand_references.name) AS searah";
        if ($groupByField == 'master_brand_references.name') {
            $searahField = 'master_sub_brand_references.name AS searah';
        }

        $products = Product::selectRaw( ($groupByField != '' ? $groupByField . ' AS group_value, ' : '') . $kodeField . ", master_product_types.name as type, master_products.name, " . $searahField . ", master_products.selling_price, master_brand_references.name as brand, master_sub_brand_references.name as sub_brand")
        ->join('master_product_categories', 'master_product_categories.id', '=', 'master_products.category_id')
        ->join('master_product_types', 'master_product_types.id', '=', 'master_products.type_id')
        ->join('master_brand_references', 'master_brand_references.id', '=', 'master_products.brand_reference_id')
        ->join('master_sub_brand_references', function($join) {
            $join->on('master_sub_brand_references.id', '=', 'master_products.sub_brand_reference_id');
            $join->on('master_sub_brand_references.brand_reference_id', '=', 'master_brand_references.id');
        })
        ->where('master_product_categories.id', $categoryId)
        ->where('master_products.status', 1);

        if ('all' !== $request->input('type') && $request->type !== 'nonFF-ALL') {
            $products =  $products->where('master_product_types.id', $request->input('type'));
        }
        
        if ($request->type == 'nonFF-ALL') {
            $products = $products->where('master_product_types.name', 'like', "Non FF -%");
            $products = $products->orderBy('master_product_types.name','ASC');
        }

        if ('' !== $groupByField) {
            $products =  $products->orderBy($groupByField, 'ASC');
        }

        $products = $products->orderBy($sortByField,'ASC');

        $products = $products->orderBy('master_products.name','ASC');

        $data['banner'] = '';
        if ($request->jenisReport == 'pl') {
            $data['banner'] = $category->image_header_price;
            $data['jenisReportText'] = $category->name . " Price List";
        } else if ($request->jenisReport == 'pd') {
            $data['banner'] = $category->image_header_list;
            $data['jenisReportText'] = $category->name . " Product List";
        }

        $data['typeText'] = '';
        if ('all' !== $request->input('type') && $request->type !== 'nonFF-ALL') {
            $data['typeText'] = $type->name;
        }

        $data['products'] = $products->get();

        $data['request'] = $request;
        $data['groupBy'] = $groupBy;

        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        if ($request->type === 'nonFF-ALL') {
            $pdf->loadView('superuser.master.product.cetak.pdf-non-ff', $data);
        } else if ($request->category == $longdaCategoryId) {
            $pdf->loadView('superuser.master.product.cetak.pdf-longda', $data);
        } else {
            $pdf->loadView('superuser.master.product.cetak.pdf', $data);
        }
        $pdf->setPaper('a5', 'portait');

        $filename = 'Print Product ' . date('dmY') . '.pdf';
        if ($request->jenisReport == 'pl') {
            $filename = 'Price List';
        } else if ($request->jenisReport == 'pd') {
            $filename = 'Product List';
        }
        $filename .= ' ' . $category->name;
        if ($request->type === 'nonFF-ALL') {
            $filename .= ' nonFF';
        }

        $filename .= ' ' . date('dmY') . '.pdf';

        return $pdf->stream($filename);
    }

    public function cetak() {
        $data['categories'] = MasterRepo::product_categories();
        $data['types'] = MasterRepo::product_types();

        return view('superuser.master.product.cetak.index', $data);
    }

    // public function fragrant()
    // {
    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => "https://www.fragrantica.com/perfume/Bath-and-Body-Works/Cactus-Blossom-56360.html",// your preferred link
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_TIMEOUT => 30000,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => "GET",
    //         CURLOPT_HTTPHEADER => array(
    //             // Set Here Your Requesred Headers
    //             'Content-Type: application/json',
    //         ),
    //     ));

    //     $response = curl_exec($curl);
    //     $err = curl_error($curl);
    //     curl_close($curl);

    //     if($err){
    //         echo "cURL Error #:" . $err;
    //     }else{
    //         print_r(json_decode($response));
    //     }
    //     // dd($response);
    // }

    // public function cetak_cr()
    // {
    //     $runtime = new \NetPhp\Core\NetPhpRuntime('COM', 'netutilities.NetPhpRuntime');
    // }
}
