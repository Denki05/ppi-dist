<?php

namespace App\Http\Controllers\Superuser;

use App\Entities\Account\Superuser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class AuthenticationController extends Controller
{
    public function index()
    {
        return view('superuser.auth');
    }

    public function login(Request $request)
    {
        if ($request->ajax()) {
            $rules = [
                'account_name' => 'required|string',
                'password' => 'required|string',
                'remember' => 'nullable',
            ];

            $validator = validator($request->all(), $rules);

            $superuser = Superuser::where('username', $request->account_name)->orWhere('email', $request->account_name)->first();

            if (filter_var($request->account_name, FILTER_VALIDATE_EMAIL)) {
                $field = 'email';
            } else {
                $field = 'username';
            }

            $credentials = [
                $field => $request->account_name,
                'password' => $request->password,
                'is_active' => true
            ];

            $remember = $request->remember ?? false;

            if ($validator->fails() OR Auth::guard('superuser')->attempt($credentials) == false OR !$superuser) {
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'danger',
                    'content' => 'Login Failed',
                ];

                return $this->response(400, $response);
            } else if ($validator->passes() AND Auth::guard('superuser')->attempt($credentials, $remember) == true) {
                $request->session()->regenerate();

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];
                $response['redirect_to'] = route('superuser.index');
                return $this->response(200, $response);
            }
        } else {
            return redirect()->route('auth.superuser.index');
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('superuser')->logout();
        $request->session()->flush();

        return redirect()->route('auth.superuser.index');
    }

    public function getToken(Request $request) {
        if ($request->ajax()) {
            $superuser = Auth::guard('superuser')->user();
            $passcode = $request->link . '###' . $superuser->username;
            $encrypted = Crypt::encryptString($passcode);

            return response()->json($encrypted);
        } else {
            return 'hello';
        }
    }
}
