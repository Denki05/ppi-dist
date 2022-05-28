<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\EkspedisiTable;
use App\Entities\Master\Ekspedisi;
use App\Exports\Master\EkspedisiExport;
use App\Exports\Master\EkspedisiImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\EkspedisiImport;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class EkspedisiController extends Controller
{
    public function json(Request $request, EkspedisiTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        // if(!Auth::guard('superuser')->user()->can('ekspedisi-manage')) {
        //     return abort(403);
        // }

        return view('superuser.master.ekspedisi.index');
    }

    public function create()
    {
        // if(!Auth::guard('superuser')->user()->can('ekspedisi-create')) {
        //     return abort(403);
        // }

        return view('superuser.master.ekspedisi.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_ekspedisi,code',
                'name' => 'required|string',
                'address' => 'nullable|string',

                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',

                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',

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
                $ekspedisi = new Ekspedisi;

                $ekspedisi->code = $request->code;
                $ekspedisi->name = $request->name;
                $ekspedisi->address = $request->address;

                $ekspedisi->provinsi = $request->provinsi;
                $ekspedisi->kota = $request->kota;
                $ekspedisi->kecamatan = $request->kecamatan;
                $ekspedisi->kelurahan = $request->kelurahan;
                $ekspedisi->text_provinsi = $request->text_provinsi;
                $ekspedisi->text_kota = $request->text_kota;
                $ekspedisi->text_kecamatan = $request->text_kecamatan;
                $ekspedisi->text_kelurahan = $request->text_kelurahan;

                $ekspedisi->zipcode = $request->zipcode;

                $ekspedisi->email = $request->email;
                $ekspedisi->phone = $request->phone;
                $ekspedisi->fax = $request->fax;

                $ekspedisi->owner_name = $request->owner_name;
                $ekspedisi->website = $request->website;
                
                $ekspedisi->description = $request->description;
                $ekspedisi->status = Ekspedisi::STATUS['ACTIVE'];

                if ($ekspedisi->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.ekspedisi.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        // if(!Auth::guard('superuser')->user()->can('ekspedisi-show')) {
        //     return abort(403);
        // }

        $data['ekspedisi'] = Ekspedisi::findOrFail($id);

        return view('superuser.master.ekspedisi.show', $data);
    }

    public function edit($id)
    {
        // if(!Auth::guard('superuser')->user()->can('ekspedisi-edit')) {
        //     return abort(403);
        // }

        $data['ekspedisi'] = Ekspedisi::findOrFail($id);

        return view('superuser.master.ekspedisi.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $ekspedisi = Ekspedisi::find($id);

            if ($ekspedisi == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_ekspedisi,code,'. $ekspedisi->id,
                'name' => 'required|string',
                'address' => 'nullable|string',

                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',

                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',

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
                $ekspedisi->code = $request->code;
                $ekspedisi->name = $request->name;
                $ekspedisi->address = $request->address;

                $ekspedisi->provinsi = $request->provinsi;
                $ekspedisi->kota = $request->kota;
                $ekspedisi->kecamatan = $request->kecamatan;
                $ekspedisi->kelurahan = $request->kelurahan;
                $ekspedisi->text_provinsi = $request->text_provinsi;
                $ekspedisi->text_kota = $request->text_kota;
                $ekspedisi->text_kecamatan = $request->text_kecamatan;
                $ekspedisi->text_kelurahan = $request->text_kelurahan;

                $ekspedisi->zipcode = $request->zipcode;

                $ekspedisi->email = $request->email;
                $ekspedisi->phone = $request->phone;
                $ekspedisi->fax = $request->fax;

                $ekspedisi->owner_name = $request->owner_name;
                $ekspedisi->website = $request->website;
                
                $ekspedisi->description = $request->description;

                if ($ekspedisi->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.ekspedisi.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            // if(!Auth::guard('superuser')->user()->can('ekspedisi-delete')) {
            //     return abort(403);
            // }

            $ekspedisi = Ekspedisi::find($id);

            if ($ekspedisi === null) {
                abort(404);
            }

            $ekspedisi->status = Ekspedisi::STATUS['DELETED'];

            if ($ekspedisi->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-ekspedisi-import-template.xlsx';
        return Excel::download(new EkspedisiImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            Excel::import(new EkspedisiImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-ekspedisi-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new EkspedisiExport, $filename);
    }
}
