<?php

namespace App\Http\Controllers\Superuser;

use App\Entities\Account\Superuser;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class ProfileController extends Controller
{
    public function index()
    {
        return view('superuser.profile.index');
    }

    public function update(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name'             => 'nullable|string',
                'image'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'current_password' => 'nullable|string',
                'new_password'     => 'nullable|required_with:current_password|min:8|max:16|confirmed'
                // rule - 1 letter 1 number
                // regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,16}$/
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
                $superuser = Superuser::find(Auth::guard('superuser')->id());

                if ($superuser === null) {
                    abort(404);
                }

                $superuser->name = $request->input('name');

                if (!empty($request->file('image'))) {
                    if (is_file_exists(Superuser::$directory_image.$superuser->image)) {
                        remove_file(Superuser::$directory_image.$superuser->image);
                    }

                    $superuser->image = UploadMedia::image($request->file('image'), Superuser::$directory_image);
                }

                if (!empty($request->input('current_password'))) {
                    $current_password = Auth::guard('superuser')->user()->getAuthPassword();

                    $check = Hash::check($request->input('current_password'), $current_password);
                    
                    if ($check == false) {
                        $response['notification'] = [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => 'Current password is wrong!',
                        ];
    
                        return $this->response(403, $response);
                    } else {
                        $superuser->password = Hash::make($request->input('new_password'));
                    }
                }

                if ($superuser->save()) {

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
}
