<?php

namespace App\Http\Controllers\Superuser\Master;

// use App\DataTables\Master\CustomerOtherAddressTable;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Dokumen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use App\Repositories\MasterRepo;
use Validator;
use Auth;
use App\Helper\UploadMedia;

class DokumenController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.dokumen";
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

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['customers'] = Customer::get(["id", "name"]);
        // $data['other_address'] = CustomerOtherAddress::all();
        
        // dd($data);
        return view('superuser.master.dokumen.create', $data);
    }

    

    public function getstore(request $request)
    {
        $customer_id = $request->customer_id;

        $members = CustomerOtherAddress::where('customer_id', $customer_id)->get();

        foreach ($members as $member){
            echo "<option value='$member->id'>$member->name</option>";
        }
    }

    // public function getkecamatan(request $request)
    // {
    //     $city_id = $request->city_id;

    //     $kecamatans = District::where('city_id', $city_id)->get();

    //     foreach ($kecamatans as $kecamatan){
    //         echo "<option value='$kecamatan->dis_id'>$kecamatan->dis_name</option>";
    //     }
    // }

    // public function getkelurahan(request $request)
    // {
    //     $dis_id = $request->dis_id;

    //     $kelurahans = Village::where('dis_id', $dis_id)->get();

    //     foreach ($kelurahans as $kelurahan){
    //         echo "<option value='$kelurahan->subdis_id'>$kelurahan->subdis_name</option>";
    //     }
    // }

    // public function getzipcode(request $request)
    // {
    //     $subdis_id = $request->subdis_id;

    //     $zipcodes = Zipcode::where('subdis_id', $subdis_id)->get();

    //     foreach ($zipcodes as $zipcode){
    //         echo "<option value='$zipcode->postal_code'>$zipcode->postal_code</option>";
    //     }
    // }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'document_type' => 'required|string',
                // 'other_address' => 'required|string',
                'document_number' => 'required|string',
                'name_person' => 'required|string',
                'address' => 'required|string',
                'image_npwp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image_ktp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
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

                // if ($customer == null) {
                //     abort(404);
                // }

                $dokumen = new Dokumen;

                $dokumen->customer_id = $request->customer;
                $dokumen->customer_other_address_id = $request->other_address;
                
                $dokumen->document_type = $request->document_type;
                $dokumen->document_number = $request->document_number;
                $dokumen->name_person = $request->name_person;
                $dokumen->address = $request->address;
                $dokumen->for_member = $request->for_member;

                if (!empty($request->file('image_ktp'))) {
                    $dokumen->image_ktp = UploadMedia::image($request->file('image_ktp'), Dokumen::$directory_image);
                }

                if (!empty($request->file('image_npwp'))) {
                    $dokumen->image_npwp = UploadMedia::image($request->file('image_npwp'), Dokumen::$directory_image);
                }

                // $other_address->status = CustomerOtherAddress::STATUS['ACTIVE'];
                
                if ($dokumen->save()) {
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

        $data['dokumen'] = Dokumen::findOrFail($id);

        return view('superuser.master.dokumen.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // $data['customers'] = MasterRepo::customers();
        $data['dokumen'] = Dokumen::findOrFail($id);
        $data['other_address'] = MasterRepo::CustomerOtherAddress();
        // $data['provinces'] = Province::all();

        // dd($data);
        return view('superuser.master.dokumen.edit', $data);
    }
    
    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'other_address' => 'required|string',
                'contact' => 'required|string',
                'npwp' => 'required|string',
                'ktp' => 'required|string',
                'image_npwp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image_ktp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
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
                $dokumen = Dokumen::find($id);

                if ($dokumen == null) {
                    abort(404);
                }

                $dokumen->customer_other_address_id = $request->other_address;
                
                $dokumen->name = $request->name;
                $dokumen->contact = $request->contact;
                $dokumen->npwp = $request->npwp;
                $dokumen->ktp = $request->ktp;

                if (!empty($request->file('image_npwp'))) {
                    if (is_file_exists(CustomerOtherAddress::$directory_image.$dokumen->image_npwp)) {
                        remove_file(CustomerOtherAddress::$directory_image.$dokumen->image_npwp);
                    }

                    $dokumen->image_npwp = UploadMedia::image($request->file('image_npwp'), Dokumen::$directory_image);
                }

                if (!empty($request->file('image_ktp'))) {
                    if (is_file_exists(CustomerOtherAddress::$directory_image.$dokumen->image_ktp)) {
                        remove_file(CustomerOtherAddress::$directory_image.$dokumen->image_ktp);
                    }

                    $dokumen->image_ktp = UploadMedia::image($request->file('image_ktp'), Dokumen::$directory_image);
                }


                if ($dokumen->save()) {

                    // dd($other_address);
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.dokumen.show', $dokumen->id);

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
            // $customer = Customer::find($id);
            $dokumen = Dokumen::find($id);

            if ($dokumen === null) {
                abort(404);
            }

            // $other_address->status = CustomerOtherAddress::STATUS['DELETED'];

            if ($dokumen->save()) {
                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }
}
