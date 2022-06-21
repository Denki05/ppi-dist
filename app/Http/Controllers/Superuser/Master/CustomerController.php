<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\CustomerTable;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerTypePivot;
use App\Exports\Master\CustomerExport;
use App\Exports\Master\CustomerImportTemplate;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use App\Imports\Master\CustomerImport;
use App\Repositories\CodeRepo;
use App\Repositories\MasterRepo;
use DB;
use Excel;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Models\Zipcode;

class CustomerController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.customer";
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
    public function json(Request $request, CustomerTable $datatable)
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
        return view('superuser.master.customer.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['customer_categories'] = MasterRepo::customer_categories();
        $data['customer_types'] = MasterRepo::customer_types();
        $data['store'] = MasterRepo::store();
		$data['provinces'] = Province::all();

        return view('superuser.master.customer.create', $data);
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

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_customers,code',
                'name' => 'required|string',
                'category' => 'required|integer',
                'type' => 'required|array',
                'type.*' => 'required|integer',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'npwp' => 'nullable|string',
                'address' => 'required|string',
                // 'owner_name' => 'nullable|string',
                'website' => 'nullable|string',
                'plafon_piutang' => 'nullable|numeric',
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
                'image_store' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image_ktp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'notification_email' => 'nullable'
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

                $customer = new Customer;

                $customer->code = CodeRepo::generateCustomer();
                $customer->name = $request->name;

                $customer->category_id = $request->category;
                $customer->store_id = $request->store;
                // $customer->type_id = $request->type;

                $customer->email = $request->email;
                $customer->phone = $request->phone;
                $customer->npwp = $request->npwp;
                $customer->address = $request->address;

                // $customer->owner_name = $request->owner_name;
                $customer->website = $request->website;
                $customer->plafon_piutang = ($request->plafon_piutang) ? $request->plafon_piutang : 0;

                $customer->gps_latitude = $request->gps_latitude;
                $customer->gps_longitude = $request->gps_longitude;

                $customer->provinsi = $request->provinsi;
                $customer->kota = $request->kota;
                $customer->kecamatan = $request->kecamatan;
                $customer->kelurahan = $request->kelurahan;
                $customer->text_provinsi = $request->text_provinsi;
                $customer->text_kota = $request->text_kota;
                $customer->text_kecamatan = $request->text_kecamatan;
                $customer->text_kelurahan = $request->text_kelurahan;

                $customer->zipcode = $request->zipcode;

                if (!empty($request->file('image_store'))) {
                    $customer->image_store = UploadMedia::image($request->file('image_store'), Customer::$directory_image);
                }

                if (!empty($request->file('image_ktp'))) {
                    $customer->image_ktp = UploadMedia::image($request->file('image_ktp'), Customer::$directory_image);
                }

                $customer->notification_email = ($request->notification_email) ? true : false;
                $customer->status = Customer::STATUS['ACTIVE'];

                if ($customer->save()) {
                    foreach ($request->type as $type) {
                        $customer_type_pivot = new CustomerTypePivot;
                        $customer_type_pivot->customer_id = $customer->id;
                        $customer_type_pivot->type_id = $type;
    
                        $customer_type_pivot->save();
                    }

                    DB::commit();

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

    public function show($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $data['customer'] = Customer::findOrFail($id);

        return view('superuser.master.customer.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['customer'] = Customer::findOrFail($id);
        $data['customer_categories'] = MasterRepo::customer_categories();
        $data['customer_types'] = MasterRepo::customer_types();
        $data['store'] = MasterRepo::store();
        $data['provinces'] = Province::all();

        return view('superuser.master.customer.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $customer = Customer::find($id);

            if ($customer == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_customers,code,' . $customer->id,
                'name' => 'required|string',
                'category' => 'required|integer',
                'store' => 'required|integer',
                'type' => 'required|array',
                'type.*' => 'required|integer',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'npwp' => 'nullable|string',
                'address' => 'required|string',
                'owner_name' => 'nullable|string',
                'website' => 'nullable|string',
                'plafon_piutang' => 'nullable|numeric',
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
                'image_store' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image_ktp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'notification_email' => 'nullable'
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

                // $customer->code = $request->code;
                $customer->name = $request->name;

                $customer->category_id = $request->category;
                $customer->store_id = $request->store;
                // $customer->type_id = $request->type;

                $customer->email = $request->email;
                $customer->phone = $request->phone;
                $customer->npwp = $request->npwp;
                $customer->address = $request->address;

                // $customer->owner_name = $request->owner_name;
                $customer->website = $request->website;
                $customer->plafon_piutang = ($request->plafon_piutang) ? $request->plafon_piutang : 0;

                $customer->gps_latitude = $request->gps_latitude;
                $customer->gps_longitude = $request->gps_longitude;

                $customer->provinsi = $request->provinsi;
                $customer->kota = $request->kota;
                $customer->kecamatan = $request->kecamatan;
                $customer->kelurahan = $request->kelurahan;
                $customer->text_provinsi = $request->text_provinsi;
                $customer->text_kota = $request->text_kota;
                $customer->text_kecamatan = $request->text_kecamatan;
                $customer->text_kelurahan = $request->text_kelurahan;

                $customer->zipcode = $request->zipcode;

                if (!empty($request->file('image_store'))) {
                    if (is_file_exists(Customer::$directory_image.$customer->image_store)) {
                        remove_file(Customer::$directory_image.$customer->image_store);
                    }

                    $customer->image_store = UploadMedia::image($request->file('image_store'), Customer::$directory_image);
                }

                if (!empty($request->file('image_ktp'))) {
                    if (is_file_exists(Customer::$directory_image.$customer->image_ktp)) {
                        remove_file(Customer::$directory_image.$customer->image_ktp);
                    }

                    $customer->image_ktp = UploadMedia::image($request->file('image_ktp'), Customer::$directory_image);
                }

                $customer->notification_email = ($request->notification_email) ? true : false;

                if ($customer->save()) {
                    $customer->types()->detach();
                    foreach ($request->type as $type) {
                        $customer_type_pivot = new CustomerTypePivot;
                        $customer_type_pivot->customer_id = $customer->id;
                        $customer_type_pivot->type_id = $type;
    
                        $customer_type_pivot->save();
                    }

                    DB::commit();

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

    public function destroy(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        if ($request->ajax()) {
            $customer = Customer::find($id);

            if ($customer === null) {
                abort(404);
            }

            $customer->status = Customer::STATUS['DELETED'];

            if ($customer->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-customer-import-template.xlsx';
        return Excel::download(new CustomerImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            Excel::import(new CustomerImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-customer-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new CustomerExport, $filename);
    }
}
