<?php

namespace App\Http\Controllers\Superuser\Account;

use App\DataTables\Account\SalesPersonTable;
use App\Entities\Account\SalesPerson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class SalesPersonController extends Controller
{
    public function json(Request $request, SalesPersonTable $datatable) {
        return $datatable->build();
    }

    public function index()
    {
        return view('superuser.account.sales_person.index');
    }

    public function create()
    {
        return view('superuser.account.sales_person.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:sales_persons,username',
                'email' => 'required|email|unique:sales_persons,email',
                'password' => 'required_with:current_password|min:8|max:16|confirmed',
                'name' => 'required|string',
                'phone' => 'nullable|string',
                'address' => 'required|string',
                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',
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
                $sales_person = new SalesPerson;
    
                $sales_person->username = $request->username;
                $sales_person->email = $request->email;
                $sales_person->password = Hash::make($request->password);
    
                $sales_person->name = $request->name;
                $sales_person->phone = $request->phone;
                $sales_person->address = $request->address;
    
                $sales_person->provinsi = $request->provinsi;
                $sales_person->kota = $request->kota;
                $sales_person->kecamatan = $request->kecamatan;
                $sales_person->kelurahan = $request->kelurahan;
                $sales_person->text_provinsi = $request->text_provinsi;
                $sales_person->text_kota = $request->text_kota;
                $sales_person->text_kecamatan = $request->text_kecamatan;
                $sales_person->text_kelurahan = $request->text_kelurahan;
    
                $sales_person->zipcode = $request->zipcode;
    
                if ($sales_person->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
    
                    $response['redirect_to'] = route('superuser.account.sales_person.show', $sales_person->id);
    
                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        $data['sales_person'] = SalesPerson::findOrFail($id);

        return view('superuser.account.sales_person.show', $data);
    }

    public function edit($id)
    {
        $data['sales_person'] = SalesPerson::findOrFail($id);

        return view('superuser.account.sales_person.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $sales_person = SalesPerson::find($id);

            if ($sales_person == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'username' => 'nullable|string|unique:sales_persons,username,' . $sales_person->id,
                'email' => 'nullable|email|unique:sales_persons,email,' . $sales_person->id,
                'password' => 'nullable|min:8|max:16|confirmed',
                'name' => 'required|string',
                'phone' => 'nullable|string',
                'address' => 'required|string',
                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',
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
                if (Auth::guard('superuser')->user()->hasRole('SuperAdmin')) {
                    $sales_person->username = $request->username;
                    $sales_person->email = $request->email;

                    if (!empty($request->password)) {
                        $sales_person->password = Hash::make($request->password);
                    }
                }
    
                $sales_person->name = $request->name;
                $sales_person->phone = $request->phone;
                $sales_person->address = $request->address;
    
                $sales_person->provinsi = $request->provinsi;
                $sales_person->kota = $request->kota;
                $sales_person->kecamatan = $request->kecamatan;
                $sales_person->kelurahan = $request->kelurahan;
                $sales_person->text_provinsi = $request->text_provinsi;
                $sales_person->text_kota = $request->text_kota;
                $sales_person->text_kecamatan = $request->text_kecamatan;
                $sales_person->text_kelurahan = $request->text_kelurahan;
    
                $sales_person->zipcode = $request->zipcode;
    
                if ($sales_person->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
    
                    $response['redirect_to'] = route('superuser.account.sales_person.show', $sales_person->id);
    
                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            $sales_person = SalesPerson::find($id);

            if ($sales_person === null) {
                abort(404);
            }

            $sales_person->is_active = false;

            if ($sales_person->save()) {
                $response['redirect_to'] = '#datatable';

                return $this->response(200, $response);
            }
        }
    }

    public function restore(Request $request, $id)
    {
        if ($request->ajax()) {
            $sales_person = SalesPerson::find($id);

            if ($sales_person === null) {
                abort(404);
            }

            $sales_person->is_active = true;

            if ($sales_person->save()) {
                $response['redirect_to'] = '#datatable';

                return $this->response(200, $response);
            }
        }
    }
}
