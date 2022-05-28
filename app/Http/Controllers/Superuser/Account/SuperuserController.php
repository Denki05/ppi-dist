<?php

namespace App\Http\Controllers\Superuser\Account;

use App\DataTables\Account\SuperuserTable;
use App\Entities\Account\Superuser;
use App\Entities\Utility\Role;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Validator;

class SuperuserController extends Controller
{
    public function json(Request $request, SuperuserTable $datatable) {
        return $datatable->build();
    }

    public function index()
    {
        return view('superuser.account.superuser.index');
    }

    public function create()
    {
        return view('superuser.account.superuser.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:superusers,username',
                'email' => 'required|email|unique:superusers,email',
                'password' => 'required_with:current_password|min:8|max:16|confirmed',
                'name' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
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
                $role = Role::where('name', 'Admin')->firstOrFail();
    
                $superuser = new Superuser;
    
                $superuser->username = $request->username;
                $superuser->email = $request->email;
                $superuser->password = Hash::make($request->password);
                $superuser->name = $request->name;
    
                if (!empty($request->file('image'))) {
                    $superuser->image = UploadMedia::image($request->file('image'), Superuser::$directory_image);
                }
    
                if ($superuser->save()) {
                    $superuser->assignRole($role->name);
    
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
    
                    $response['redirect_to'] = route('superuser.account.superuser.show', $superuser->id);
    
                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        $data['account'] = Superuser::findOrFail($id);

        if (($data['account']->id == 1 OR $data['account']->email == 'contact.willystwn@gmail.com') AND !Auth::guard('superuser')->user()->hasRole('Developer')) {
            abort(403);
        }

        return view('superuser.account.superuser.show', $data);
    }

    public function edit($id)
    {
        $data['account'] = Superuser::findOrFail($id);

        if (($data['account']->id == 1 OR $data['account']->email == 'contact.willystwn@gmail.com') AND !Auth::guard('superuser')->user()->hasRole('Developer')) {
            abort(403);
        }

        return view('superuser.account.superuser.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:superusers,username,' . $id,
                'email' => 'required|email|unique:superusers,email,' . $id,
                'password' => 'nullable|min:8|max:16|confirmed',
                'name' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
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
                $superuser = Superuser::find($id);
    
                if ($superuser === null) {
                    abort(404);
                }
    
                if (Auth::guard('superuser')->user()->hasRole('SuperAdmin')) {
                    $superuser->username = $request->username;
                    $superuser->email = $request->email;
    
                    if (!empty($request->password)) {
                        $superuser->password = Hash::make($request->password);
                    }
                }
    
                $superuser->name = $request->name;
    
                if (!empty($request->file('image'))) {
                    if (is_file_exists(Superuser::$directory_image.$superuser->image)) {
                        remove_file(Superuser::$directory_image.$superuser->image);
                    }
    
                    $superuser->image = UploadMedia::image($request->file('image'), Superuser::$directory_image);
                }
    
                if ($superuser->save()) {
    
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
    
                    $response['redirect_to'] = route('superuser.account.superuser.show', $superuser->id);
    
                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            $superuser = Superuser::find($id);

            if ($superuser->hasRole('Developer') OR !Auth::guard('superuser')->user()->hasRole('SuperAdmin')) {
                abort(403);
            }

            if ($superuser === null) {
                abort(404);
            }

            $superuser->is_active = false;

            if ($superuser->save()) {
                $response['redirect_to'] = '#datatable';

                return $this->response(200, $response);
            }
        }
    }

    public function restore(Request $request, $id)
    {
        if ($request->ajax()) {
            $superuser = Superuser::find($id);

            if ($superuser->hasRole('Developer') OR !Auth::guard('superuser')->user()->hasRole('SuperAdmin')) {
                abort(403);
            }

            if ($superuser === null) {
                abort(404);
            }

            $superuser->is_active = true;

            if ($superuser->save()) {
                $response['redirect_to'] = '#datatable';

                return $this->response(200, $response);
            }
        }
    }

    public function assignRole(Request $request, $id)
    {
        if ($request->ajax()) {
            $role = Role::find($request->role);
            $superuser = Superuser::find($id);

            if ($role === null OR $superuser === null) {
                abort(404);
            }

            $superuser->assignRole($role->name);

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = 'reload()';
            return $this->response(200, $response);
        }
    }

    public function removeRole(Request $request, $id)
    {
        if ($request->ajax()) {
            $role = Role::find($request->role);
            $superuser = Superuser::find($id);

            if ($role === null OR $superuser === null) {
                abort(404);
            }

            if ($role->name == 'Developer' AND !Auth::guard('superuser')->id() == 1) {
                abort(403);
            }

            $superuser->removeRole($role->name);

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = 'reload()';
            return $this->response(200, $response);
        }
    }

    public function syncPermission(Request $request, $id)
    {
        if ($request->ajax()) {
            $superuser = Superuser::findOrFail($id);

            $superuser->syncPermissions($request->permissions);

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = route('superuser.account.superuser.show', $superuser->id);
            
            return $this->response(200, $response);
        }
    }
}
