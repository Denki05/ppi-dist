<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\DataTables\Master\WarehouseTable;
use App\Entities\Gudang\StockSalesOrder;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Product;
use App\Http\Controllers\Controller;
use App\Repositories\CodeRepo;
use App\Repositories\MasterRepo;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class StockSalesOrderController extends Controller
{
    public function __construct(){
        $this->route = "superuser.gudang.stock_sales_order";
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
    public function json(Request $request, WarehouseTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        return view('superuser.gudang.stock_sales_order.index');
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $data['product'] = MasterRepo::products();
        $data['warehouses'] = MasterRepo::warehouses();

        return view('superuser.gudang.stock_sales_order.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|numeric',
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
                DB::beginTransaction();

                $exists = StockSalesOrder::where([
                    'product_id' => $request->product,
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

                $stock_sales = new StockSalesOrder();

                $stock_sales->product_id = $request->product;
                $stock_sales->warehouse_id = $request->warehouse;
                $stock_sales->quantity = $request->quantity;

                if ($stock_sales->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.stock_sales_order.index');

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
        $data['stock_sales'] = StockSalesOrder::findOrFail($id);

        return view('superuser.gudang.stock_sales_order.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $data['warehouse'] = Warehouse::findOrFail($id);
        // $data['branch_offices'] = MasterRepo::branch_offices();

        return view('superuser.master.warehouse.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $warehouse = Warehouse::find($id);

            if ($warehouse == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                // 'type' => 'required|integer',
                // 'branch_office' => Rule::requiredIf(function () use ($request) {
                //     return $request->type == Warehouse::TYPE['BRANCH_OFFICE'];
                // }),
                // 'code' => 'required|string|unique:master_warehouses,code,' . $warehouse->id,
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'description' => 'nullable|string',
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

                // $warehouse->type = $request->type;
                // $warehouse->branch_office_id = ($warehouse->type == Warehouse::TYPE['HEAD_OFFICE']) ? null : $request->branch_office;

                // $warehouse->code = $request->code;
                $warehouse->name = $request->name;
                $warehouse->contact_person = $request->contact_person;
                $warehouse->phone = $request->phone;
                $warehouse->address = $request->address;
                $warehouse->description = $request->description;

                if ($warehouse->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.warehouse.index');

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
            $warehouse = Warehouse::find($id);

            if ($warehouse === null) {
                abort(404);
            }

            // check for warehouse head office
            if ($warehouse->type == Warehouse::TYPE['HEAD_OFFICE']) {
                abort(400);
            }

            $warehouse->status = Warehouse::STATUS['DELETED'];

            if ($warehouse->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
