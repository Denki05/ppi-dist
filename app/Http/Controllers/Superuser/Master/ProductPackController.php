<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Http\Controllers\Controller;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\Entities\Master\Product;
use App\Entities\Master\ProductType;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\BrandLokal;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use App\Repositories\MasterRepo;
use Validator;
use Auth;
use DB;

class ProductPackController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.product_pack";
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

    public function create($id)
    {
        $data['product'] = Product::find($id);
        $data['type'] = ProductType::where('status', ProductType::STATUS['ACTIVE'])->get();
        $data['packaging'] = Packaging::get();
        $data['brand_ppi'] = BrandLokal::get();
        $data['sub_brand_references'] = MasterRepo::sub_brand_references();
        $data['category'] = ProductCategory::get();

        return view('superuser.master.product_pack.create', $data);
    }

    public function store(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'selling_price' => 'required|numeric',
                'packaging' => 'required|integer',
                'type' => 'required|integer',
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
                $product = Product::find($id);

                if ($product == null) {
                    abort(404);
                }
                
                // search product same
                $new_pack = new ProductPack;

                $count_pack = Product::where('id', $product->id)
                        ->selectRaw('count_pack as kode')
                        ->get();

                $kd = "";

                foreach($count_pack AS $value){
                    $tmp = ((int) $value->kode)+1;
                    $kd = sprintf("%01s", $tmp);
                }

                $new_pack->id = $request->code.'-'.$request->packaging.'_'.$kd;
                $new_pack->product_id = $product->id;
                $new_pack->warehouse_id = 2;
                $new_pack->packaging_id = $request->packaging;
                $new_pack->type_id = $request->type;
                $new_pack->material_code = $request->material_code;
                $new_pack->material_name = $request->material_name;
                $new_pack->code = $request->code;
                $new_pack->name = $request->name;
                $new_pack->price = $request->selling_price;
                $new_pack->gender = $request->gender;
                $new_pack->gender = $request->gender;
                $new_pack->note = $request->note ?? null;
                $new_pack->status = ProductPack::STATUS['ACTIVE'];
                $new_pack->condition = 0;
                if($new_pack->save()){
                    // update count product
                    $updateProduct = Product::where('id', $product->id)->update([
                        'count_pack' => $kd
                    ]);

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product.show', base64_encode($product->id));

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id, $pack_id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $decode_product = base64_decode($id);
        $decode_pack = base64_decode($pack_id);

        $data['product'] = Product::find($decode_product);
        $data['product_pack'] = ProductPack::find($decode_pack);
        $data['type'] = ProductType::where('status', 1)->get();
        $data['packaging'] = Packaging::get();

        return view('superuser.master.product_pack.edit', $data);
    }

    public function update(Request $request, $id, $pack_id)
    {
        if ($request->ajax()) {

            $product_pack = ProductPack::find($pack_id);

            if ($product_pack == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'selling_price' => 'required',
                'type' => 'required',
                // 'pacakging' => 'required',
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

                try{

                    $product_pack->price = $request->selling_price;
                    $product_pack->type_id = $request->type;
                    $product_pack->packaging_id = $request->packaging;
                    if ($product_pack->save()) {

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.master.product.index');

                        return $this->response(200, $response);
                    }

                }catch (\Exception $e) {
                    DD($e);
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => "Internal Server Error",
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }
}