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
use App\Entities\Gudang\MutasiShowroom;
use App\Entities\Gudang\MutasiShowroomDetail;
use App\Services\StockService;
use App\Entities\Master\Vendor;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\Request;
use App\Repositories\CodeRepo;
use Illuminate\Support\Facades\Log;
use Validator;
use Auth;
use COM;
use DB;
use PDF;

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

    public function index()
    {
        $aktif = SalesOrderProforma::with('salesOrder.member')
            ->whereHas('salesOrder', function ($q) {
                $q->where('status_proforma', 1);
            })->get();

        $terbuat = SalesOrderProforma::with('salesOrder.member')
            ->whereHas('salesOrder', function ($q) {
                $q->where('status_proforma', 2);
            })->get();


        $siap = SalesOrderProforma::with('salesOrder.member')
            ->whereHas('salesOrder', function ($q) {
                $q->where('status_proforma', 3);
            })->get();

        $tutup = SalesOrderProforma::with('salesOrder.member')
            ->whereHas('salesOrder', function ($q) {
                $q->where('status_proforma', 4);
            })->get();

        return view('superuser.penjualan.so_proforma.index', [
            'aktif' => $aktif,
            'terbuat' => $terbuat,
            'siap' => $siap,
            'tutup' => $tutup,

            'count_aktif' => $aktif->count(),
            'count_terbuat' => $terbuat->count(),
            'count_siap' => $siap->count(),
            'count_tutup' => $tutup->count(),
        ]);
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
                                    // 'free_product' => $request->free_product[$key],
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

        $results = SalesOrderProforma::findOrFail($id);

        // Jika belum ada cost detail, buat instance kosong
        $detailsCost = $results->details_cost ?? new SalesOrderProformaDetails();

        return view($this->view . "edit", [
            'results'     => $results,
            'detailsCost' => $detailsCost,
            'warehouse'   => Warehouse::get(),
            'brand'       => BrandLokal::get(),
            'provinsi'    => Province::get(),
            'rekening'    => DB::table('rekening')->get(),
            'vendor'      => Vendor::where('type', 1)->get(),
            'customer'    => CustomerOtherAddress::get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'warehouse' => 'required|exists:master_warehouses,id',
        
            'so_date' => 'required|date',
            'so_brand_name' => 'required|string',
            'idr_rate' => 'required|numeric|min:1',
        
            'qty' => 'required|array',
            'qty.*' => 'nullable|numeric|min:0.01',
            'price.*' => 'nullable|numeric|min:0',
            'disc_usd.*' => 'nullable|numeric|min:0',
        ], [
            'warehouse.required' => 'Warehouse wajib dipilih.',
            'warehouse.exists'   => 'Warehouse tidak valid.',
        ]);

        if ($validator->fails()) {
            return $this->response(400, [
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ]
            ]);
        }

        DB::beginTransaction();

        try {

            $sales_proforma = SalesOrderProforma::findOrFail($id);

            $sales_proforma->warehouse_id = $request->warehouse;
            $sales_proforma->rekening_id = $request->rekening;
            $sales_proforma->vendor_id = $request->vendor;
            $sales_proforma->so_date = $request->so_date;
            $sales_proforma->so_brand_name = $request->so_brand_name;
            $sales_proforma->so_type_transaction = $request->type_transaction;
            $sales_proforma->so_idr_rate = $request->idr_rate;
            $sales_proforma->note = $request->note;
            $sales_proforma->sales_senior_id = $request->sales_senior_id;
            $sales_proforma->sales_id = $request->sales_id;
            $sales_proforma->updated_by = Auth::id();
            $sales_proforma->status = 1;

            if ($sales_proforma->exsisting_customer == 0) {
                $sales_proforma->customer_name = $request->customer;
                $sales_proforma->customer_address = $request->customer_address;
                $sales_proforma->customer_region = $request->customer_region;
                $sales_proforma->customer_city = $request->customer_city;
                $sales_proforma->customer_phone = $request->customer_phone;
                $sales_proforma->customer_owner = $request->customer_owner;
            } else {
                $sales_proforma->customer_other_address_id = $request->customer;
            }

            $sales_proforma->save();

            // ===== DETAIL COST =====
            $detail_cost = SalesOrderProformaDetails::firstOrNew([
                'so_proforma_id' => $sales_proforma->id
            ]);

            $detail_cost->discount_1_percent = $request->disc_agen_percent ?? 0;
            $detail_cost->discount_1 = $request->disc_agen_idr ?? 0;
            $detail_cost->discount_2_percent = $request->disc_kemasan_percent ?? 0;
            $detail_cost->discount_2 = $request->disc_kemasan_idr ?? 0;
            $detail_cost->discount_idr = $request->disc_tambahan_idr ?? 0;
            $detail_cost->voucher_idr = $request->voucher_idr ?? 0;
            $detail_cost->delivery_cost_idr = $request->delivery_cost_idr ?? 0;
            $detail_cost->purchase_total_idr = $request->subtotal ?? 0;
            $detail_cost->grand_total_idr = $request->grand_total ?? 0;

            $detail_cost->save();

            // ===== DELETE ITEMS =====
            if ($request->ids_delete) {
                $ids = explode(",", $request->ids_delete);
                SalesOrderProformaItem::whereIn('id', $ids)->delete();
            }

            // ===== UPDATE STATUS PROFORMA =====
            $sales_order = SalesOrder::find($sales_proforma->so_id);
            $sales_order->status_proforma = 2;
            $sales_order->save();

            // ===== ITEMS =====
            if ($request->sku) {

                foreach ($request->sku as $key => $value) {

                    if (!$value) continue;

                    $free = isset($request->free_product[$key]) 
                            ? $request->free_product[$key] 
                            : 0;

                    if (!empty($request->edit[$key])) {

                        $item = SalesOrderProformaItem::find($request->edit[$key]);

                    } else {

                        $item = new SalesOrderProformaItem;
                        $item->so_proforma_id = $sales_proforma->id;
                    }

                    $item->product_packaging_id = $value;
                    $item->price = $request->price[$key] ?? 0;
                    $item->qty = $request->qty[$key] ?? 0;
                    $item->disc_usd = $request->disc_usd[$key] ?? 0;
                    $item->total_item = $request->total[$key] ?? 0;
                    $item->packaging_id = $request->packaging[$key] ?? null;
                    $item->free_product = $free;

                    $item->save();
                }
            }

            DB::commit();

            return $this->response(200, [
                'notification' => [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ],
                'redirect_to' => route('superuser.penjualan.so_proforma.index')
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return $this->response(500, [
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => [$e->getMessage()],
                ]
            ]);
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
        if (!$request->ajax()) {
            abort(400, 'Invalid request type.');
        }
    
        DB::beginTransaction();
    
        try {
    
            /*
            |--------------------------------------------------------------------------
            | 1️⃣ LOCK & AMBIL PROFORMA
            |--------------------------------------------------------------------------
            */
    
            $sales_proforma = SalesOrderProforma::with(['items','details_cost'])
                ->lockForUpdate()
                ->findOrFail($id);
    
            if ($sales_proforma->status == 4) {
                throw new \Exception("Proforma sudah pernah di-ACC.");
            }
    
            /*
            |--------------------------------------------------------------------------
            | 🔹 PREPARE & GENERATE SO CODE (DIPINDAH KE ATAS)
            |--------------------------------------------------------------------------
            */
    
            $sales_order = SalesOrder::lockForUpdate()
                ->findOrFail($sales_proforma->so_id);
    
            $sales_order->status = 4;
            $sales_order->status_proforma = 4;
            $sales_order->code = CodeRepo::generateSO();
            $sales_order->payment_status = 0; // cek apakah status nya 
            $sales_order->updated_by = Auth::id();
            $sales_order->so_date = $sales_proforma->so_date ?? null ;
            $sales_order->sales_id = $sales_proforma->sales_id ?? null ;
            $sales_order->sales_senior_id = $sales_proforma->sales_senior_id ?? null ;
            $sales_order->rekening = $sales_proforma->rekening_id;
            $sales_order->origin_warehouse_id = $sales_proforma->warehouse_id;
            $sales_order->ekspedisi_id = $sales_proforma->vendor_id ?? null;
            $sales_order->save();
    
            $transactionCode = $sales_order->code;
    
            /*
            |--------------------------------------------------------------------------
            | 2️⃣ VALIDASI KETAT & POTONG STOK LANGSUNG (PROFORMA RULE)
            |--------------------------------------------------------------------------
            */

            $warnings = [];
            $mutasiItems = []; // untuk free product mutasi showroom
            $stockService = new \App\Services\StockService(); // Panggil 1 Pintu

            foreach ($sales_proforma->items as $item) {

                // A. Pengecekan Ketersediaan Stok (Strict Check)
                $stock = \App\Entities\Master\ProductMinStock::where('warehouse_id', $sales_proforma->warehouse_id)
                    ->where('product_packaging_id', $item->product_packaging_id)
                    ->first();

                $currentQty = $stock ? (float) $stock->quantity : 0;
                $currentReserved = $stock ? (float) $stock->reserved_quantity : 0;
                $available = $currentQty - $currentReserved;

                // Jika stok tidak cukup, batalkan ACC!
                if ($available < $item->qty) {
                    
                    // 1. Ambil detail produk (Hapus suffix _1 dll jika ada)
                    $base_product_id = preg_replace('/_\d+$/', '', $item->product_packaging_id);
                    $productDetail = DB::table('master_products_packaging')
                                        ->where('id', $base_product_id)
                                        ->first();
                    
                    // 2. Format tampilan: Kode - Nama (atau ID jika tidak ketemu)
                    $productDisplay = $productDetail 
                        ? ($productDetail->code . ' - ' . $productDetail->name) 
                        : "ID " . $item->product_packaging_id;

                    // 3. Lempar error dengan nama yang jelas
                    throw new \Exception("STOK TIDAK MENCUKUPI! Produk {$productDisplay} hanya tersedia {$available}, namun Anda membutuhkan {$item->qty}. Harap periksa fisik/kartu stok Anda.");
                }
            }
    
            /*
            |--------------------------------------------------------------------------
            | 3️⃣ HANDLE CUSTOMER (JIKA NON EXISTING)
            |--------------------------------------------------------------------------
            */
    
            if ($sales_proforma->exsisting_customer == 0) {
    
                $duplicate = Customer::whereRaw('LOWER(name) = ?', 
                            [strtolower($sales_proforma->customer_name)])
                            ->where('status', Customer::STATUS['ACTIVE'])
                            ->exists();
    
                if ($duplicate) {
                    throw new \Exception("Duplicate store found");
                }
    
                $customer = Customer::create([
                    'code'          => CodeRepo::generateCustomer(),
                    'name'          => $sales_proforma->customer_name,
                    'count_member'  => 1,
                    'phone'         => $sales_proforma->customer_phone,
                    'address'       => $sales_proforma->customer_address,
                    'owner_name'    => $sales_proforma->customer_owner,
                    'provinsi'      => $sales_proforma->customer_region,
                    'kota'          => $sales_proforma->customer_city,
                    'status'        => Customer::STATUS['ACTIVE'],
                ]);
    
                $otherAddress = CustomerOtherAddress::create([
                    'id'                => $customer->id.'.1',
                    'customer_id'       => $customer->id,
                    'member_default'    => 1,
                    'name'              => $customer->name,
                    'contact_person'    => $customer->owner_name,
                    'phone'             => $customer->phone,
                    'address'           => $customer->address,
                    'provinsi'          => $customer->provinsi,
                    'kota'              => $customer->kota,
                    'status'            => CustomerOtherAddress::STATUS['ACTIVE'],
                ]);
    
                $sales_proforma->customer_other_address_id = $otherAddress->id;
            }
    
            /*
            |--------------------------------------------------------------------------
            | 5️⃣ UPDATE PROFORMA
            |--------------------------------------------------------------------------
            */
    
            $sales_proforma->status = 4;
            $sales_proforma->so_lanjutan = 1;
            $sales_proforma->updated_by = Auth::id();
            $sales_proforma->save();
    
            /*
            |--------------------------------------------------------------------------
            | 6️⃣ CREATE PACKING ORDER
            |--------------------------------------------------------------------------
            */
    
            $packingOrder = PackingOrder::create([
                'code' => CodeRepo::generatePO(),
                'do_code' => $sales_order->code,
                'warehouse_id' => $sales_proforma->warehouse_id,
                'customer_id' => $sales_order->customer_id,
                'customer_other_address_id' => $sales_proforma->customer_other_address_id,
                'vendor_id' => $sales_proforma->vendor_id,
                'idr_rate' => $sales_proforma->so_idr_rate,
                'type_transaction' => optional($sales_proforma->salesOrder)->type_transaction,
                'count_cancel' => 0,
                'status' => 2,
                'note' => $sales_proforma->note,
                'created_by' => Auth::id(),
                'so_id' => $sales_order->id,
            ]);
    
            $do_id = $packingOrder->id;
    
            /*
            |--------------------------------------------------------------------------
            | 7 COPY DETAIL COST
            |--------------------------------------------------------------------------
            */
    
            if ($sales_proforma->details_cost) {
    
                PackingOrderDetail::create([
                    'do_id' => $do_id,
                    'discount_1' => $sales_proforma->details_cost->discount_1_percent,
                    'discount_2' => $sales_proforma->details_cost->discount_2_percent,
                    'discount_1_idr' => $sales_proforma->details_cost->discount_1,
                    'discount_2_idr' => $sales_proforma->details_cost->discount_2,
                    'discount_idr' => $sales_proforma->details_cost->discount_idr,
                    'voucher_idr' => $sales_proforma->details_cost->voucher_idr,
                    'purchase_total_idr' => $sales_proforma->details_cost->purchase_total_idr,
                    'delivery_cost_idr' => $sales_proforma->details_cost->delivery_cost_idr,
                    'status_resi' => 0,
                    'grand_total_idr' => $sales_proforma->details_cost->grand_total_idr,
                ]);
            }
    
            /*
            |--------------------------------------------------------------------------
            | 8 COPY ITEMS
            |--------------------------------------------------------------------------
            */
    
            foreach ($sales_proforma->items as $item) {
    
                $soItem = SalesOrderItem::where('so_id', $sales_order->id)
                    ->where('product_packaging_id', $item->product_packaging_id)
                    ->first();

                $is_free_product = !empty($soItem->free_product) && (float)$soItem->free_product > 0;

                if ($is_free_product) {
                    
                    if ($item->qty > 0) {
                    
                        $mutasiItems[] = [
                            'product_packaging_id' => $item->product_packaging_id,
                            'qty'  => $item->qty,
                            'note' => 'Free product otomatis dari SO ' . $sales_order->code,
                        ];
                    }
                }
    
                PackingOrderItem::create([
                    'product_packaging_id' => $item->product_packaging_id,
                    'do_id' => $do_id,
                    'so_item_id' => $soItem->id ?? null,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'usd_disc' => $item->disc_usd,
                    'packaging_id' => $item->packaging_id,
                    'total' => ($item->price - $item->disc_usd) * $item->qty,
                    'created_by' => Auth::id(),
                    'qty_worked' => $item->qty,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | MUTASI SHOWROOM FREE PRODUCT
            |--------------------------------------------------------------------------
            */

            if (!empty($mutasiItems)) {

                $mutasiItems = array_filter($mutasiItems, function ($item) {
                    return isset($item['qty']) && $item['qty'] > 0;
                });

                if (!empty($mutasiItems)) {

                    $mutasi = MutasiShowroom::create([
                        'kode' => CodeRepo::generateMutasiShowroom(MutasiShowroom::TYPE_SYSTEM_FREE_SO),
                        'brand_name' => $sales_order->brand_name ?? '-',
                        'type' => MutasiShowroom::TYPE_SYSTEM_FREE_SO,
                        'warehouse_from_id' => $sales_proforma->warehouse_id,
                        'warehouse_to_id' => $sales_order->customer_id == 51
                                                ? 53
                                                : $sales_order->customer_id,
                        'customer_other_address_id' => $sales_proforma->customer_other_address_id ?? null,
                        'so_id' => $sales_order->id,
                        'tanggal' => now(),
                        'status' => MutasiShowroom::STATUS['SETTLE'],
                        'status_checked' => MutasiShowroom::STATUS_CHECKED['CHECKED'],
                        'status_barang' => MutasiShowroom::STATUS_BARANG['DIAMBIL'],
                        'note' => 'Mutasi Free Product dari SO ' . $sales_order->code,
                        'created_by' => Auth::id(),
                    ]);

                    // foreach ($mutasiItems as $item) {

                    //     MutasiShowroomDetail::create([
                    //         'penjualan_showroom_id' => $mutasi->id,
                    //         'product_packaging_id' => $item['product_packaging_id'],
                    //         'qty' => $item['qty'],
                    //         'price' => 0,
                    //         'total_price' => 0,
                    //         'note' => $item['note'] ?? null,
                    //     ]);

                    // }
                    
                    foreach ($mutasiItems as $item) {

                        MutasiShowroomDetail::create([
                            'penjualan_showroom_id' => $mutasi->id,
                            'product_packaging_id' => $item['product_packaging_id'],
                            'qty' => $item['qty'],
                            'price' => 0,
                            'total_price' => 0,
                            'note' => $item['note'] ?? null,
                        ]);

                        // ==========================================
                        // TUTUP KEBOCORAN: CETAK LOG KARTU STOK SAJA
                        // ==========================================
                        $docCode = $mutasi->kode;
                        $note = 'Mutasi Free Product dari SO ' . $sales_order->code;

                        // ❌ BAGIAN INI SAYA HAPUS KARENA FISIK SUDAH DIPOTONG DI TAHAP 2 (ATAS)
                        // $stockService->deductPhysicalStock( ... ); 

                        // ✅ CUKUP PANGGIL PENCETAKAN KARTU STOK SAJA:
                        $stockService->recordAdministrativeLog(
                            $mutasi->warehouse_from_id, 
                            $item['product_packaging_id'], 
                            $item['qty'], 
                            $docCode, 
                            $note
                        );
                    }
                }
            }
    
            /*
            |--------------------------------------------------------------------------
            | 8️⃣ CREATE INVOICE
            |--------------------------------------------------------------------------
            */
    
            Invoicing::create([
                'code'                      => $sales_order->code,
                'do_id'                     => $packingOrder->id,
                'customer_id'               => $sales_order->customer_id,
                'customer_other_address_id' => $sales_proforma->customer_other_address_id,
                'grand_total_idr'           => $sales_proforma->details_cost->grand_total_idr ?? 0,
                'created_by'                => Auth::id(),
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'ACC berhasil. SO lanjutan & DO telah dibuat.'
            ]);
    
        } catch (\Throwable $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function acc(Request $request, $id)
    // {
    //     if ($request->ajax()) {
    //         // Find the sales proforma order by ID
    //         $sales_proforma = SalesOrderProforma::find($id);

    //         // Abort if not found
    //         if ($sales_proforma == null) {
    //             abort(404);
    //         }

    //         if($sales_proforma->exsisting_customer  == 0){
    //             // Update the sales proforma
    //             $sales_proforma->updated_by = Auth::id();
    //             $sales_proforma->status = 2;

    //             if ($sales_proforma->save()) {
    //                 // Check for duplicate store names
    //                 $input_name = $sales_proforma->customer_name;

    //                 $customer = DB::table('master_customers')
    //                         ->selectRaw('id, name, kota, category_id, status')
    //                         ->where('status', Customer::STATUS["ACTIVE"])
    //                         ->get();

    //                 $duplicateFound = false;

    //                 if ($customer) {
    //                     foreach ($customer as $cust) {
    //                         if (strtolower($cust->name) == strtolower($input_name)) {
    //                             $duplicateFound = true;
    //                             break;
    //                         }
    //                     }
    //                 }

    //                 if ($duplicateFound) {
    //                     // Return error response if duplicate is found
    //                     $response['notification'] = [
    //                         'alert' => 'block',
    //                         'type' => 'alert-danger',
    //                         'header' => 'Error',
    //                         'content' => "Duplicate store found",
    //                     ];

    //                     return $this->response(400, $response);
    //                 } else {
    //                     // Create new customer
    //                     $customer = new Customer;
    //                     $customer->code = CodeRepo::generateCustomer();
    //                     $customer->name = $sales_proforma->customer_name;
    //                     $customer->count_member = 1;
    //                     $customer->phone = $sales_proforma->customer_phone;
    //                     $customer->address = $sales_proforma->customer_address;
    //                     $customer->owner_name = $sales_proforma->customer_owner;
    //                     $customer->provinsi = $sales_proforma->customer_region;
    //                     $customer->kota = $sales_proforma->customer_city;
    //                     $customer->status = Customer::STATUS['ACTIVE'];
    //                     $customer->save();

    //                     // Create customer's other address
    //                     $other_address = new CustomerOtherAddress;
    //                     $other_address->id = $customer->id . '.' . $customer->count_member;
    //                     $other_address->customer_id = $customer->id;
    //                     $other_address->member_default = 1;
    //                     $other_address->name = $customer->name;
    //                     $other_address->contact_person = $customer->owner_name;
    //                     $other_address->phone = $customer->phone;
    //                     $other_address->address = $customer->address;
    //                     $other_address->provinsi = $customer->provinsi;
    //                     $other_address->kota = $customer->kota;
    //                     $other_address->status = CustomerOtherAddress::STATUS['ACTIVE'];
    //                     $other_address->save();

    //                     // update member ID in proforma
    //                     $update_proforma = SalesOrderProforma::where('id', $sales_proforma->id)->update([
    //                         'customer_other_address_id' => $other_address->id
    //                     ]);
    //                 }

    //                 DB::commit();

    //                 // Return success response
    //                 $response['notification'] = [
    //                     'alert' => 'notify',
    //                     'type' => 'success',
    //                     'content' => 'Success',
    //                 ];

    //                 $response['redirect_to'] = route('superuser.penjualan.so_proforma.index');

    //                 return $this->response(200, $response);
    //             }
    //         }elseif($sales_proforma->exsisting_customer == 1){
    //             // Update the sales proforma
    //             $sales_proforma->updated_by = Auth::id();
    //             $sales_proforma->status = 2;
    //             if ($sales_proforma->save()) {
    //                 DB::commit();
    //                 // Return success response
    //                 $response['notification'] = [
    //                     'alert' => 'notify',
    //                     'type' => 'success',
    //                     'content' => 'Success',
    //                 ];

    //                 $response['redirect_to'] = route('superuser.penjualan.so_proforma.index');

    //                 return $this->response(200, $response);
    //             }
    //         }
    //     }
    // }

    // public function approval_so(Request $request, $id)
    // {
    //     if (!$request->ajax()) {
    //         abort(400, 'Invalid request type.');
    //     }

    //     if (
    //         Auth::user()->is_superuser == 0 &&
    //         (empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0)
    //     ) {
    //         abort(405, 'Unauthorized action.');
    //     }

    //     $sales_proforma = SalesOrderProforma::with(['items', 'details_cost'])->find($id);
    //     if (!$sales_proforma) {
    //         abort(404, 'Sales Order Proforma not found.');
    //     }

    //     // Prevent double approval
    //     if ($sales_proforma->status == 4) {
    //         abort(400, 'Proforma sudah pernah di approve.');
    //     }

    //     $sales_order = SalesOrder::find($sales_proforma->so_id);
    //     if (!$sales_order) {
    //         abort(404, 'SO awal tidak ditemukan.');
    //     }

    //     DB::beginTransaction();

    //     try {

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 1️⃣ VALIDASI + RESERVE STOCK
    //         |--------------------------------------------------------------------------
    //         */
    //         foreach ($sales_proforma->items as $item) {

    //             $stock = \App\Entities\Master\ProductMinStock::where('product_packaging_id', $item->product_packaging_id)
    //                 ->where('warehouse_id', $sales_proforma->warehouse_id)
    //                 ->lockForUpdate()
    //                 ->first();

    //             if (!$stock) {
    //                 throw new \Exception('Stock tidak ditemukan untuk salah satu produk.');
    //             }

    //             $available = $stock->quantity - ($stock->reserved_quantity ?? 0);

    //             if ($available < $item->qty) {
    //                 throw new \Exception('Stock tersedia tidak mencukupi untuk produk tertentu.');
    //             }

    //             // 🔒 RESERVE STOCK
    //             $stock->reserved_quantity += $item->qty;
    //             $stock->save();
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | 2️⃣ UPDATE SO AWAL (JADI TUTUP)
    //         |--------------------------------------------------------------------------
    //         */
    //         $sales_order->status = 4;
    //         $sales_order->code = CodeRepo::generateSO();
    //         $sales_order->origin_warehouse_id = $request->warehouse_id ?? $sales_order->origin_warehouse_id;
    //         $sales_order->rekening = $request->rekening_id ?? $sales_proforma->rekening_id;
    //         $sales_order->sales_senior_id = $request->sales_senior_id ?? $sales_order->sales_senior_id;
    //         $sales_order->sales_id = $request->sales_id ?? $sales_order->sales_id;
    //         $sales_order->ekspedisi_id = $request->vendor_id ?? $sales_order->ekspedisi_id;
    //         $sales_order->so_date = $request->so_date ?? now();
    //         $sales_order->payment_status = 1;
    //         $sales_order->updated_by = Auth::id();
    //         $sales_order->save();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 3️⃣ UPDATE PROFORMA
    //         |--------------------------------------------------------------------------
    //         */
    //         $sales_proforma->status = 4;
    //         $sales_proforma->so_lanjutan = 1;
    //         $sales_proforma->updated_by = Auth::id();
    //         $sales_proforma->save();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 4️⃣ CREATE PACKING ORDER
    //         |--------------------------------------------------------------------------
    //         */
    //         $packingOrder = PackingOrder::create([
    //             'code' => CodeRepo::generatePO(),
    //             'do_code' => $sales_order->code,
    //             'warehouse_id' => $sales_proforma->warehouse_id,
    //             'customer_id' => $sales_order->customer_id,
    //             'customer_other_address_id' => $sales_proforma->customer_other_address_id,
    //             'vendor_id' => $sales_proforma->vendor_id,
    //             'idr_rate' => $sales_proforma->so_idr_rate,
    //             'type_transaction' => $sales_proforma->so_type_transaction,
    //             'count_cancel' => 0,
    //             'status' => 2,
    //             'note' => $sales_proforma->note,
    //             'created_by' => Auth::id(),
    //             'so_id' => $sales_order->id,
    //         ]);

    //         $do_id = $packingOrder->id;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 5️⃣ COPY DETAIL COST
    //         |--------------------------------------------------------------------------
    //         */
    //         if ($sales_proforma->details_cost) {

    //             PackingOrderDetail::create([
    //                 'do_id' => $do_id,
    //                 'discount_1' => $sales_proforma->details_cost->discount_1_percent,
    //                 'discount_2' => $sales_proforma->details_cost->discount_2_percent,
    //                 'discount_1_idr' => $sales_proforma->details_cost->discount_1,
    //                 'discount_2_idr' => $sales_proforma->details_cost->discount_2,
    //                 'discount_idr' => $sales_proforma->details_cost->discount_idr,
    //                 'voucher_idr' => $sales_proforma->details_cost->voucher_idr,
    //                 'purchase_total_idr' => $sales_proforma->details_cost->purchase_total_idr,
    //                 'delivery_cost_idr' => $sales_proforma->details_cost->delivery_cost_idr,
    //                 'status_resi' => 0,
    //                 'grand_total_idr' => $sales_proforma->details_cost->grand_total_idr,
    //             ]);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 6️⃣ COPY ITEMS
    //         |--------------------------------------------------------------------------
    //         */
    //         foreach ($sales_proforma->items as $item) {

    //             $soItem = SalesOrderItem::where('so_id', $sales_order->id)
    //                 ->where('product_packaging_id', $item->product_packaging_id)
    //                 ->first();

    //             PackingOrderItem::create([
    //                 'product_packaging_id' => $item->product_packaging_id,
    //                 'do_id' => $do_id,
    //                 'so_item_id' => $soItem->id ?? null,
    //                 'qty' => $item->qty,
    //                 'price' => $item->price,
    //                 'usd_disc' => $item->disc_usd,
    //                 'packaging_id' => $item->packaging_id,
    //                 'total' => ($item->price - $item->disc_usd) * $item->qty,
    //                 'created_by' => Auth::id(),
    //                 'qty_worked' => $item->qty,
    //             ]);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 7️⃣ CREATE INVOICE
    //         |--------------------------------------------------------------------------
    //         */
    //         Invoicing::create([
    //             'code' => $sales_order->code,
    //             'do_id' => $do_id,
    //             'customer_id' => $sales_order->customer_id,
    //             'customer_other_address_id' => $sales_proforma->customer_other_address_id,
    //             'grand_total_idr' => $sales_proforma->details_cost->grand_total_idr ?? 0,
    //             'created_by' => Auth::id(),
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'notification' => [
    //                 'alert' => 'notify',
    //                 'type' => 'success',
    //                 'content' => 'SO berhasil diteruskan dan DO dibuat.'
    //             ],
    //             'redirect_to' => route('superuser.penjualan.so_proforma.index')
    //         ]);

    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         Log::error('Approval SO failed: ' . $e->getMessage());

    //         return response()->json([
    //             'notification' => [
    //                 'alert' => 'block',
    //                 'type' => 'alert-danger',
    //                 'header' => 'Error',
    //                 'content' => $e->getMessage()
    //             ]
    //         ], 500);
    //     }
    // }

    public function destroy(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 400);
        }

        DB::beginTransaction();

        try {

            // cek permission
            if (Auth::user()->is_superuser == 0) {
                if (empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses.'
                    ], 403);
                }
            }

            $sales_proforma = SalesOrderProforma::with(['items','details_cost'])
                ->find($id);

            if (!$sales_proforma) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proforma tidak ditemukan.'
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | CEK JIKA SUDAH DIPROSES
            |--------------------------------------------------------------------------
            */

            if ($sales_proforma->so_lanjutan == 1 || $sales_proforma->status == 4) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proforma sudah diproses dan tidak bisa dihapus.'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | RESET SALES ORDER
            |--------------------------------------------------------------------------
            */

            $sales_order = SalesOrder::find($sales_proforma->so_id);

            if ($sales_order) {
                $sales_order->is_proforma = 1;
                $sales_order->status_proforma = 0;
                $sales_order->updated_by = Auth::id();
                $sales_order->save();
            }

            /*
            |--------------------------------------------------------------------------
            | HAPUS RELASI PROFORMA
            |--------------------------------------------------------------------------
            */

            if ($sales_proforma->items) {
                $sales_proforma->items()->delete();
            }

            if ($sales_proforma->details_cost) {
                $sales_proforma->details_cost()->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | HARD DELETE PROFORMA
            |--------------------------------------------------------------------------
            */

            $sales_proforma->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proforma berhasil dihapus.'
            ]);

        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
        $customers = CustomerOtherAddress::where('situation', CustomerOtherAddress::SITUATION['ACTIVE'])
            ->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', $request->input('q', '') . '%');
            })
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

    public function rollbackProforma($so_id)
    {
        DB::beginTransaction();

        try {
            $sales_order = SalesOrder::find($so_id);
            if (!$sales_order) {
                throw new \Exception("Sales Order tidak ditemukan");
            }

            $proforma = SalesOrderProforma::where('so_id', $sales_order->id)->first();

            $deleted_items = 0;
            $deleted_header = 0;

            if ($proforma) {
                $deleted_items = SalesOrderProformaItem::where('so_proforma_id', $proforma->id)->delete();
                $deleted_header = $proforma->delete();
            }

            $sales_order->status_proforma = 0;
            $sales_order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proforma berhasil dirollback',
                'deleted_items' => $deleted_items,
                'deleted_header' => $deleted_header
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal rollback proforma: ' . $e->getMessage()
            ], 500);
        }
    }

    public function statusSiap($id)
    {
        DB::beginTransaction();

        try {
            $so_proforma = SalesOrderProforma::find($id);
            if (!$so_proforma) {
                throw new \Exception("Sales Order Proforma tidak ditemukan");
            }

            $sales_order = SalesOrder::find($so_proforma->so_id);
            if (!$sales_order) {
                throw new \Exception("Sales Order tidak ditemukan");
            }   

            $sales_order->status_proforma = 3;
            $sales_order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proforma berhasil diupdate ke status siap',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal update status proforma: ' . $e->getMessage()
            ], 500);
        }
    }

    public function MultiCancel($id)
    {
        DB::beginTransaction();

        try {
            $so_proforma = SalesOrderProforma::find($id);
            if (!$so_proforma) {
                throw new \Exception("Sales Order Proforma tidak ditemukan");
            }

            $sales_order = SalesOrder::find($so_proforma->so_id);
            if (!$sales_order) {
                throw new \Exception("Sales Order tidak ditemukan");
            }   

            if ($sales_order->status_proforma == 1) {
                $sales_order->status_proforma = 0;
                $sales_order->save();
            } elseif ($sales_order->status_proforma == 2) { 
                $sales_order->status_proforma = 1;
                $sales_order->save();
            } elseif ($sales_order->status_proforma == 3) {
                $sales_order->status_proforma = 2;
                $sales_order->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proforma berhasil diupdate ke status cancel',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal update status proforma: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printProforma($id)
    {
    
        $so = SalesOrderProforma::with([
            'items.productPack.product',
            'items.packaging',
            'details_cost'
        ])->findOrFail($id);
    
        // ambil grand total dari detail cost
        $grandTotal = $so->details_cost->grand_total_idr ?? 0;
    
        // ubah ke terbilang
        $terbilang = trim($this->terbilang($grandTotal));
    
        $pdf = PDF::loadView(
            'superuser.penjualan.so_proforma.pdf_proforma',
            [
                'so' => $so,
                'terbilang' => $terbilang
            ]
        )->setPaper('A5','landscape');
    
        return $pdf->stream('proforma-'.$so->code.'.pdf');
    
    }
    
    private function terbilang($angka)
    {
        $angka = abs($angka);
    
        $huruf = ["","Satu","Dua","Tiga","Empat","Lima","Enam","Tujuh","Delapan","Sembilan","Sepuluh","Sebelas"];
    
        if ($angka < 12) {
            return " ".$huruf[$angka];
        } elseif ($angka < 20) {
            return $this->terbilang($angka - 10)." Belas";
        } elseif ($angka < 100) {
            return $this->terbilang($angka / 10)." Puluh".$this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            return " Seratus".$this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return $this->terbilang($angka / 100)." Ratus".$this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return " Seribu".$this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return $this->terbilang($angka / 1000)." Ribu".$this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return $this->terbilang($angka / 1000000)." Juta".$this->terbilang($angka % 1000000);
        }
    
        return "";
    }
}