<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use App\Entities\Setting\UserMenu;
use App\Entities\Penjualan\SalesOrderProforma;
use App\Entities\Penjualan\SalesOrderProformaItem;
use App\Entities\Penjualan\SalesOrderProformaDetails;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Finance\Invoicing;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Vendor;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\Request;
use App\Repositories\CodeRepo;
use Validator;
use Auth;
use COM;
use DB;

class SalesOrderProformaController extends Controller
{
    public function __construct(){
        $this->view = "superuser.penjualan.so_proforma.";
        $this->route = "superuser.penjualan.so_proforma";
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

    public function search_sku(Request $request)
    {
        $products = ProductPack::leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->where('master_products.brand_name', $request->id)
            ->where('master_products_packaging.name', 'LIKE', '%'.$request->input('q', '').'%')
            ->where('master_products_packaging.status', ProductPack::STATUS['ACTIVE'])
            ->where('master_products_packaging.condition', ProductPack::CONDITION['ENABLE'])
            ->selectRaw("
                master_products_packaging.id, 
                CONCAT(master_products_packaging.code, ' - ', master_products_packaging.name, ' / ', master_packaging.pack_name) as text, 
                master_products_packaging.price AS product_price,
                master_packaging.id AS IdKemasan
            ")
            ->get();
        return ['results' => $products];
    }

    public function index(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['results'] = SalesOrderProforma::get();

        return view($this->view . "index", $data);
    }

    public function create(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouse'] = Warehouse::get();
        $data['provinsi'] = Province::get();
        $data['brand'] = BrandLokal::get();
        $data['rekening'] = DB::table('rekening')->get();
        $data['vendor'] = Vendor::where('type', 1)->get();
        $data['member'] = CustomerOtherAddress::get();

        return view($this->view . "create", $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'so_date' => 'required|date',
                'warehouse' => 'required|integer',
                'qty' => 'required|array',
                'qty.*' => 'required|numeric|min:0.01',
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
                $sales_proforma = New SalesOrderProforma;

                if($request->existing_customer == 1){
                    $sales_proforma->code = CodeRepo::generateSoProforma();
                    $sales_proforma->warehouse_id = $request->warehouse;
                    $sales_proforma->rekening_id = $request->rekening;
                    $sales_proforma->customer_other_address_id = $request->customer_exisiting;
                    $sales_proforma->vendor_id = $request->vendor;
                    $sales_proforma->so_date = $request->so_date;
                    $sales_proforma->so_brand_name = $request->so_brand_name;
                    $sales_proforma->so_type_transaction = $request->type_transaction;
                    $sales_proforma->so_idr_rate = $request->idr_rate;
                    $sales_proforma->note = $request->note;
                    $sales_proforma->created_by = Auth::id();
                    $sales_proforma->sales_senior_id = $request->sales_senior_id;
                    $sales_proforma->sales_id = $request->sales_id;
                    $sales_proforma->exsisting_customer = 1;
                    $sales_proforma->status = 1;

                    if($sales_proforma->save()) {
                        // input cost proforma
                        $sales_proforma_cost = new SalesOrderProformaDetails;
                        $sales_proforma_cost->so_proforma_id = $sales_proforma->id;
                        $sales_proforma_cost->discount_1_percent = $request->disc_agen_percent;
                        $sales_proforma_cost->discount_1 = $request->disc_agen_idr;
                        $sales_proforma_cost->discount_2_percent = $request->disc_kemasan_percent;
                        $sales_proforma_cost->discount_2 = $request->disc_kemasan_idr;
                        $sales_proforma_cost->discount_idr = $request->disc_tambahan_idr;
                        $sales_proforma_cost->voucher_idr = $request->voucher_idr;
                        $sales_proforma_cost->purchase_total_idr = $request->subtotal;
                        $sales_proforma_cost->delivery_cost_idr = $request->delivery_cost_idr;
                        $sales_proforma_cost->grand_total_idr = $request->grand_total;
                        $sales_proforma_cost->save();

                        if($request->sku) {
                            foreach($request->sku as $key => $item){
                                $duplicate_product = [];
                                $duplicate = false;
                                $listItem[] = [
                                    'sku' => $request->sku[$key],
                                    'free_product' => $request->free_product[$key],
                                ];

                                foreach($listItem as $row => $value){
                                    if(in_array($value, $duplicate_product)) {
                                        $duplicate = true;
                                        break;
                                    } else {
                                        array_push($duplicate_product, $value);
                                    }
                                }

                                if($duplicate){
                                    $response['notification'] = [
                                        'alert' => 'block',
                                        'type' => 'alert-danger',
                                        'header' => 'Error',
                                        'content' => 'Item sudah ada!',
                                    ];
                    
                                    return $this->response(400, $response);
                                }else{
                                    $sales_proforma_item = new SalesOrderProformaItem;

                                    $sales_proforma_item->so_proforma_id = $sales_proforma->id;
                                    $sales_proforma_item->product_packaging_id = $request->sku[$key];
                                    $sales_proforma_item->price = $request->price[$key];
                                    $sales_proforma_item->qty = $request->qty[$key];
                                    $sales_proforma_item->disc_usd = $request->disc_usd[$key];
                                    $sales_proforma_item->total_item = $request->total[$key];
                                    $sales_proforma_item->packaging_id = $request->packaging[$key];
                                    $sales_proforma_item->free_product = $request->free_product[$key];

                                    $sales_proforma_item->save();
                                }
                            }
                        }
                    }

                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.penjualan.so_proforma.index');
                    return $this->response(200, $response);
                }else{
                    $sales_proforma->code = CodeRepo::generateSoProforma();
                    $sales_proforma->warehouse_id = $request->warehouse;
                    $sales_proforma->rekening_id = $request->rekening;
                    $sales_proforma->vendor_id = $request->vendor;
                    $sales_proforma->so_date = $request->so_date;
                    $sales_proforma->so_brand_name = $request->so_brand_name;
                    $sales_proforma->so_type_transaction = $request->type_transaction;
                    $sales_proforma->so_idr_rate = $request->idr_rate;
                    $sales_proforma->note = $request->note;
                    $sales_proforma->customer_name = $request->customer_name;
                    $sales_proforma->customer_address = $request->customer_address;
                    $sales_proforma->customer_region = $request->customer_region;
                    $sales_proforma->customer_city = $request->customer_city;
                    $sales_proforma->customer_phone = $request->customer_phone;
                    $sales_proforma->customer_owner = $request->customer_owner;
                    $sales_proforma->created_by = Auth::id();
                    $sales_proforma->sales_senior_id = $request->sales_senior_id;
                    $sales_proforma->sales_id = $request->sales_id;
                    $sales_proforma->exsisting_customer = 0;
                    $sales_proforma->status = 1;
    
                    if($sales_proforma->save()) {
                        // input cost proforma
                        $sales_proforma_cost = new SalesOrderProformaDetails;
                        $sales_proforma_cost->so_proforma_id = $sales_proforma->id;
                        $sales_proforma_cost->discount_1_percent = $request->disc_agen_percent;
                        $sales_proforma_cost->discount_1 = $request->disc_agen_idr;
                        $sales_proforma_cost->discount_2_percent = $request->disc_kemasan_percent;
                        $sales_proforma_cost->discount_2 = $request->disc_kemasan_idr;
                        $sales_proforma_cost->discount_idr = $request->disc_tambahan_idr;
                        $sales_proforma_cost->voucher_idr = $request->voucher_idr;
                        $sales_proforma_cost->purchase_total_idr = $request->subtotal;
                        $sales_proforma_cost->delivery_cost_idr = $request->delivery_cost_idr;
                        $sales_proforma_cost->grand_total_idr = $request->grand_total;
                        $sales_proforma_cost->save();
    
                        if($request->sku) {
                            foreach($request->sku as $key => $item){
                                $duplicate_product = [];
                                $duplicate = false;
                                $listItem[] = [
                                    'sku' => $request->sku[$key],
                                    'free_product' => $request->free_product[$key],
                                ];
    
                                foreach($listItem as $row => $value){
                                    if(in_array($value, $duplicate_product)) {
                                        $duplicate = true;
                                        break;
                                    } else {
                                        array_push($duplicate_product, $value);
                                    }
                                }
    
                                if($duplicate){
                                    $response['notification'] = [
                                        'alert' => 'block',
                                        'type' => 'alert-danger',
                                        'header' => 'Error',
                                        'content' => 'Item sudah ada!',
                                    ];
                      
                                    return $this->response(400, $response);
                                }else{
                                    $sales_proforma_item = new SalesOrderProformaItem;
    
                                    $sales_proforma_item->so_proforma_id = $sales_proforma->id;
                                    $sales_proforma_item->product_packaging_id = $request->sku[$key];
                                    $sales_proforma_item->price = $request->price[$key];
                                    $sales_proforma_item->qty = $request->qty[$key];
                                    $sales_proforma_item->disc_usd = $request->disc_usd[$key];
                                    $sales_proforma_item->total_item = $request->total[$key];
                                    $sales_proforma_item->packaging_id = $request->packaging[$key];
                                    $sales_proforma_item->free_product = $request->free_product[$key];
    
                                    $sales_proforma_item->save();
                                }
                            }
                        }
                    }
    
                    DB::commit();
    
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
    
                    $response['redirect_to'] = route('superuser.penjualan.so_proforma.index');
                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                abort(405);
            }
        }

        $data['results'] = SalesOrderProforma::find($id);
        $data['warehouse'] = Warehouse::get();
        $data['brand'] = BrandLokal::get();
        $data['provinsi'] = Province::get();
        $data['rekening'] = DB::table('rekening')->get();
        $data['vendor'] = Vendor::where('type', 1)->get();
        $data['customer'] = CustomerOtherAddress::get();

        return view($this->view . "edit", $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'so_date' => 'required|date',
                'so_brand_name' => 'required|string',
                'qty' => 'required|array',
                'qty.*' => 'required|numeric|min:0.01',
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

            DB::beginTransaction();

            try {
                $sales_proforma = SalesOrderProforma::find($id);
                
                $sales_proforma->warehouse_id = $request->warehouse;
                $sales_proforma->rekening_id = $request->rekening;
                $sales_proforma->vendor_id = $request->vendor_id;
                $sales_proforma->so_date = $request->so_date;
                $sales_proforma->so_brand_name = $request->so_brand_name;
                $sales_proforma->so_type_transaction = $request->type_transaction;
                $sales_proforma->so_idr_rate = $request->idr_rate;
                $sales_proforma->note = $request->note;
                $sales_proforma->vendor_id = $request->vendor;

                if ($sales_proforma->exsisting_customer == 0) {
                    $sales_proforma->customer_name = $request->customer;
                    $sales_proforma->customer_address = $request->customer_address;
                    $sales_proforma->customer_region = $request->customer_region;
                    $sales_proforma->customer_city = $request->customer_city;
                    $sales_proforma->customer_phone = $request->customer_phone;
                    $sales_proforma->customer_owner = $request->customer_owner;
                } elseif ($sales_proforma->exsisting_customer == 1) {
                    $sales_proforma->customer_other_address_id = $request->customer;
                }

                $sales_proforma->updated_by = Auth::id();
                $sales_proforma->sales_senior_id = $request->sales_senior_id;
                $sales_proforma->sales_id = $request->sales_id;
                $sales_proforma->status = 1;
                $sales_proforma->save();

                $detail_cost = SalesOrderProformaDetails::where('so_proforma_id', $sales_proforma->id)->first();
                $detail_cost->discount_1_percent = $request->disc_agen_percent;
                $detail_cost->discount_1 = $request->disc_agen_idr;
                $detail_cost->discount_2_percent = $request->disc_kemasan_percent;
                $detail_cost->discount_2 = $request->disc_kemasan_idr;
                $detail_cost->discount_idr = $request->disc_tambahan_idr;
                $detail_cost->voucher_idr = $request->voucher_idr;
                $detail_cost->delivery_cost_idr = $request->delivery_cost_idr;
                $detail_cost->purchase_total_idr = $request->subtotal;
                $detail_cost->grand_total_idr = $request->grand_total;
                $detail_cost->save();

                if ($request->ids_delete) {
                    $pieces = explode(",", $request->ids_delete);
                    foreach ($pieces as $piece) {
                        SalesOrderProformaItem::where('id', $piece)->delete();
                    }
                }

                if ($request->sku) {
                    foreach ($request->sku as $key => $value) {
                        $sales_proforma_item = $request->edit[$key] 
                            ? SalesOrderProformaItem::find($request->edit[$key]) 
                            : new SalesOrderProformaItem;
                
                        $sales_proforma_item->so_proforma_id = $sales_proforma->id;
                        $sales_proforma_item->product_packaging_id = $request->sku[$key];
                        $sales_proforma_item->price = $request->price[$key];
                        $sales_proforma_item->qty = $request->qty[$key];
                        $sales_proforma_item->disc_usd = $request->disc_usd[$key];
                        $sales_proforma_item->total_item = $request->total[$key];
                        if (isset($request->packaging[$key])) {
                            $sales_proforma_item->packaging_id = $request->packaging[$key];
                        }
                        if (isset($request->free_product[$key])) {
                            $sales_proforma_item->free_product = $request->free_product[$key];
                        }
                        $sales_proforma_item->save();
                    }
                }

                DB::commit();

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.penjualan.so_proforma.index');

                return $this->response(200, $response);
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => ['An error occurred while updating the sales order proforma. Please try again.'],
                ];
                return $this->response(500, $response);
            }
        }
    }

    public function show($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['results'] = SalesOrderproforma::find($id);
        $data['warehouse'] = Warehouse::get();
        $data['brand'] = BrandLokal::get();
        $data['provinsi'] = Province::get();
        $data['rekening'] = DB::table('rekening')->get();
        $data['vendor'] = Vendor::where('type', 1)->get();

        return view($this->view . "show", $data);
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            // Find the sales proforma order by ID
            $sales_proforma = SalesOrderProforma::find($id);

            // Abort if not found
            if ($sales_proforma == null) {
                abort(404);
            }

            if($sales_proforma->exsisting_customer  == 0){
                // Update the sales proforma
                $sales_proforma->updated_by = Auth::id();
                $sales_proforma->status = 2;

                if ($sales_proforma->save()) {
                    // Check for duplicate store names
                    $input_name = $sales_proforma->customer_name;

                    $customer = DB::table('master_customers')
                            ->selectRaw('id, name, kota, category_id, status')
                            ->where('status', Customer::STATUS["ACTIVE"])
                            ->get();

                    $duplicateFound = false;

                    if ($customer) {
                        foreach ($customer as $cust) {
                            if (strtolower($cust->name) == strtolower($input_name)) {
                                $duplicateFound = true;
                                break;
                            }
                        }
                    }

                    if ($duplicateFound) {
                        // Return error response if duplicate is found
                        $response['notification'] = [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => "Duplicate store found",
                        ];

                        return $this->response(400, $response);
                    } else {
                        // Create new customer
                        $customer = new Customer;
                        $customer->code = CodeRepo::generateCustomer();
                        $customer->name = $sales_proforma->customer_name;
                        $customer->count_member = 1;
                        $customer->phone = $sales_proforma->customer_phone;
                        $customer->address = $sales_proforma->customer_address;
                        $customer->owner_name = $sales_proforma->customer_owner;
                        $customer->provinsi = $sales_proforma->customer_region;
                        $customer->kota = $sales_proforma->customer_city;
                        $customer->status = Customer::STATUS['ACTIVE'];
                        $customer->save();

                        // Create customer's other address
                        $other_address = new CustomerOtherAddress;
                        $other_address->id = $customer->id . '.' . $customer->count_member;
                        $other_address->customer_id = $customer->id;
                        $other_address->member_default = 1;
                        $other_address->name = $customer->name;
                        $other_address->contact_person = $customer->owner_name;
                        $other_address->phone = $customer->phone;
                        $other_address->address = $customer->address;
                        $other_address->provinsi = $customer->provinsi;
                        $other_address->kota = $customer->kota;
                        $other_address->status = CustomerOtherAddress::STATUS['ACTIVE'];
                        $other_address->save();

                        // update member ID in proforma
                        $update_proforma = SalesOrderProforma::where('id', $sales_proforma->id)->update([
                            'customer_other_address_id' => $other_address->id
                        ]);
                    }

                    DB::commit();

                    // Return success response
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.penjualan.so_proforma.index');

                    return $this->response(200, $response);
                }
            }elseif($sales_proforma->exsisting_customer == 1){
                // Update the sales proforma
                $sales_proforma->updated_by = Auth::id();
                $sales_proforma->status = 2;
                if ($sales_proforma->save()) {
                    DB::commit();
                    // Return success response
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.penjualan.so_proforma.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function approval_so(Request $request, $id)
    {
        if (!$request->ajax()) {
            abort(400, 'Invalid request type.');
        }

        if (Auth::user()->is_superuser == 0 && (empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0)) {
            abort(405, 'Unauthorized action.');
        }

        $sales_proforma = SalesOrderProforma::find($id);
        if ($sales_proforma === null) {
            abort(404, 'Sales Order Proforma not found.');
        }

        $sales_proforma->status = 4;
        $sales_proforma->so_lanjutan = 1;
        $sales_proforma->updated_by = Auth::id();

        if (!$sales_proforma->save()) {
            abort(500, 'Failed to update Sales Order Proforma.');
        }

        DB::beginTransaction();

        try {
            $get_customer = CustomerOtherAddress::where('id', $sales_proforma->customer_other_address_id)->firstOrFail();

            // Creating Sales Order
            $insert_so = $this->createSalesOrder($sales_proforma, $get_customer);
            $this->createSalesOrderItems($sales_proforma, $insert_so->id);

            // Creating Packing Order
            $insert_do = $this->createPackingOrder($sales_proforma, $get_customer, $insert_so->code, $insert_so->id);
            $this->createPackingOrderCost($sales_proforma, $insert_do->id);
            $this->createPackingOrderItems($sales_proforma, $insert_do->id, $insert_so->id);

            // Creating Invoicing
            $this->createInvoicing($insert_do->id, $sales_proforma, $get_customer, $insert_do->do_code);

            DB::commit();

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = route('superuser.penjualan.so_proforma.index');

            return $this->response(200, $response);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for further inspection
            Log::error('Transaction failed: ' . $e->getMessage());
            abort(500, 'Transaction failed: ' . $e->getMessage());
        }
    }

    private function createSalesOrder($sales_proforma, $customer)
    {
        $data_so_regular = [
            'so_code' => CodeRepo::generateSoAwal(),
            'code' => CodeRepo::generateSO(),
            'so_date' => $sales_proforma->so_date,
            'brand_name' => $sales_proforma->so_brand_name,
            'origin_warehouse_id' => $sales_proforma->warehouse_id,
            'customer_id' => $customer->customer_id,
            'customer_other_address_id' => $sales_proforma->customer_other_address_id,
            'type_transaction' => $sales_proforma->so_type_transaction,
            'rekening' => $sales_proforma->rekening_id,
            'type_so' => 'nonppn',
            'idr_rate' => $sales_proforma->so_idr_rate,
            'status' => 4,
            'condition' => 1,
            'payment_status' => 0,
            'so_for' => 1,
            'so_indent' => 0,
            'count_rev' => 0,
            'created_by' => Auth::id(),
            'note' => $sales_proforma->note,
            'ekspedisi_id' => $sales_proforma->vendor_id,
            'sales_senior_id' => $sales_proforma->sales_senior_id,
            'sales_id' => $sales_proforma->sales_id,
        ];

        return SalesOrder::create($data_so_regular);
    }

    private function createSalesOrderItems($sales_proforma, $so_id)
    {
        foreach ($sales_proforma->items as $item) {
            $data_so_item = [
                'product_packaging_id' => $item->product_packaging_id,
                'so_id' => $so_id,
                'price' => $item->price,
                'qty' => $item->qty,
                'disc_usd' => $item->disc_usd,
                'packaging_id' => $item->packaging_id,
                'free_product' => 0,
                'kontrak' => 0,
                'created_by' => Auth::id(),
                'qty_worked' => $item->qty,
            ];

            SalesOrderItem::create($data_so_item);
        }
    }

    private function createPackingOrder($sales_proforma, $customer, $so_code, $so_id)
    {
        $data_do = [
            'code' => CodeRepo::generatePO(),
            'do_code' => $so_code,
            'warehouse_id' => $sales_proforma->warehouse_id,
            'customer_id' => $customer->customer_id,
            'customer_other_address_id' => $sales_proforma->customer_other_address_id,
            'vendor_id' => null,
            'idr_rate' => $sales_proforma->so_idr_rate,
            'type_transaction' => $sales_proforma->so_type_transaction,
            'count_cancel' => 0,
            'status' => 2,
            'note' => $sales_proforma->note,
            'created_by' => Auth::id(),
            'so_id' => $so_id,
        ];

        return PackingOrder::create($data_do);
    }

    private function createPackingOrderCost($sales_proforma, $do_id)
    {
        $details_cost = $sales_proforma->details_cost[0];

        $data_do_cost = [
            'do_id' => $do_id,
            'discount_1' => $details_cost->discount_1_percent,
            'discount_2' => $details_cost->discount_2_percent,
            'discount_1_idr' => $details_cost->discount_1,
            'discount_2_idr' => $details_cost->discount_2,
            'discount_idr' => $details_cost->discount_idr,
            'voucher_idr' => $details_cost->voucher_idr,
            'purchase_total_idr' => $details_cost->purchase_total_idr,
            'delivery_cost_idr' => $details_cost->delivery_cost_idr,
            'status_resi' => 0,
            'grand_total_idr' => $details_cost->grand_total_idr,
        ];

        PackingOrderDetail::create($data_do_cost);
    }

    private function createPackingOrderItems($sales_proforma, $do_id, $so_id)
    {
        foreach ($sales_proforma->items as $item) {
            $data_do_item = [
                'product_packaging_id' => $item->product_packaging_id,
                'do_id' => $do_id,
                'so_item_id' => $so_id,
                'qty' => $item->qty,
                'price' => $item->price,
                'usd_disc' => $item->disc_usd,
                'packaging_id' => $item->packaging_id,
                'total' => ($item->price - $item->disc_usd) * $item->qty,
                'created_by' => Auth::id(),
                'qty_worked' => $item->qty,
            ];

            PackingOrderItem::create($data_do_item);
        }
    }

    private function createInvoicing($do_id, $sales_proforma, $customer, $code)
    {
        $data_invoicing = [
            'code' => $code,
            'do_id' => $do_id,
            'customer_id' => $customer->customer_id,
            'customer_other_address_id' => $sales_proforma->customer_other_address_id,
            'grand_total_idr' => $sales_proforma->details_cost[0]->grand_total_idr,
            'created_by' => Auth::id(),
        ];

        Invoicing::create($data_invoicing);
    }

    public function destroy(Request $request, $id)
    {   
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    abort(405);
                }
            }

            $sales_proforma = SalesOrderProforma::find($id);

            if ($sales_proforma === null) {
                abort(404);
            }

            $sales_proforma->status = 0;
            $sales_proforma->deleted_by = Auth::id();
            if ($sales_proforma->save()) {
                $response['redirect_to'] = route('superuser.penjualan.so_proforma.index');
                return $this->response(200, $response);
            }
        }
    }

    public function print_so_proforma(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrderProforma::where('id', $id)->first();

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\proforma\\invoice_proforma.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\proforma\\export\\'.$result->code.'.pdf';

         //- Variables - Server Information 
        $my_server = "LOCAL"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi_araya";
        $COM_Object = "CrystalDesignRunTime.Application";

        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{penjualan_so_proforma.id}= $result->id";


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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\proforma\\export\\'.$result->code.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function getCustomer(Request $request)
    {
        $customers = CustomerOtherAddress::leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
            ->where('master_customers.status', 1)
            ->where(function ($query) use ($request) {
                $query->where('master_customer_other_addresses.name', 'LIKE', $request->input('q', '') . '%');
            })
            ->select(
                'master_customer_other_addresses.id',
                'master_customer_other_addresses.name', 
                'master_customer_other_addresses.text_kota', 
                'master_customer_other_addresses.text_provinsi', 
                'master_customer_other_addresses.contact_person',
                'master_customer_other_addresses.address',
                'master_customer_other_addresses.phone',
            )
            ->get();

        $results = [];

        foreach ($customers as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->name . ' - ' . $item->text_kota, // Concatenate name and address
                'provinsi' => $item->text_provinsi,
                'kota' => $item->text_kota,
                'owner' => $item->contact_person,
                'address' => $item->address,
                'phone' => $item->phone,
            ];
        }

        return ['results' => $results];
    }
}
