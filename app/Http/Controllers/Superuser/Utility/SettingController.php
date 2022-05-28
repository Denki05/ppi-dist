<?php

namespace App\Http\Controllers\Superuser\Utility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
class SettingController extends Controller
{
    public function index()
    {
        return view('superuser.utility.settings');
    }

    public function website(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'maintenance' => 'nullable',
                'maintenance_message' => 'nullable|string'
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
                    'website.name' => $request->name,
                    'website.maintenance' => isset($request->maintenance),
                    'website.maintenance_message' => $request->maintenance_message,
                    'website.color_themes' => $request->color_themes
                ]);

                setting()->save();

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Setting:Website updated',
                ];

                $response['redirect_to'] = 'reload()';

                return $this->response(200, $response);
            }
        }
    }
}
