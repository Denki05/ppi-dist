<?php

namespace App\Http\Controllers\Superuser\Utility;

use App\Entities\Account\Superuser;
use App\Entities\Utility\Permission;
use App\Entities\Utility\Role;
use App\Helper\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class GateController extends Controller
{
    public function index()
    {
        $data['roles'] = Role::get();
        $data['permission_modules'] = PermissionHelper::MODULES;
        $data['permission_actions'] = PermissionHelper::ACTIONS;
        return view('superuser.gate.index', $data);
    }

    public function save_guard(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
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
                setting([
                    'system.guard' => $request->name,
                ]);

                setting()->save();

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Guard updated',
                ];

                $response['redirect_to'] = 'reload()';

                return $this->response(200, $response);
            }
        }
    }

    public function save_role(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:roles,name',
                'guard' => 'required|string',
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
                $role = new Role;

                $role->name = $request->name;
                $role->guard_name = $request->guard;

                if ($role->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = 'reload()';

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show_role($id)
    {
        $users = [];

        if ($id == -1) {
            $account = Superuser::doesntHave('roles')->get(); 

            foreach($account as $data) {
                array_push($users, [
                    'type' => 'superuser',
                    'id' => $data->id,
                    'username' => $data->username,
                    'email' => $data->email
                ]);
            }
        } else {
            $role = Role::findOrFail($id);

            foreach($role->user()->get() as $data) {
                array_push($users, [
                    'type' => $data->model_type == 'App\Entities\Account\Superuser' ? 'superuser': 'user',
                    'id' => $data->model_id,
                    'username' => $data->model_type::findOrFail($data->model_id)->username,
                    'email' => $data->model_type::findOrFail($data->model_id)->email
                ]);
            }
        }

        return $users;
    }

    public function delete_role($id)
    {
        $role = Role::findOrFail($id);

        $whitelist = ['Developer', 'Admin', 'SuperAdmin'];
        
        if ($role->user()->count() > 0 OR in_array($role->name, $whitelist)) {
            abort(403);
        }

        $role->delete();

        return redirect()->route('superuser.gate.index');
    }

    public function save_permission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:single,reload',
            'guard' => 'required|string',
            'permission' => 'required|string'
        ]);

        if ($validator->failed()) {
            $response['notification'] = [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => $validator->errors()->all(),
            ];

            return $this->response(400, $response);
        }

        if ($validator->passes()) {
            if ($request->type == 'reload') {
                foreach(PermissionHelper::ACTIONS as $action) {
                    Permission::updateOrCreate([
                        'name' => $request->permission . '-' . $action,
                        'guard_name' => $request->guard
                    ]);
                }
            }

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = 'reload()';

            return $this->response(200, $response);        
        }
    }
}
