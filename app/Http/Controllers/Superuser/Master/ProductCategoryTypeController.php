<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\ProductCategory;
use App\Entities\Master\ProductCategoryType;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Validator;
use App\Entities\Setting\UserMenu;
use Auth;

class ProductCategoryTypeController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.product_category_type";
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

        $data['product_category'] = ProductCategory::findOrFail($id);
        $data['product_types'] = MasterRepo::product_types();

        return view('superuser.master.product_category.type', $data);
    }

    public function add(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $product_category = ProductCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        $exists = ProductCategoryType::where([
            'category_id' => $product_category->id,
            'type_id' => $request->type,
        ])->first();

        if ($exists) {
            return redirect()->back()->withErrors(['Type already exists']);
        }

        $type = new ProductCategoryType;

        $type->category_id = $product_category->id;
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
        $product_category = ProductCategory::findOrFail($id);
        $type = ProductCategoryType::findOrFail($type_id);

        $type->delete();

        return redirect()->back();
    }
}
