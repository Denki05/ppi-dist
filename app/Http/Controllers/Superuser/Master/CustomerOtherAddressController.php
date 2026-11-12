<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\DataTables\Master\CustomerOtherAddressTable;
use App\Exports\Master\CustomerOtherAddressImportTemplate;
use App\Exports\Master\CustomerOtherAddressImportTemplate2;
use App\Imports\Master\CustomerOtherAddressImport;
use App\Imports\Master\CustomerOtherAddressImport2;
use App\Exports\Master\CustomerOtherAddressExport;
use App\Http\Controllers\Controller;
use App\Jobs\SyncCustomerToTrial;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Excel;
use Auth;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Models\Zipcode;
use App\Helper\UploadMedia;
use App\Helper\LogActivity;
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
                'pic' => 'nullable|string|max:255',
                'sm_am' => 'nullable|string|max:255',
                'asm' => 'nullable|string|max:255',
                'officer' => 'nullable|string|max:255',
                'ar1' => 'nullable|string|max:255',
                'ar2' => 'nullable|string|max:255',
                'ar3' => 'nullable|string|max:255',
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

                $salesColumns = CustomerOtherAddress::salesColumnMap();
                $salesInputMap = [
                    'member_sm'      => 'sm_am',
                    'member_asm'     => 'asm',
                    'member_pic'     => 'pic',
                    'member_officer' => 'officer',
                    'member_ar1'     => 'ar1',
                    'member_ar2'     => 'ar2',
                    'member_ar3'     => 'ar3',
                ];

                $missingColumns = [];
                foreach ($salesInputMap as $columnKey => $inputName) {
                    if (empty($salesColumns[$columnKey]) && $request->filled($inputName)) {
                        $missingColumns[] = $inputName;
                    }
                }

                if (!empty($missingColumns)) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Kolom database untuk field berikut tidak ditemukan: ' . implode(', ', array_unique($missingColumns)) . '. Periksa struktur tabel master member.',
                    ];

                    return $this->response(400, $response);
                }

                $salesValue = function ($inputName) use ($request) {
                    $input = trim((string) $request->input($inputName));

                    return $input === '' ? null : $input;
                };

                $other_address = new CustomerOtherAddress;

                $other_address->id = $customer->id.'.'.$kd;
                $other_address->customer_id = $customer->id;
                $other_address->member_default = 0;

                foreach ($salesInputMap as $columnKey => $inputName) {
                    if (!empty($salesColumns[$columnKey])) {
                        $other_address->{$salesColumns[$columnKey]} = $salesValue($inputName);
                    }
                }

                $other_address->name = $request->name;
                $other_address->contact_person = $request->contact_person;
                $other_address->npwp = implode("/", [$request->name_card_npwp,$request->npwp]);
                $other_address->ktp = implode("/", [$request->name_card_ktp,$request->ktp]);
                $other_address->phone = $request->phone;
                $other_address->address = $request->address;
                $other_address->free_shipping = $request->free_shipping;
                $other_address->setting_income_target = ($request->target_income) ? $request->target_income : 0;

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
                $other_address->zone = $request->zone;

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

                    SyncCustomerToTrial::dispatch($customer->id);

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
        $data['sales_columns'] = CustomerOtherAddress::salesColumnMap();

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
                'sm_am' => 'nullable|string|max:255',
                'asm' => 'nullable|string|max:255',
                'pic' => 'nullable|string|max:255',
                'officer' => 'nullable|string|max:255',
                'ar1' => 'nullable|string|max:255',
                'ar2' => 'nullable|string|max:255',
                'ar3' => 'nullable|string|max:255',
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

                $salesColumns = CustomerOtherAddress::salesColumnMap();
                $salesInputMap = [
                    'member_sm'      => 'sm_am',
                    'member_asm'     => 'asm',
                    'member_pic'     => 'pic',
                    'member_officer' => 'officer',
                    'member_ar1'     => 'ar1',
                    'member_ar2'     => 'ar2',
                    'member_ar3'     => 'ar3',
                ];

                $missingColumns = [];
                foreach ($salesInputMap as $columnKey => $inputName) {
                    if (empty($salesColumns[$columnKey]) && $request->filled($inputName)) {
                        $missingColumns[] = $inputName;
                    }
                }

                if (!empty($missingColumns)) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Kolom database untuk field berikut tidak ditemukan: ' . implode(', ', array_unique($missingColumns)) . '. Periksa struktur tabel master member.',
                    ];

                    return $this->response(400, $response);
                }

                $value = function ($key) use ($request) {
                    $input = $request->input($key);

                    return ($input === null) ? null : trim((string) $input);
                };

                DB::beginTransaction();

                try {
                    $other_address->name = $request->name;
                    $other_address->customer_id = $other_address->customer_id;
                    $other_address->contact_person = $request->contact_person;
                    $other_address->npwp = implode("/", [$request->name_card_npwp,$request->npwp]);
                    $other_address->ktp = implode("/", [$request->name_card_ktp,$request->ktp]);
                    $other_address->phone = $request->phone;
                    $other_address->address = $request->address;
                    $other_address->free_shipping = $request->free_shipping;
                    $other_address->setting_income_target = ($request->target_income) ? $request->target_income : 0;

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
                    $other_address->zone = $request->zone;

                    $other_address->zipcode = $request->zipcode;

                    if ($salesColumns['member_sm']) {
                        $other_address->{$salesColumns['member_sm']} = $value('sm_am');
                    }
                    if ($salesColumns['member_asm']) {
                        $other_address->{$salesColumns['member_asm']} = $value('asm');
                    }
                    if ($salesColumns['member_pic']) {
                        $other_address->{$salesColumns['member_pic']} = $value('pic');
                    }
                    if ($salesColumns['member_officer']) {
                        $other_address->{$salesColumns['member_officer']} = $value('officer');
                    }
                    if ($salesColumns['member_ar1']) {
                        $other_address->{$salesColumns['member_ar1']} = $value('ar1');
                    }
                    if ($salesColumns['member_ar2']) {
                        $other_address->{$salesColumns['member_ar2']} = $value('ar2');
                    }
                    if ($salesColumns['member_ar3']) {
                        $other_address->{$salesColumns['member_ar3']} = $value('ar3');
                    }

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

                    if (!$other_address->save()) {
                        throw new \Exception('Failed to save member');
                    }

                    // Member default menjaga data store tetap sama.
                    if ((int) $other_address->member_default === CustomerOtherAddress::MEMBER_DEFAULT['YES']) {
                        $customerData = [];

                        if ($salesColumns['customer_sm']) {
                            $customerData[$salesColumns['customer_sm']] = $value('sm_am');
                        }
                        if ($salesColumns['customer_asm']) {
                            $customerData[$salesColumns['customer_asm']] = $value('asm');
                        }
                        if ($salesColumns['customer_pic']) {
                            $customerData[$salesColumns['customer_pic']] = $value('pic');
                        }

                        if (!empty($customerData)) {
                            $customerData['updated_at'] = now();

                            DB::table('master_customers')
                                ->where('id', $other_address->customer_id)
                                ->update($customerData);
                        }
                    }

                    DB::commit();

                    SyncCustomerToTrial::dispatch($other_address->customer_id);

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer.index');

                    return $this->response(200, $response);
                } catch (\Exception $e) {
                    DB::rollback();
                    \Log::error('Update member failed: ' . $e->getMessage());

                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Gagal menyimpan data member. Silakan coba lagi.',
                    ];

                    return $this->response(400, $response);
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
                LogActivity::addToLog('Deleted Member: ' . $other_address->name);
                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-customer-other-address-import-template.xlsx';
        return Excel::download(new CustomerOtherAddressImportTemplate, $filename);
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
            $import = new CustomerOtherAddressImport();
            Excel::import($import, $request->import_file);
        
            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }

    public function inactive_member(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $member = CustomerOtherAddress::find($id);

        if ($member === null) {
            abort(404);
        }

        $member->situation = CustomerOtherAddress::SITUATION['INACTIVE'];

        if ($member->save()) {
            return redirect()->route('superuser.master.customer.index')->with('success', 'Member successfully Inactive.');
        } else {
            return redirect()->route('superuser.master.customer.index')->with('error', 'Failed to Inactive member.');
        }
    }

    public function active_member(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $member = CustomerOtherAddress::find($id);

        if ($member === null) {
            abort(404);
        }

        $member->situation = CustomerOtherAddress::SITUATION['ACTIVE'];

        if ($member->save()) {
            return redirect()->route('superuser.master.customer.index')->with('success', 'Member successfully Active.');
        } else {
            return redirect()->route('superuser.master.customer.index')->with('error', 'Failed to Active member.');
        }
    }

    public function disabled_member(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $member = CustomerOtherAddress::find($id);

        if ($member === null) {
            abort(404);
        }

        $member->status_key = CustomerOtherAddress::STATUS_KEY['DISABLED'];

        if ($member->save()) {
            return redirect()->route('superuser.master.customer.index')->with('success', 'Member successfully disabled.');
        } else {
            return redirect()->route('superuser.master.customer.index')->with('error', 'Failed to disable member.');
        }
    }

    public function enabled_member(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $member = CustomerOtherAddress::find($id);

        if ($member === null) {
            abort(404);
        }

        $member->status_key = CustomerOtherAddress::STATUS_KEY['ENABLE'];

        // dd()

        if ($member->save()) {
            return redirect()->route('superuser.master.customer.index')->with('success', 'Member successfully Enabled.');
        } else {
            return redirect()->route('superuser.master.customer.index')->with('error', 'Failed to Enabled member.');
        }
    }

    public function export()
    {
        $status = '1';
        $existence = '1';
        $filename = 'master-member-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new CustomerOtherAddressExport($status, $existence), $filename);
    }

    public function import_template2()
    {
        $filename = 'master-customer-other-address-import-template-2.xlsx';
        return Excel::download(new CustomerOtherAddressImportTemplate2, $filename);
    }

    public function import2(Request $request)
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
            $import = new CustomerOtherAddressImport2();
            Excel::import($import, $request->import_file);
        
            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }
}