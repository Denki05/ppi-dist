<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Company;
use App\Entities\Master\Warehouse;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use App\Repositories\CodeRepo;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class CompanyController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.company";
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
    public function show()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['company'] = Company::find(1);
        return view('superuser.master.company.show', $data);
    }

    public function edit()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['company'] = Company::find(1);
        return view('superuser.master.company.edit', $data);
    }

    public function update(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
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
                'phone' => 'nullable|string',
                'website' => 'nullable|string',
                'owner_name' => 'nullable|string',
                'note' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
                $company = Company::find(1);

                if ($company === null) {
                    $company = new Company;
                }
                
                $company->name = $request->name;
                $company->address = $request->address;
                $company->provinsi = $request->provinsi;
                $company->kota = $request->kota;
                $company->kecamatan = $request->kecamatan;
                $company->kelurahan = $request->kelurahan;
                $company->text_provinsi = $request->text_provinsi;
                $company->text_kota = $request->text_kota;
                $company->text_kecamatan = $request->text_kecamatan;
                $company->text_kelurahan = $request->text_kelurahan;
                $company->zipcode = $request->zipcode;
                $company->phone = $request->phone;
                $company->website = $request->website;
                $company->owner_name = $request->owner_name;
                $company->note = $request->note;

                if (!empty($request->file('logo'))) {
                    if (is_file_exists(Company::$directory_image.$company->logo)) {
                        remove_file(Company::$directory_image.$company->logo);
                    }

                    $company->logo = UploadMedia::image($request->file('logo'), Company::$directory_image);
                }

                if ($company->save()) {
                    // check for exists warehouse head office
                    $exists_wh_headoffice = Warehouse::where([
                        'type' => Warehouse::TYPE['HEAD_OFFICE'],
                        'status' => Warehouse::STATUS['ACTIVE']
                    ])->first();

                    if (! $exists_wh_headoffice) {
                        $wh_head_office = new Warehouse;

                        $wh_head_office->type = Warehouse::TYPE['HEAD_OFFICE'];
                        $wh_head_office->code = CodeRepo::generateWarehouse();
                        $wh_head_office->name = 'Warehouse Head Office';
                        $wh_head_office->status = Warehouse::STATUS['ACTIVE'];

                        $wh_head_office->save();
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.company.show');

                    return $this->response(200, $response);
                }
            }
        }
    }
}
