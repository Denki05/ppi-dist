<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Product;
use App\Entities\Master\ProductMinStock;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class ProductMinStockController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.product_min_stock";
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
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['product'] = Product::findOrFail($id);
        $data['units'] = MasterRepo::units();
        $data['warehouses'] = MasterRepo::warehouses();

        return view('superuser.master.product_min_stock.create', $data);
    }

    public function store(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|numeric',
                'unit' => 'required|integer',
                'warehouse' => 'required|integer',
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

                $exists = ProductMinStock::where([
                    'product_id' => $product->id,
                    'warehouse_id' => $request->warehouse,
                ])->first();
        
                if ($exists) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => [
                            'Min Stock already exists',
                            '',
                            "Product : [{$exists->product->code}] {$exists->product->name}",
                            "Warehouse : {$exists->warehouse->name}",
                            "Quantity : {$exists->quantity} {$exists->unit->abbreviation}"
                        ],
                    ];

                    return $this->response(400, $response);
                }

                $min_stock = new ProductMinStock;

                $min_stock->product_id = $product->id;
                $min_stock->warehouse_id = $request->warehouse;
                $min_stock->quantity = $request->quantity;
                $min_stock->unit_id = $request->unit;

                if ($min_stock->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product.show', $id);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id, $min_stock_id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['product'] = Product::findOrFail($id);
        $data['min_stock'] = ProductMinStock::findOrFail($min_stock_id);
        $data['units'] = MasterRepo::units();
        $data['warehouses'] = MasterRepo::warehouses();

        return view('superuser.master.product_min_stock.edit', $data);
    }

    public function update(Request $request, $id, $min_stock_id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|numeric',
                'unit' => 'required|integer',
                'warehouse' => 'required|integer',
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
                $min_stock = ProductMinStock::find($min_stock_id);

                if ($product == null OR $min_stock == null) {
                    abort(404);
                }

                $exists = ProductMinStock::where([
                    'product_id' => $product->id,
                    'warehouse_id' => $request->warehouse,
                ])->where('id', '!=', $min_stock_id)->first();
        
                if ($exists) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => [
                            'Min Stock already exists',
                            '',
                            "Product : [{$exists->product->code}] {$exists->product->name}",
                            "Warehouse : {$exists->warehouse->name}",
                            "Quantity : {$exists->quantity} {$exists->unit->abbreviation}"
                        ],
                    ];

                    return $this->response(400, $response);
                }

                $min_stock->warehouse_id = $request->warehouse;
                $min_stock->quantity = $request->quantity;
                $min_stock->unit_id = $request->unit;

                if ($min_stock->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product.show', $id);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id, $min_stock_id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        if ($request->ajax()) {
            $product = Product::find($id);
            $min_stock = ProductMinStock::find($min_stock_id);

            if ($product === null OR $min_stock === null) {
                abort(404);
            }

            if ($min_stock->delete()) {
                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }
}
