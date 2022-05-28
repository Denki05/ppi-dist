<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\ProductCategoryTable;
use App\Entities\Master\ProductCategory;
use App\Exports\Master\ProductCategoryExport;
use App\Exports\Master\ProductCategoryImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\ProductCategoryImport;
use App\Repositories\CodeRepo;
use App\Repositories\MasterRepo;
use DB;
use Excel;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class ProductCategoryController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.product_category";
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
    public function json(Request $request, ProductCategoryTable $datatable)
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
        return view('superuser.master.product_category.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        // $data['product_types'] = MasterRepo::product_types();

        return view('superuser.master.product_category.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_product_categories,code',
                'name' => 'required|string',
                // 'type' => 'required|integer',
                'description' => 'nullable|string',
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

            $image_header_price = $request->file('image_header_price');
            $image_header_list = $request->file('image_header_list');

            if(!empty($image_header_price)){
                $extension = $image_header_price->getClientOriginalExtension();
                $valid_ext = ['jpeg','png','jpg','gif'];

                if(!in_array(strtolower($extension), $valid_ext)){
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Format image diperbolehkan yaitu jpeg,jpg,png,gif',
                    ];

                    return $this->response(400, $response);
                }

                $image_header_price = $image_header_price->store('images/master/product_category', 'public');
            }

            if(!empty($image_header_list)){
                $extension = $image_header_list->getClientOriginalExtension();
                $valid_ext = ['jpeg','png','jpg','gif'];

                if(!in_array(strtolower($extension), $valid_ext)){
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Format image diperbolehkan yaitu jpeg,jpg,png,gif',
                    ];

                    return $this->response(400, $response);
                }

                $image_header_list = $image_header_list->store('images/master/product_category', 'public');
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                $product_category = new ProductCategory;

                // $product_category->type_id = $request->type;
                $product_category->code = CodeRepo::generateProductCategory();
                $product_category->name = $request->name;
                $product_category->description = $request->description;
                $product_category->status = ProductCategory::STATUS['ACTIVE'];
                $product_category->image_header_list = $image_header_list;
                $product_category->image_header_price = $image_header_price;

                if ($product_category->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product_category.index');

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

        $data['product_category'] = ProductCategory::findOrFail($id);

        return view('superuser.master.product_category.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['product_category'] = ProductCategory::findOrFail($id);
        // $data['product_types'] = MasterRepo::product_types();

        return view('superuser.master.product_category.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $product_category = ProductCategory::find($id);

            if ($product_category == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_product_categories,code,' . $product_category->id,
                'name' => 'required|string',
                // 'type' => 'required|integer',
                'description' => 'nullable|string',
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

            $image_header_price = $request->file('image_header_price');
            $image_header_list = $request->file('image_header_list');

            if(!empty($image_header_price)){
                $extension = $image_header_price->getClientOriginalExtension();
                $valid_ext = ['jpeg','png','jpg','gif'];

                if(!in_array(strtolower($extension), $valid_ext)){
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Format image diperbolehkan yaitu jpeg,jpg,png,gif',
                    ];

                    return $this->response(400, $response);
                }

                $image_header_price = $image_header_price->store('images/master/product_category', 'public');
            }
            else{
                $image_header_price = $product_category->image_header_price;
            }

            if(!empty($image_header_list)){
                $extension = $image_header_list->getClientOriginalExtension();
                $valid_ext = ['jpeg','png','jpg','gif'];

                if(!in_array(strtolower($extension), $valid_ext)){
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Format image diperbolehkan yaitu jpeg,jpg,png,gif',
                    ];

                    return $this->response(400, $response);
                }

                $image_header_list = $image_header_list->store('images/master/product_category', 'public');
            }
            else{
                $image_header_list = $product_category->image_header_list;
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                // $product_category->type_id = $request->type;
                // $product_category->code = $request->code;
                $product_category->name = $request->name;
                $product_category->description = $request->description;
                $product_category->image_header_list = $image_header_list;
                $product_category->image_header_price = $image_header_price;

                if ($product_category->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product_category.index');

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
            $product_category = ProductCategory::find($id);

            if ($product_category === null) {
                abort(404);
            }

            $product_category->status = ProductCategory::STATUS['DELETED'];

            if ($product_category->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-product-category-import-template.xlsx';
        return Excel::download(new ProductCategoryImportTemplate, $filename);
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
            Excel::import(new ProductCategoryImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-product-category-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new ProductCategoryExport, $filename);
    }
}
