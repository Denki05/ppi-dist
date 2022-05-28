<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\ProductTypeTable;
use App\Entities\Master\ProductType;
use App\Exports\Master\ProductTypeExport;
use App\Exports\Master\ProductTypeImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\ProductTypeImport;
use App\Repositories\CodeRepo;
use DB;
use Excel;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class ProductTypeController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.product_type";
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
    public function json(Request $request, ProductTypeTable $datatable)
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
        return view('superuser.master.product_type.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        return view('superuser.master.product_type.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_product_types,code',
                'name' => 'required|string',
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

            if ($validator->passes()) {
                DB::beginTransaction();

                $product_type = new ProductType;

                $product_type->code = CodeRepo::generateProductType();
                $product_type->name = $request->name;
                $product_type->description = $request->description;
                $product_type->status = ProductType::STATUS['ACTIVE'];

                if ($product_type->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product_type.index');

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
        $data['product_type'] = ProductType::findOrFail($id);

        return view('superuser.master.product_type.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $data['product_type'] = ProductType::findOrFail($id);

        return view('superuser.master.product_type.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $product_type = ProductType::find($id);

            if ($product_type == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_product_types,code,' . $product_type->id,
                'name' => 'required|string',
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

            if ($validator->passes()) {
                DB::beginTransaction();

                // $product_type->code = $request->code;
                $product_type->name = $request->name;
                $product_type->description = $request->description;

                if ($product_type->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product_type.index');

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
            $product_type = ProductType::find($id);

            if ($product_type === null) {
                abort(404);
            }

            $product_type->status = ProductType::STATUS['DELETED'];

            if ($product_type->save()) {
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
                        $product_type = ProductType::find($id);

                        if ($product_type === null) {
                            abort(404);
                        }

                        $product_type->status = ProductType::STATUS['DELETED'];

                        if (!$product_type->save()) {
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

    public function import_template()
    {
        $filename = 'master-product-type-import-template.xlsx';
        return Excel::download(new ProductTypeImportTemplate, $filename);
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
            Excel::import(new ProductTypeImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-product-type-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new ProductTypeExport, $filename);
    }
}
