<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Customer;
use App\Entities\Master\CustomerContact;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class CustomerContactController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.customer_contact";
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
    public function manage($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['customer'] = Customer::findOrFail($id);
        $data['contacts'] = MasterRepo::contacts();

        return view('superuser.master.customer.contact', $data);
    }

    public function add(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'contact' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        $exists = CustomerContact::where([
            'customer_id' => $customer->id,
            'contact_id' => $request->contact,
        ])->first();

        if ($exists) {
            return redirect()->back()->withErrors(['Contact already exists']);
        }

        $contact = new CustomerContact;

        $contact->customer_id = $customer->id;
        $contact->contact_id = $request->contact;

        $contact->save();
        
        return redirect()->back();
    }

    public function remove($id, $contact_id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $customer = Customer::findOrFail($id);
        $contact = CustomerContact::findOrFail($contact_id);

        $contact->delete();

        return redirect()->back();
    }
}
