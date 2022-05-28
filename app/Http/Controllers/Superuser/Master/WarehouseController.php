<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\WarehouseTable;
use App\Entities\Master\Warehouse;
use App\Http\Controllers\Controller;
use App\Repositories\CodeRepo;
use App\Repositories\MasterRepo;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class WarehouseController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.warehouse";
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
    public function json(Request $request, WarehouseTable $datatable)
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
        return view('superuser.master.warehouse.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        // $data['branch_offices'] = MasterRepo::branch_offices();

        return view('superuser.master.warehouse.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                // 'type' => 'required|integer',
                // 'branch_office' => Rule::requiredIf(function () use ($request) {
                //     return $request->type == Warehouse::TYPE['BRANCH_OFFICE'];
                // }),
                // 'code' => 'required|string|unique:master_warehouses,code',
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
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

                $warehouse = new Warehouse;

                // $warehouse->type = $request->type;
                // $warehouse->branch_office_id = ($warehouse->type == Warehouse::TYPE['HEAD_OFFICE']) ? null : $request->branch_office;

                $warehouse->type = Warehouse::TYPE['GENERAL'];
                $warehouse->code = CodeRepo::generateWarehouse();

                $warehouse->name = $request->name;
                $warehouse->contact_person = $request->contact_person;
                $warehouse->phone = $request->phone;
                $warehouse->address = $request->address;
                $warehouse->description = $request->description;
                $warehouse->status = Warehouse::STATUS['ACTIVE'];

                if ($warehouse->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.warehouse.index');

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
        $data['warehouse'] = Warehouse::findOrFail($id);

        return view('superuser.master.warehouse.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $data['warehouse'] = Warehouse::findOrFail($id);
        // $data['branch_offices'] = MasterRepo::branch_offices();

        return view('superuser.master.warehouse.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $warehouse = Warehouse::find($id);

            if ($warehouse == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                // 'type' => 'required|integer',
                // 'branch_office' => Rule::requiredIf(function () use ($request) {
                //     return $request->type == Warehouse::TYPE['BRANCH_OFFICE'];
                // }),
                // 'code' => 'required|string|unique:master_warehouses,code,' . $warehouse->id,
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
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

                // $warehouse->type = $request->type;
                // $warehouse->branch_office_id = ($warehouse->type == Warehouse::TYPE['HEAD_OFFICE']) ? null : $request->branch_office;

                // $warehouse->code = $request->code;
                $warehouse->name = $request->name;
                $warehouse->contact_person = $request->contact_person;
                $warehouse->phone = $request->phone;
                $warehouse->address = $request->address;
                $warehouse->description = $request->description;

                if ($warehouse->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.warehouse.index');

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
            $warehouse = Warehouse::find($id);

            if ($warehouse === null) {
                abort(404);
            }

            // check for warehouse head office
            if ($warehouse->type == Warehouse::TYPE['HEAD_OFFICE']) {
                abort(400);
            }

            $warehouse->status = Warehouse::STATUS['DELETED'];

            if ($warehouse->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
