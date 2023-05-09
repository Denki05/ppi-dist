<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\ContactTable;
use App\Entities\Master\Contact;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Vendor;
use App\Exports\Master\ContactExport;
use App\Exports\Master\ContactImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\ContactImport;
use App\Entities\Setting\UserMenu;
use DB;
use Excel;
use Illuminate\Http\Request;
use Validator;
use Auth;

class ContactController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.contact";
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
    public function json(Request $request, ContactTable $datatable)
    {
        return $datatable->build();
    }

    public function get_store(Request $request)
    {
        $store = Customer::all()->pluck('name', 'id');

        return response()->json($store);
    }

    public function get_member(Request $request)
    {
        $member = CustomerOtherAddress::all()->pluck('name', 'id');

        return response()->json($member);
    }

    public function get_vendor(Request $request)
    {
       $vendor = Vendor::all()->pluck('name', 'id');

       return response()->json($vendor);
    }

    public function index()
    {
       // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        return view('superuser.master.contact.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view('superuser.master.contact.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'nullable|email',
                'position' => 'nullable',
                'dob' => 'nullable|date|date_format:d-m-Y',
                'npwp' => 'nullable',
                'ktp' => 'nullable',
                'address' => 'nullable'
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

                $contact = new Contact();

                $contact->name = $request->name;
                $contact->phone = $request->phone;
                $contact->email = $request->email;
                $contact->position = $request->position;
                $contact->dob = ($request->dob != null) ? date('Y-m-d', strtotime($request->dob)) : null;
                $contact->npwp = $request->npwp;
                $contact->ktp = $request->ktp;
                $contact->address = $request->address;
                $contact->status = Contact::STATUS['ACTIVE'];

                if ($contact->save()) {
                    
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.contact.show', $contact->id);

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

        $data['contact'] = Contact::findOrFail($id);

        return view('superuser.master.contact.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['contact'] = Contact::findOrFail($id);

        return view('superuser.master.contact.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $contact = Contact::find($id);

            if ($contact == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'nullable|email',
                'position' => 'nullable',
                'dob' => 'nullable|date|date_format:d-m-Y',
                'npwp' => 'nullable',
                'ktp' => 'nullable',
                'address' => 'nullable'
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

                $contact->name = $request->name;
                $contact->phone = $request->phone;
                $contact->email = $request->email;
                $contact->position = $request->position;
                $contact->dob = ($request->dob != null) ? date('Y-m-d', strtotime($request->dob)) : null;
                $contact->npwp = $request->npwp;
                $contact->ktp = $request->ktp;
                $contact->address = $request->address;

                if ($contact->save()) {
                    DB::commit();
                    
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.contact.index');

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
            $contact = Contact::find($id);

            if ($contact === null) {
                abort(404);
            }

            $contact->status = Contact::STATUS['DELETED'];

            if ($contact->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-contact-import-template.xlsx';
        return Excel::download(new ContactImportTemplate, $filename);
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
            Excel::import(new ContactImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-contact-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new ContactExport, $filename);
    }
}
