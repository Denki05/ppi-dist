<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\CustomerCategory;
use App\Entities\Master\CustomerCategoryType;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class CustomerCategoryTypeController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.customer_category_type";
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
        $data['customer_category'] = CustomerCategory::findOrFail($id);
        $data['customer_types'] = MasterRepo::customer_types();

        return view('superuser.master.customer_category.type', $data);
    }

    public function add(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $customer_category = CustomerCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        $exists = CustomerCategoryType::where([
            'category_id' => $customer_category->id,
            'type_id' => $request->type,
        ])->first();

        if ($exists) {
            return redirect()->back()->withErrors(['Type already exists']);
        }

        $type = new CustomerCategoryType;

        $type->category_id = $customer_category->id;
        $type->type_id = $request->type;

        $type->save();
        
        return redirect()->back();
    }

    public function remove($id, $type_id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $customer_category = CustomerCategory::findOrFail($id);
        $type = CustomerCategoryType::findOrFail($type_id);

        $type->delete();

        return redirect()->back();
    }
}
