<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\CustomerTable;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerSaldoLog;
use App\Entities\Master\CustomerType;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\CustomerCategoryType;
use App\Entities\Master\CustomerCategory;
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
Use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Models\Zipcode;
use PDF;
use COM;

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

    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $search = $request->input('search');
        $filter_customer = $request->input('customer_name');
        $customer = Customer::where(function($query2) use($search){
                                if(!empty($search)){
                                    $query2->where('name','like','%'.$search.'%');
                                }
                            })  
                            ->orderBy('id','ASC')
                            ->paginate(5);

        $other_address = CustomerOtherAddress::get();
        
        $data =[
            'other_address' => $other_address,
            'customers' => $customer,
        ];

        return view('superuser.master.customer.index', $data);
    }

    // public function cari(Request $request)
	// {
	// 	// menangkap data pencarian
	// 	$cari = $request->cari;
 
    // 		// mengambil data dari table pegawai sesuai pencarian data
	// 	$data['cari'] = DB::table('tbl_customers')
	// 	->where('name','like',"%".$cari."%")
	// 	->paginate();
 
    // 		// mengirim data pegawai ke view index
    //         return view('superuser.master.customer.index', $data);
 
	// }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // $data['customer_categories'] = MasterRepo::customer_categories();
        $data['customer_types'] = MasterRepo::customer_types();
        $data['provinces'] = Province::all();
        $data['category'] = CustomerCategory::all();

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

    public function getcustomertype(request $request)
    {
        $cat_id = $request->category_id;

        $customer_type = CustomerCategoryType::select(
                                                        'master_customer_types.id AS typeID',
                                                        'master_customer_types.code AS typeCode', 
                                                        'master_customer_types.name AS typeName',
                                                        'master_customer_category_types.type_id', 
                                                        'master_customer_category_types.category_id',
                                                    )
                ->leftJoin('master_customer_types', 'master_customer_types.id', '=', 'master_customer_category_types.type_id')
                ->where('category_id', $cat_id)
                ->get();
        foreach ($customer_type as $row){
            echo "<option value='$row->type_id'>$row->typeName</option>";
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
                'ktp' => 'nullable|string',
                'address' => 'required|string',
                'owner_name' => 'nullable|string',
                'website' => 'nullable|string',
                'plafon_piutang' => 'nullable|numeric',
                // 'gps_latitude' => 'nullable|string',
                // 'gps_longitude' => 'nullable|string',
                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',
                // 'image_store' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image_ktp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image_npwp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
                // $customer->type_id = $request->type;

                $customer->email = $request->email;
                $customer->phone = $request->phone;
                $customer->npwp = $request->npwp;
                $customer->ktp = $request->ktp;
                $customer->address = $request->address;

                $customer->owner_name = $request->owner_name;
                $customer->website = $request->website;
                $customer->plafon_piutang = ($request->plafon_piutang) ? $request->plafon_piutang : 0;
                $customer->saldo = $request->plafon_piutang;
                $customer->has_ppn = 0;

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

                if (!empty($request->file('image_ktp'))) {
                    $customer->image_ktp = UploadMedia::image($request->file('image_ktp'), Customer::$directory_image);
                }

                if (!empty($request->file('image_npwp'))) {
                    $customer->image_npwp = UploadMedia::image($request->file('image_npwp'), Customer::$directory_image);
                }

                if (!empty($request->file('image_store'))) {
                    $customer->image_store = UploadMedia::image($request->file('image_store'), Customer::$directory_image);
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

                        $log_saldo = new CustomerSaldoLog;
                        $log_saldo->customer_id = $customer->id;
                        $log_saldo->saldo_log = $request->plafon_piutang;
                        $log_saldo->note = CustomerSaldoLog::NOTE['SALDO AWAL'];
                        $log_saldo->save();
                    

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
        // $data['saldo'] = Customer::find(1)->saldo()->first($id);

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
                'type' => 'required|array',
                'type.*' => 'required|integer',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'npwp' => 'nullable|string',
                'ktp' => 'nullable|string',
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
                'image_npwp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image_ktp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image_store' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
                // $customer->type_id = $request->type;

                $customer->email = $request->email;
                $customer->phone = $request->phone;
                $customer->npwp = $request->npwp;
                $customer->ktp = $request->ktp;
                $customer->address = $request->address;

                $customer->owner_name = $request->owner_name;
                $customer->website = $request->website;
                $customer->plafon_piutang = ($request->plafon_piutang) ? $request->plafon_piutang : 0;
                $customer->saldo = $request->plafon_piutang;
                $customer->has_ppn = 0;

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

                if (!empty($request->file('image_npwp'))) {
                    if (is_file_exists(Customer::$directory_image.$customer->image_npwp)) {
                        remove_file(Customer::$directory_image.$customer->image_npwp);
                    }

                    $customer->image_npwp = UploadMedia::image($request->file('image_npwp'), Customer::$directory_image);
                }

                if (!empty($request->file('image_ktp'))) {
                    if (is_file_exists(Customer::$directory_image.$customer->image_ktp)) {
                        remove_file(Customer::$directory_image.$customer->image_ktp);
                    }

                    $customer->image_ktp = UploadMedia::image($request->file('image_ktp'), Customer::$directory_image);
                }
                
                if (!empty($request->file('image_store'))) {
                    if (is_file_exists(Customer::$directory_image.$customer->image_store)) {
                        remove_file(Customer::$directory_image.$customer->image_store);
                    }

                    $customer->image_store = UploadMedia::image($request->file('image_store'), Customer::$directory_image);
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

                    $log_saldo = new CustomerSaldoLog;
                    $log_saldo->customer_id = $customer->id;
                    $log_saldo->saldo_log = $request->plafon_piutang;
                    $log_saldo->note = CustomerSaldoLog::NOTE['SALDO UPDATE'];
                    $log_saldo->save();

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

    public function export_customer(Request  $id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
            $customer = Customer::find($id);
    
            if ($customer === null) {
                abort(404);
            }

            //- Variables - for your RPT and PDF
            // echo "Export rpt Crystal Report ON pdf";
            $my_report = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\customer_detail2.rpt"; 
            $my_pdf = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\export\\customer_detail2.pdf";
    
            //- Variables - Server Information 
            $my_server = "SERVER_PPI_DIST"; 
            $my_user = "ppi_report"; 
            $my_password = "Denki@05121996"; 
            $my_database = "ppi-dist";
            $COM_Object = "CrystalDesignRunTime.Application";
    
            $crapp= New COM($COM_Object) or die("Unable to Create Object");
            $creport = $crapp->OpenReport($my_report, 1);
    
            //- Set database logon info - must have 
            $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);
    
            //------ Put the values that you want -------- 
            // $creport->RecordSelectionFormula="{idCust.storeID}='$customer'";
    
            //- field prompt or else report will hang - to get through 
            $creport->EnableParameterPrompting = 0;
            
            //------ DiscardSavedData make a Refresh in your data -------
            $creport->DiscardSavedData;
            $creport->ReadRecords();
    
            //------ Pass formula fields --------
            $creport->ParameterFields(1)->SetCurrentValue(275); // <-- param 1
    
            //export to PDF process
            $creport->ExportOptions->DiskFileName=$my_pdf; //export to pdf
            $creport->ExportOptions->PDFExportAllPages=true;
            $creport->ExportOptions->DestinationType=1; // export to file
            $creport->ExportOptions->FormatType=31; // PDF type
            $creport->Export(false);
    
            //------ Release the variables ------
            $creport = null;
            $crapp = null;
            $ObjectFactory = null;
    
            //------ Embed the report in the webpage ------
            // print "<embed src=\"C:\\xampp\\htdocs\\ppi-manage\\report\\export\\product_list.pdf\" width=\"100%\" height=\"100%\">"
    
    
            $file = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\export\\customer_detail2.pdf"; 
    
            header("Content-Description: File Transfer"); 
            header("Content-Type: application/octet-stream"); 
            header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
    
            readfile ($file);
            exit();
    }
}
