<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\DataTables\Master\CustomerOtherAddressTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Models\Zipcode;
use App\Helper\UploadMedia;
use DB;

class CustomerOtherAddressController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.customer_other_address";
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
        return $datatable->build($request);
    }
	
    public function create($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['customer'] = Customer::findOrFail($id);
        $data['provinces'] = Province::all();

        return view('superuser.master.customer_other_address.create', $data);
    }

    public function getkabupaten(request $request)
    {
        $prov_id = $request->prov_id;

        $kabupatens = Regency::where('prov_id', $prov_id)->get();

        foreach ($kabupatens as $kabupaten){
            echo "<option value='$kabupaten->city_id'>$kabupaten->city_name</option>";
        }
    }

    public function getkecamatan(request $request)
    {
        $city_id = $request->city_id;

        $kecamatans = District::where('city_id', $city_id)->get();

        foreach ($kecamatans as $kecamatan){
            echo "<option value='$kecamatan->dis_id'>$kecamatan->dis_name</option>";
        }
    }

    public function getkelurahan(request $request)
    {
        $dis_id = $request->dis_id;

        $kelurahans = Village::where('dis_id', $dis_id)->get();

        foreach ($kelurahans as $kelurahan){
            echo "<option value='$kelurahan->subdis_id'>$kelurahan->subdis_name</option>";
        }
    }

    public function getzipcode(request $request)
    {
        $subdis_id = $request->subdis_id;

        $zipcodes = Zipcode::where('subdis_id', $subdis_id)->get();

        foreach ($zipcodes as $zipcode){
            echo "<option value='$zipcode->postal_code'>$zipcode->postal_code</option>";
        }
    }

    public function store(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'npwp' => 'nullable|string',
                'ktp' => 'nullable|string',
                'phone' => 'nullable|string',
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
                'zipcode' => 'nullable|string'
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
                $customer = Customer::find($id);

                if ($customer == null) {
                    abort(404);
                }

                $countMember = DB::table('master_customers')
                                ->where('id', $customer->id)
                                ->selectRaw('count_member as kode')
                                ->get(); 
                
                $kd = "";

                foreach ($countMember as $key)
                {
                    $tmp = ((int) $key->kode)+1;
                    $kd = sprintf("%01s", $tmp);
                }

                $other_address = new CustomerOtherAddress;

                $other_address->id = $customer->id.'.'.$kd;
                $other_address->customer_id = $customer->id;
                $other_address->member_default = 0;

                $other_address->name = $request->name;
                $other_address->contact_person = $request->contact_person;
                $other_address->npwp = implode("/", [$request->name_card_npwp,$request->npwp]);
                $other_address->ktp = implode("/", [$request->name_card_ktp,$request->ktp]);
                $other_address->phone = $request->phone;
                $other_address->address = $request->address;
                $other_address->free_shipping = $request->free_shipping;

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

                if (!empty($request->file('image_ktp'))) {
                    $customer->image_ktp = UploadMedia::image($request->file('image_ktp'), Customer::$directory_image);
                }

                if (!empty($request->file('image_npwp'))) {
                    $customer->image_npwp = UploadMedia::image($request->file('image_npwp'), Customer::$directory_image);
                }

                $other_address->status = CustomerOtherAddress::STATUS['ACTIVE'];

                
                if ($other_address->save()) {
                    
                    $updateCust = Customer::where('id', $customer->id)->update([
                        'count_member' => $kd
                    ]);

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer.index', $id);

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

        return view('superuser.master.customer_other_address.show', $data);
    }

    public function edit($address_id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['other_address'] = CustomerOtherAddress::findOrFail($address_id);
        $data['provinces'] = Province::all();
        $data['kota'] = Regency::all();
        $data['kecamatan'] = District::all();
        $data['kelurahan'] = Village::all();
        $data['zipcode'] = Zipcode::all();

        return view('superuser.master.customer_other_address.edit', $data);
    }
    
    public function update(Request $request, $address_id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'npwp' => 'nullable|string',
                'ktp' => 'nullable|string',
                'phone' => 'nullable|string',
                'address' => 'required|string',
                'gps_latitude' => 'nullable|string',
                'gps_longitude' => 'nullable|string',
                // 'provinsi' => 'nullable|string',
                // 'kota' => 'nullable|string',
                // 'kecamatan' => 'nullable|string',
                // 'kelurahan' => 'nullable|string',
                // 'text_provinsi' => 'nullable|required_with:provinsi|string',
                // 'text_kota' => 'nullable|required_with:kota|string',
                // 'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                // 'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                // 'zipcode' => 'nullable|string'
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
                // $customer = Customer::find($id);
                $other_address = CustomerOtherAddress::find($address_id);

                if ($other_address == null) {
                    abort(404);
                }

                $other_address->name = $request->name;
                $other_address->customer_id = $other_address->customer_id;
                $other_address->contact_person = $request->contact_person;
                $other_address->npwp = $request->npwp;
                $other_address->ktp = $request->ktp;
                $other_address->phone = $request->phone;
                $other_address->address = $request->address;
                $other_address->free_shipping = $request->free_shipping;

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

                if (!empty($request->file('image_npwp'))) {
                    if (is_file_exists(CustomerOtherAddress::$directory_image.$other_address->image_npwp)) {
                        remove_file(CustomerOtherAddress::$directory_image.$other_address->image_npwp);
                    }

                    $other_address->image_npwp = UploadMedia::image($request->file('image_npwp'), CustomerOtherAddress::$directory_image);
                }

                if (!empty($request->file('image_ktp'))) {
                    if (is_file_exists(CustomerOtherAddress::$directory_image.$other_address->image_ktp)) {
                        remove_file(CustomerOtherAddress::$directory_image.$other_address->image_ktp);
                    }

                    $other_address->image_ktp = UploadMedia::image($request->file('image_ktp'), CustomerOtherAddress::$directory_image);
                }


                if ($other_address->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id, $address_id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        if ($request->ajax()) {
            $customer = Customer::find($id);
            $other_address = CustomerOtherAddress::find($address_id);

            if ($customer === null OR $other_address === null) {
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
