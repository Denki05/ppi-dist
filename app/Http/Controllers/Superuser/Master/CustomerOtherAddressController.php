<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\CustomerOtherAddressTable;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class CustomerOtherAddressController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.store";
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

    public function json(Request $request, CustomerOtherAddressTable $datatable)
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
        return view('superuser.master.store.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view('superuser.master.store.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'npwp' => 'nullable|string',
                'address' => 'required|string',
                'gps_latitude' => 'nullable|string',
                'gps_longitude' => 'nullable|string',
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

                $other_address = new CustomerOtherAddress;

                $other_address->name = $request->name;
                $other_address->contact_person = $request->contact_person;
                $other_address->phone = $request->phone;
                $other_address->npwp = $request->npwp;
                $other_address->address = $request->address;

                $other_address->gps_latitude = $request->gps_latitude;
                $other_address->gps_longitude = $request->gps_longitude;

                $other_address->provinsi = $request->provinsi;
                $other_address->kota = $request->kota;
                $other_address->kecamatan = $request->kecamatan;
                $other_address->kelurahan = $request->kelurahan;
                $other_address->text_provinsi = $request->text_provinsi;
                $other_address->text_kota = $request->text_kota;
                $other_address->text_kecamatan = $request->text_kecamatan;
                $other_address->text_kelurahan = $request->text_kelurahan;

                $other_address->zipcode = $request->zipcode;

                $other_address->status = CustomerOtherAddress::STATUS['ACTIVE'];
                
                if ($other_address->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    // dd($other_address->save());

                    $response['redirect_to'] = route('superuser.master.store.index');

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
        $data['other_address'] = CustomerOtherAddress::findOrFail($id);

        return view('superuser.master.store.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['other_address'] = CustomerOtherAddress::findOrFail($id);

        return view('superuser.master.store.edit', $data);
    }
    
    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'npwp' => 'nullable|string',
                'address' => 'required|string',
                'gps_latitude' => 'nullable|string',
                'gps_longitude' => 'nullable|string',
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
                $other_address = CustomerOtherAddress::find($id);

                if ($other_address == null) {
                    abort(404);
                }

                $other_address->name = $request->name;
                $other_address->contact_person = $request->contact_person;
                $other_address->phone = $request->phone;
                $other_address->npwp = $request->npwp;
                $other_address->address = $request->address;

                $other_address->gps_latitude = $request->gps_latitude;
                $other_address->gps_longitude = $request->gps_longitude;

                $other_address->provinsi = $request->provinsi;
                $other_address->kota = $request->kota;
                $other_address->kecamatan = $request->kecamatan;
                $other_address->kelurahan = $request->kelurahan;
                $other_address->text_provinsi = $request->text_provinsi;
                $other_address->text_kota = $request->text_kota;
                $other_address->text_kecamatan = $request->text_kecamatan;
                $other_address->text_kelurahan = $request->text_kelurahan;

                $other_address->zipcode = $request->zipcode;

                if ($other_address->save()) {
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
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        if ($request->ajax()) {
            $other_address = CustomerOtherAddress::find($id);

            if ($other_address === null) {
                abort(404);
            }

            $other_address->status = CustomerOtherAddress::STATUS['DELETED'];

            if ($other_address->save()) {
                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }
}
