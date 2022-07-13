<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\VendorTable;
use App\Entities\Master\Vendor;
use App\Exports\Master\VendorExport;
use App\Exports\Master\VendorImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\VendorImport;
use App\Repositories\CodeRepo;
use DB;
use Excel;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class VendorController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.vendor";
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
    public function json(Request $request, VendorTable $datatable)
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
        return view('superuser.master.vendor.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        return view('superuser.master.vendor.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'address' => 'nullable|string',

                'email' => 'nullable|email',
                'phone' => 'nullable|string',

                'owner_name' => 'nullable|string',
                'website' => 'nullable|string',

                'description' => 'nullable|string',
                'type' => 'nullable|string'
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

                $vendor = new Vendor;

                $vendor->code = CodeRepo::generateVendor();
                $vendor->name = $request->name;
                $vendor->address = $request->address;

                $vendor->email = $request->email;
                $vendor->phone = $request->phone;

                $vendor->owner_name = $request->owner_name;
                $vendor->website = $request->website;
                
                $vendor->description = $request->description;
                
                $vendor->type = $request->type;

                $vendor->status = Vendor::STATUS['ACTIVE'];

                if ($vendor->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.vendor.index');

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

        $data['vendor'] = Vendor::findOrFail($id);

        return view('superuser.master.vendor.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['vendor'] = Vendor::findOrFail($id);

        return view('superuser.master.vendor.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $vendor = Vendor::find($id);

            if ($vendor == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'address' => 'nullable|string',

                'email' => 'nullable|email',
                'phone' => 'nullable|string',

                'owner_name' => 'nullable|string',
                'website' => 'nullable|string',

                'description' => 'nullable|string'
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

                // $vendor->code = $request->code;
                $vendor->name = $request->name;
                $vendor->address = $request->address;
                
                $vendor->email = $request->email;
                $vendor->phone = $request->phone;

                $vendor->owner_name = $request->owner_name;
                $vendor->website = $request->website;
                
                $vendor->description = $request->description;

                if ($vendor->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.vendor.index');

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
            $vendor = Vendor::find($id);

            if ($vendor === null) {
                abort(404);
            }

            $vendor->status = Vendor::STATUS['DELETED'];

            if ($vendor->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-vendor-import-template.xlsx';
        return Excel::download(new VendorImportTemplate, $filename);
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
            Excel::import(new VendorImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-vendor-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new VendorExport, $filename);
    }
}
