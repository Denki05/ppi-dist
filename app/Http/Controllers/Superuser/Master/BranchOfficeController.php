<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\BranchOfficeTable;
use App\Entities\Master\BranchOffice;
use App\Entities\Setting\UserMenu;
use App\Http\Controllers\Controller;
use App\Repositories\CodeRepo;
use DB;
use Illuminate\Http\Request;
use Validator;
use Auth;

class BranchOfficeController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.branch_office";
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
    public function json(Request $request, BranchOfficeTable $datatable)
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

        return view('superuser.master.branch_office.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view('superuser.master.branch_office.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_branch_offices,code',
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'address' => 'required|string',
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

                $branch_office = new BranchOffice;  

                $branch_office->code = CodeRepo::generateBranchOffice();
                $branch_office->name = $request->name;
                $branch_office->contact_person = $request->contact_person;
                $branch_office->phone = $request->phone;
                $branch_office->address = $request->address;
                $branch_office->description = $request->description;
                $branch_office->status = BranchOffice::STATUS['ACTIVE'];

                if ($branch_office->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.branch_office.index');

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

        $data['branch_office'] = BranchOffice::findOrFail($id);

        return view('superuser.master.branch_office.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['branch_office'] = BranchOffice::findOrFail($id);

        return view('superuser.master.branch_office.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $branch_office = BranchOffice::find($id);

            if ($branch_office == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_branch_offices,code,' . $branch_office->id,
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'address' => 'required|string',
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

                // $branch_office->code = $request->code;
                $branch_office->name = $request->name;
                $branch_office->contact_person = $request->contact_person;
                $branch_office->phone = $request->phone;
                $branch_office->address = $request->address;
                $branch_office->description = $request->description;

                if ($branch_office->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.branch_office.index');

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
            
            $branch_office = BranchOffice::find($id);

            if ($branch_office === null) {
                abort(404);
            }

            $branch_office->status = BranchOffice::STATUS['DELETED'];

            if ($branch_office->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
