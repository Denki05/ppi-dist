<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\StoreTable;
use App\Entities\Master\Store;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CodeRepo;
use App\Repositories\MasterRepo;
use Illuminate\Support\Facades\Auth;
use Validator;

class StoreController extends Controller
{
    public function json(Request $request, StoreTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view('superuser.master.store.index');
    }

    public function create()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['customers'] = MasterRepo::customers();

        return view('superuser.master.store.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_stores,code',
                'name' => 'required|string'
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
                $store = new Store;

                $store->code = CodeRepo::generateStores();
                $store->name = $request->name;
                $store->customer_id = $request->customer;

                $store->status = Store::STATUS['ACTIVE'];

                if ($store->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.store.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['store'] = Store::findOrFail($id);

        return view('superuser.master.store.show', $data);
    }

    public function edit($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['store'] = Store::findOrFail($id);
        $data['customers'] = MasterRepo::customers();

        return view('superuser.master.store.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $store = Store::find($id);

            if ($store == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_stores,code,' . $store->id,
                'name' => 'required|string'
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
                // $store->code = $request->code;
                $store->name = $request->name;
                $store->customer_id = $request->customer;

                if ($store->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.store.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('branch office-delete')) {
                return abort(403);
            }

            $store = Store::find($id);

            if ($store === null) {
                abort(404);
            }

            // $store->status = Store::STATUS['DELETED'];

            if ($store->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
