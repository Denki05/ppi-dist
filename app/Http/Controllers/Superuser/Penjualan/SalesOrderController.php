<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrderKontrak;
use App\Entities\Penjualan\SalesOrderKontrakItem;
use App\Entities\Penjualan\SalesOrderKontrakPivot;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\SoProforma;
use App\Entities\Penjualan\SoProformaDetail;
use App\Entities\Penjualan\DeliveryOrderMutationItem;
use App\Entities\Finance\Invoicing;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerCategory;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Company;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Packaging;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Sales;
use App\Entities\Master\Ekspedisi;
use App\Entities\Master\Vendor;
use App\Exports\Penjualan\SalesOrderAwalExport;
use App\Entities\Setting\UserMenu;
use App\Entities\Account\User;
use App\Repositories\CodeRepo;
use App\Helper\CustomHelper;
use Spatie\PdfToImage\pdf;
use App\DataTables\Penjualan\SalesOrderAwalTable;
use App\DataTables\Penjualan\SalesOrderLanjutanTable;
use Illuminate\Support\Facades\Response;
use Org_Heigl\Ghostscript\Ghostscript;
use App\Helper\LogActivity;
use App\Notifications\SoNotification;
use Illuminate\Support\Facades\Log;
use Imagick;
use Validator;
// use Twilio\Rest\Client;
use Auth;
use DB;
use COM;
use Carbon;
use Excel;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.penjualan.sales_order.";
        $this->route = "superuser.penjualan.sales_order";
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

    // public function index(Request $request, $step = NULL)
    // {
    //     // Access
    //     if(Auth::user()->is_superuser == 0){
    //         if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
    //             return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
    //         }
    //     }

    //     $search = $request->input('search');
    //     $so_for = $request->input('so_for');
    //     $customer_other_address_id = $request->input('customer_other_address_id');
    //     $status_so = $request->input('status_so');

    //     $table = SalesOrder::query()
    //                 ->when(!Auth::user()->is_superuser, function($query) use($step, $customer_other_address_id, $status_so) {
    //                     $query->when(!empty($step), function($query) use($step) {
    //                         if ($step === 1) {
    //                             $query->whereIn('status', [1, 2, 3, 4])
    //                                 ->where('so_for', 1)
    //                                 ->where('created_by', Auth::id());
    //                         } elseif ($step === 2) {
    //                             $query->whereIn('status', [2, 4])
    //                                 ->where('so_for', 1);
    //                         }
    //                     })
    //                     ->when(!empty($customer_other_address_id), function($query) use($customer_other_address_id) {
    //                         $query->whereHas('member', function($query) use($customer_other_address_id) {
    //                             $query->where('customer_other_address_id', $customer_other_address_id);
    //                         });
    //                     })
    //                     ->when(!empty($status_so), function($query) use($status_so) {
    //                         $query->where('status', $status_so);
    //                     })
    //                     ->where('type_so', 'nonppn')
    //                     ->where('so_indent', SalesOrder::INDENT['NO']);
    //                 })
    //                 ->orderBy('id', 'DESC')
    //                 ->get();

    //     $customers = Customer::get();
    //     $other_address = CustomerOtherAddress::where('situation', 1)->get();
    //     $brand = BrandLokal::get();
    //     $packing_order = PackingOrder::get();

    //     $data = [
    //         'customers' => $customers,
    //         'other_address' => $other_address,
    //         'packing_order' => $packing_order,
    //         'brand' => $brand,
    //         'step' => $step,
    //         'table' => $table,
    //         'step_txt' => SalesOrder::STEP[$step],
    //     ];

    //     return view($this->view."index",$data);
    // }

    public function json_awal(Request $request, SalesOrderAwalTable $datatable)
    {
        return $datatable->build($request);
    }

    public function json_lanjutan(Request $request, SalesOrderLanjutanTable $datatable)
    {
        return $datatable->build($request);
    }
    
    public function index_awal(Request $request, $step = 1)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $session = session('is_from_agenda', false);

        $search = $request->input('search');
        $so_for = $request->input('so_for');
        $customer_other_address_id = $request->input('customer_other_address_id');
        $status_so = $request->input('status_so');

        $customers = Customer::get();
        $brand = BrandLokal::get();
        $packing_order = PackingOrder::get();
        
        // Filter addresses based on user access
        $filtered_other_address = CustomerOtherAddress::get()->filter(function($address) {
            return $address->checkStore();
        });

        $data = [
            'customers' => $customers,
            'other_address' => $filtered_other_address, // Use filtered addresses
            'packing_order' => $packing_order,
            'brand' => $brand,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step] ?? '',
            'session' => $session,
        ];

        return view($this->view . "index_awal", $data);
    }
    
    public function index_lanjutan(Request $request, $step = 2)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $packing_order = PackingOrder::whereMonth('created_at', Carbon\Carbon::now()->month)
                            ->whereYear('created_at', Carbon\Carbon::now()->year)
                            ->get();
                            
        $so_progress = PackingOrder::whereMonth('created_at', Carbon\Carbon::now()->month)
                           ->whereYear('created_at', Carbon\Carbon::now()->year)
                           ->get();

        $data = [
            'packing_order' => $packing_order,
            'so_progress' => $so_progress,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step] ?? '',
        ];

        return view($this->view . "index_lanjutan", $data);
    }
    
    public function index_mutasi(Request $request)
    {
        return view("superuser.coming-soon");
        return $this->index($request, 9);
    }

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $result = SalesOrder::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }

        $step = 2;
        if ($result->status === 1 || $result->status === 3) {
            $step = 1;
        }

        $data = [
            'result' => $result,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
        ];
        return view($this->view."detail",$data);
    }

    public function data_so($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Join sales_orders with customers
        $result = DB::table('penjualan_so')
            ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
            ->select(
                'penjualan_so.*',
                'master_customer_other_addresses.name as customer_name',
                'master_customer_other_addresses.address AS customer_address', 
                'master_customer_other_addresses.text_kota AS customer_kota', 
                'master_customer_other_addresses.text_provinsi AS customer_provinsi',
            )
            ->where('penjualan_so.id', $id)
            ->first();

        // Query to retrieve products related to the sales order
        $products = DB::table('penjualan_so_item')
        ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
        ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
        ->select(
            'penjualan_so_item.*',
            'master_products_packaging.code AS code',
            'master_products_packaging.name AS name',
            'master_packaging.pack_name AS kemasan'
        )
        ->where('penjualan_so_item.so_id', $id)
        ->get();

        // Add products data to the result object
        $result->products = $products;

        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $step, $member, $brand, $type, $indent, $approval, $note, $kurs, $disc_percent)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $merek = BrandLokal::where('brand_name', $brand)->first();
        $products = Product::all();
        $other_address = CustomerOtherAddress::find($member);
        $warehouse = Warehouse::all();
		$ekspedisi = Ekspedisi::all();
        $sales = Sales::where('is_active', 1)->get();
        $product_category = ProductCategory::get();
        $type_transaction = $type;
        $type_indent = $indent;
        $rekenings = SalesOrder::REKENING;
        $approval_mou = $approval;
        $note_so = $note;
        $idr_rate = is_numeric($kurs) ? (float) $kurs : 0;
        $disc = is_numeric($disc_percent) ? (float) $disc_percent : 0;

        // dd($member); 

        $data = [
            'other_address' => $other_address,
            'merek' => $merek,
            'products' => $products,
            'sales' => $sales,
            'warehouse' => $warehouse,
            'ekspedisi' => $ekspedisi,
            'product_category' => $product_category,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
            'type_transaction' => $type_transaction,
            'type_indent' => $type_indent,
            'rekenings' => $rekenings,
            'approval_mou' => $approval_mou,
            'note_so' => $note_so,
            'idr_rate' => $idr_rate,
            'disc' => $disc,
        ];
        
        
        return view($this->view."create",$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $member)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'brand_name' => 'required',
                'type_transaction' => 'required',
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
            
            $get_store = CustomerOtherAddress::where('id', $member)->first();

            if ($validator->passes()) {
                try {
                    DB::beginTransaction();

                    $insert = new SalesOrder;
                    $insert->so_code = CodeRepo::generateSoAwal();
                    $insert->brand_name = $request->brand_name;
                    $insert->customer_id = $get_store->customer_id;
                    $insert->customer_other_address_id = $member;
                    $insert->type_transaction = $request->type_transaction;
                    $insert->so_for = 1;
                    $insert->so_date = null;
                    $insert->type_so = 'nonppn';
                    $insert->approval_mou = $request->approval;
                    $insert->idr_rate = $request->kurs;
                    $insert->catatan = $request->disc_percent;
                    $insert->note = $request->note_so;
                    $insert->created_by = Auth::id();

                    if($request->so_indent == "YES"){
                        $insert->code = null;
                        $insert->status = 1;
                        $insert->so_indent = 1;
                        $insert->indent_status = 1;
                    } elseif($request->so_indent == "NO"){
                        $insert->code = null;
                        $insert->status = $request->ajukankelanjutan ? 2 : 1;
                        $insert->so_indent = SalesOrder::INDENT['NO'];
                    }
                    $insert->condition = 1;
                    $insert->payment_status = 0;
                    $insert->count_rev = 0;
                    if($insert->save()){
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
                                     // Find the base product packaging ID if the current product is a clone
                                     $baseProductPackagingId = null;
                                     $product = ProductPack::where('id', $request->sku[$key])->first();
 
                                     // dd($product);
                                     if ($product) {
                                         // Check if it's a clone product and get the base product's packaging ID
                                         if (strpos($product->id, '_1') !== false) { 
                                             $baseProduct = ProductPack::where('id', str_replace('_1', '', $product->id))->first();
 
                                             // dd($baseProduct->id);
                                             if ($baseProduct) {
                                                 $baseProductPackagingId = $baseProduct->id;
                                             }
                                         } else {
                                             // It's a base product, use its packaging ID
                                             $baseProductPackagingId = $product->id;
                                         }
                                     }

                                    $insertDetail = new SalesOrderItem;
                                    $insertDetail->so_id = $insert->id;
                                    $insertDetail->kontrak = $request->value_kontrak[$key];
                                    $insertDetail->product_packaging_id = $baseProductPackagingId;
                                    $insertDetail->price = $request->price[$key];
                                    $insertDetail->qty = $request->qty[$key];
                                    $insertDetail->disc_usd = $request->disc[$key];
                                    $insertDetail->packaging_id = $request->packaging[$key];
                                    $insertDetail->free_product = $request->free_product[$key];
                                    $insertDetail->created_by = Auth::id();
                                    $insertDetail->status = 1;
                                    if($request->value_kontrak[$key] == 1){
                                        $insertDetail->kontrak_id = $request->kontrak_so_id[$key];
                                    }
                                    $insertDetail->save();

                                    if($request->value_kontrak[$key] == 1){
                                        $search_kontrak = SalesOrderkontrak::where('id', $request->kontrak_so_id[$key])->first();
                                        $item_kontrak = SalesOrderkontrakItem::where('so_kontrak_id', $search_kontrak->id)->first();


                                        if ($search_kontrak) {
                                            $log_kontrak = DB::table('penjualan_so_kontrak_log')
                                                ->where('so_kontrak_id', $search_kontrak->id)
                                                ->select(DB::raw('SUM(qty_worked) AS total_qty_kontrak'))
                                                ->first();
                    
                                            $sisa_qty = $item_kontrak->qty - ($log_kontrak->total_qty_kontrak ?? 0);
                    
                                            if ($sisa_qty < $request->qty[$key]) {
                                                DB::rollBack(); // Rollback the transaction if sisa_qty is insufficient
                                                $response['notification'] = [
                                                    'alert' => 'block',
                                                    'type' => 'alert-danger',
                                                    'header' => 'Error',
                                                    'content' => 'Sisa Kontrak <b>'. $item_kontrak->product_pack->name .'</b> tidak mencukupi..!!',
                                                ];
                                                
                                                // Return JSON response with a 500 HTTP status code
                                                return response()->json([
                                                    'IsError' => true,
                                                    'Notification' => $response['notification']
                                                ], 500);
                                            }
                                        }

                                        $pivot_kontrak = new SalesOrderKontrakPivot;
                                        $pivot_kontrak->so_item_id = $insertDetail->id;
                                        $pivot_kontrak->so_kontrak_item_id = $item_kontrak->id;
                                        $pivot_kontrak->save();
                                    }
                                }
                            }
                        }

                        DB::commit();

                        if($request->so_indent == "YES"){
                            LogActivity::addToLog('Created a new SO-Indent: ' . $insert->so_code);
                        } elseif ($request->so_indent == "NO") {
                            LogActivity::addToLog('Created a new SO: ' . $insert->so_code);
                        }

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.penjualan.sales_order.index_awal');
                        return $this->response(200, $response);
                    }
                }catch (\Exception $e) {
                    dd($e);
                    DB::rollBack(); // Rollback in case of any exception
                    Log::error('Sales Order creation failed: ' . $e->getMessage());
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'An error occurred while processing your request. Please try again later.',
                    ];
    
                    return $this->response(500, $response);
                }
            }
        }
    }

    // public function store_item(Request $request)
    // {
    //     $data_json = [];
    //     $post = $request->all();
    //     if($request->method() == "POST"){
    //         if(empty($post["so_id"])){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "ID Sales Order tidak boleh kosong";
    //             goto ResultData;
    //         }
    //         if(empty($post["product_id"])){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Product wajib dipilih";
    //             goto ResultData;
    //         }
    //         if(empty($post["qty"])){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Quantity tidak boleh kosong";
    //             goto ResultData;
    //         }
    //         if(empty($post["packaging"])){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Packaging tidak boleh kosong";
    //             goto ResultData;
    //         }
            
    //         $get_so_item = SalesOrderItem::where('so_id',$post["so_id"])
    //                                      ->where('product_id',$post["product_id"])
    //                                      ->where('packaging',$post["packaging"])
    //                                      ->first();
    //         if($get_so_item){
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Item sudah ada";
    //             goto ResultData;
    //         }
    //         $data = [
    //             'so_id' => trim(htmlentities($post["so_id"])),
    //             'product_id' => trim(htmlentities($post["product_id"])),
    //             'qty' => trim(htmlentities($post["qty"])),
    //             'packaging' => trim(htmlentities($post["packaging"])),
    //             'created_by' => Auth::id(),
    //         ];

    //         $insert = SalesOrderItem::create($data);

    //         if($insert){
    //             $data_json["IsError"] = FALSE;
    //             $data_json["Message"] = "Item Berhasil Ditambahkan ke SO";
    //             goto ResultData;
    //         }
    //         else{
    //             $data_json["IsError"] = TRUE;
    //             $data_json["Message"] = "Item Gagal Ditambahkan ke SO";
    //             goto ResultData;
    //         }
    //     }
    //     else{
    //         $data_json["IsError"] = TRUE;
    //         $data_json["Message"] = "Invalid Method";
    //         goto ResultData;
    //     }
    //     ResultData:
    //     return response()->json($data_json,200);
    // }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $step)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrder::where('id',$id)->first();
        // $result = SalesOrder::find($id);
        if(empty($result)){
            abort(404);
        }
        $customers = Customer::get();
        $warehouse = Warehouse::all();
        $sales = Sales::all();
        $product_category = ProductCategory::all();
        $brand = BrandLokal::get();
        $ekspedisi = Vendor::where('type', 1)->get();
        $packaging = Packaging::get();
        $rekening = DB::table('rekening')->get();

        $data = [
            'customers' => $customers,
            'warehouse' => $warehouse,
            'sales' => $sales,
            'product_category' => $product_category,
            'brand' => $brand,
            'ekspedisi' => $ekspedisi,
            'result' => $result,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
            'packaging' => $packaging,
            'rekening' => $rekening,
        ];
        
        if ($step == 2) {
            $doList = $result->member->do;
            $invoiceList = [];
            for ($i = 0; $i < sizeof($doList); $i++) {
                $do = $doList[$i];
                if (isset($do->invoicing)) {
                    $total_payable = 0;
                    for ($j = 0; $j < sizeof($do->invoicing->payable_detail); $j++) {
                        $payable_d = $do->invoicing->payable_detail[$j];
                        $total_payable += $payable_d->total;
                    }
                    if ($total_payable < $do->invoicing->grand_total_idr) {
                        // Ambil yang belum lunas terbayar
                        array_push($invoiceList, $do->invoicing);
                    }
                }
            }
            $data['customer_history'] = $invoiceList;
        }

        if ($step == 1 || $step == 9) {
            return view($this->view."edit",$data);
        } else if ($step == 2) {
            return view($this->view."create_lanjutan",$data);
        }
    }

    public function edit_item($id)
    {
        $result = SalesOrderItem::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $product_category = ProductCategory::all();
        $product_type = ProductType::all();
        $data = [
            'product_category' => $product_category,
            'product_type' => $product_type,
            'result' => $result,
        ];
        return view($this->view."edit_item",$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            $step = $post["step"];

            if(empty($post["id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "ID Sales Order tidak boleh kosong";
                goto ResultData;
            }

            $customer = [];
            $gudang = [];
            if(!empty($post["customer_id"])){
                $customer["id"] = empty($post["customer_id"]) ? null : $post["customer_id"];
                $customer["so_for"] = 1;
            }
            else{
                $gudang["id"] = empty($post["destination_warehouse_id"]) ? null : $post["destination_warehouse_id"];
                $customer["so_for"] = 2;
            }
            
            $sales_order = SalesOrder::find($post["id"]);

            DB::beginTransaction();
            try {
                
                if ($step == 1) {
                    $sales_order->type_transaction = trim(htmlentities($post["type_transaction"]));
                    $sales_order->catatan = trim(htmlentities($post["catatan"]));
                    $sales_order->brand_name = trim(htmlentities($post["brand_name"]));
                    $sales_order->idr_rate = trim(htmlentities($post["idr_rate"]));
                    $sales_order->note = trim(htmlentities($post["note"]));
                    $sales_order->updated_by = Auth::id();
                    $sales_order->status = $step;
                } else if ($step == 2) {
                    // di set statusnya, kalau dari front end dia di cancel, tidak di forward, maka status jadi 3 => awal perlu revisi
                    $data = [
                        'origin_warehouse_id' => trim(htmlentities($post["origin_warehouse_id"])),
                        'destination_warehouse_id' => $gudang["id"] ??  null,
                        'type_transaction' => trim(htmlentities($post["type_transaction"])),
                        'updated_by' => Auth::id(),
                        'status' => $step,
                        'ekspedisi_id' => (empty($post["ekspedisi_id"])) ? null : $post["ekspedisi_id"],
                    ];
                }
                if($sales_order->save()){
                    $search_so_items = SalesOrderItem::where('so_id', $post["id"])->get();  // Use get() to retrieve all items
                    if ($search_so_items->isNotEmpty()) {  // Check if any items were found
                        foreach ($search_so_items as $search_so_item) {
                            // Get all related SalesOrderKontrakPivot records for each found SalesOrderItem
                            $get_pivot_kontrak = SalesOrderKontrakPivot::where('so_item_id', $search_so_item->id)->get();

                            // Iterate through the retrieved SalesOrderKontrakPivot records
                            foreach ($get_pivot_kontrak as $row) {
                                // Delete each related pivot record
                                SalesOrderKontrakPivot::where('so_item_id', $row->so_item_id)->delete();
                            }
                        }
                    } else {
                        return response()->json(['error' => 'SalesOrderItem not found'], 404);
                    }

                    // deleted so item
                    $update_item = SalesOrderItem::where('so_id', $post["id"])->update(['status' => 0]);
                    $deleted_item = SalesOrderItem::where('so_id', $post["id"])->delete();
                    if (sizeof($post["sku"]) > 0) {
                        for ($i = 0; $i < sizeof($post["sku"]); $i++) {
                            // dd($post["so_kontrak"][$i]);

                            $duplicate_product = [];
                            $duplicate = false;
                            $listItem[] = [
                                'sku' => $post["sku"][$i],
                                'free_product' => $post["free_product"][$i],
                            ];

                            foreach($listItem as $row => $value){
                                if(in_array($value, $duplicate_product)) {
                                    $duplicate = true;
                                    break;
                                } else {
                                    array_push($duplicate_product, $value);
                                }

                                // dd($value); 
                            }

                            if($duplicate){
                                $data_json["IsError"] = TRUE;
                                $data_json["Message"] = "Item sudah ada";
                                goto ResultData;
                            }else{
                                $insertDetail = new SalesOrderItem;
                                $insertDetail->so_id = $sales_order->id;
                                $insertDetail->product_packaging_id =  $post["sku"][$i];
                                $insertDetail->price =  $post["price"][$i];
                                $insertDetail->qty = $post["qty"][$i];
                                $insertDetail->disc_usd = $post["disc"][$i];
                                $insertDetail->packaging_id = $post["packaging"][$i];
                                $insertDetail->kontrak = $post["so_kontrak_value"][$i];
                                $insertDetail->free_product = $post["free_product"][$i];
                                $insertDetail->created_by = Auth::id();
                                $insertDetail->save();
                                
                                // if ($post["so_kontrak_value"][$i] == 1) {
                                //     if ($post["kontrak_new"][$i] == 0) {
                                //         // If kontrak_new value is 0, find and associate with a specific kontrak item
                                //         $search_kontrak = SalesOrderkontrak::where('id', $request->so_kontrak)->first();
                                //         $item_kontrak = SalesOrderkontrakItem::where('so_kontrak_id', $search_kontrak->id)->first();
                                    
                                //         $pivot_kontrak = new SalesOrderKontrakPivot;
                                //         $pivot_kontrak->so_item_id = $insertDetail->id;
                                //         $pivot_kontrak->so_kontrak_item_id = $item_kontrak->id;
                                //         $pivot_kontrak->save();
                                //     }else{
                                //         // If kontrak value is 1, associate with a specific kontrak item
                                //         $pivot_kontrak = new SalesOrderKontrakPivot;
                                //         $pivot_kontrak->so_item_id = $insertDetail->id;
                                //         $pivot_kontrak->so_kontrak_item_id = $get_pivot_kontrak->so_kontrak_item_id;
                                //         $pivot_kontrak->save();
                                //     }
                                // }else {
                                //     continue;
                                // }
                                
                                
                            }
                        }
                    }
                }   
                DB::commit();

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Sales Order Berhasil Diubah";
                goto ResultData;
            } catch (\Exception $e) {

                dd($e);
                DB::rollback();

                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales Order Gagal Diubah";
    
                return response()->json($data_json,400);
            }
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function update_item(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            if(empty($post["id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "ID item so tidak boleh kosong";
                goto ResultData;
            }
            if(empty($post["product_id"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Product wajib dipilih";
                goto ResultData;
            }
            if(empty($post["qty"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Quantity tidak boleh kosong";
                goto ResultData;
            }
            if(empty($post["packaging"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Packaging tidak boleh kosong";
                goto ResultData;
            }

            $result = $update = SalesOrderItem::where('id',$post["id"])->first();
            $get_so_item = SalesOrderItem::where('id','!=',$post["id"])
                                         ->where('so_id',$result->so_id)
                                         ->where('product_id',$post["product_id"])
                                         ->where('packaging',$post["packaging"])
                                         ->first();
            if($get_so_item){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Item sudah ada";
                goto ResultData;
            }
            $data = [
                'product_id' => trim(htmlentities($post["product_id"])),
                'qty' => trim(htmlentities($post["qty"])),
                'packaging' => trim(htmlentities($post["packaging"])),
                'updated_by' => Auth::id(),
            ];

            $update = SalesOrderItem::where('id',$post["id"])->update($data);

            if($update){
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Item Berhasil Diubah dan Ditambahkan ke SO";
                goto ResultData;
            }
            else{
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Item Gagal Diubah dan Ditambahkan ke SO";
                goto ResultData;
            }
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function lanjutkan(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $sales_order = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            $sales_order->status = 2;

            if($sales_order->save()) {
                $user = User::find(33);
                $user->notify(new SoNotification($sales_order));

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];
    
                $response['redirect_to'] = route('superuser.penjualan.sales_order.index_awal');
                return $this->response(200, $response);
            }
            
        }
    }

    public function kembali(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();

            try{

                $errors = [];
                
                $sales_order = SalesOrder::find($id);

                if($sales_order == null){
                    $errors[] = 'Sales Order , tidak ditemukan!';
                }

                $sales_order->status = 3;
                $sales_order->updated_by = Auth::id();
                if($sales_order->save()){
                    if($errors) {
                        $response['notification'] = [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => $errors,
                        ];
    
                        return $this->response(400, $response);
                    } else {
                        DB::commit();
                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
            
                        $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                        return $this->response(200, $response);
                    }
                }


            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $errors,
                ];

                return $this->response(400, $response);
            }
        }

        // // Access
        // if(Auth::user()->is_superuser == 0){
        //     if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
        //         return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
        //     }
        // }

        // DB::beginTransaction();
        // try{
        //     $request->validate([
        //         'id' => 'required'
        //     ]);
        //     $post = $request->all();
        //     $update = SalesOrder::where('id',$post["id"])->update(['status' => 3]);

        //     DB::commit();
        //     return redirect()->back()->with('success','Sales Order tidak di lanjutkan');  
            
        // }catch(\Throwable $e){
        //     DB::rollback();
        //     return redirect()->back()->with('error',$e->getMessage());
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Access Control
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // AJAX Request Check
        if ($request->ajax()) {
            $sales_order = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                // Update deleted_by and delete the SalesOrder
                $update = SalesOrder::where('id', $sales_order->id)->update(['deleted_by' => Auth::id()]);
                $destroy = SalesOrder::where('id', $sales_order->id)->delete();

                // Get all SalesOrder items
                $so_item = SalesOrderItem::where('so_id', $sales_order->id)->get();

                // Check if items are used in PackingOrder or DeliveryOrderMutation
                foreach ($so_item as $index => $value) {
                    $check_do_item = PackingOrderItem::where('so_item_id', $value->id)->first();
                    $check_do_mutation_item = DeliveryOrderMutationItem::where('so_item_id', $value->id)->first();
                    if ($check_do_item || $check_do_mutation_item) {
                        return redirect()->back()->with('error', 'Gagal menghapus Item. Item SO ini sudah digunakan di Packing Order / Delivery Order Mutation');
                    }
                }

                // Delete all SalesOrder items
                $destroy_item = SalesOrderItem::where('so_id', $sales_order->id)->delete();

                DB::commit();
                // return redirect()->back()->with('success', 'SO berhasil dihapus');
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];
    
                $response['redirect_to'] = route('superuser.penjualan.sales_order.index_awal');
                return $this->response(200, $response);
            } catch (\Throwable $e) {
                DB::rollback();
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
    }


    public function destroy_item(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $update = SalesOrderItem::where('id',$post["id"])->update(['deleted_by' => Auth::id()]);
            $destroy = SalesOrderItem::where('id',$post["id"])->delete();
            $check_do_item = PackingOrderItem::where('so_item_id',$post["id"])->first();
            $check_do_mutation_item = DeliveryOrderMutationItem::where('so_item_id',$post["id"])->first();
            if($check_do_item || $check_do_mutation_item){
                return redirect()->back()->with('error','Gagal menghapus SO . Item di SO sudah digunakan di Packing Order / Delivery Order Mutation');
            }
            DB::commit();
            return redirect()->back()->with('success','Item berhasil dihapus');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function tidak_lanjut_so(Request $request) {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            $sales_order = SalesOrder::find($post["id"]);
            if(empty($sales_order)){
                abort(404);
            }

            if(empty($post["keterangan"])){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Keterangan wajib diisi";
                goto ResultData;
            }

            DB::beginTransaction();
            try {
                $sales_order->keterangan_tidak_lanjut = trim(htmlentities($post["keterangan"]));
                $sales_order->status = 3;
                $sales_order->save();
                    
                DB::commit();

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Sales Order Berhasil Diubah";
                goto ResultData;
            } catch (\Exception $e) {
                DB::rollback();

                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales Order Gagal Diubah, ".$e;
    
                return response()->json($data_json,400);
            }
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            return response()->json($data_json,400);
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function tutup_so(Request $request)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();
            try{
                $errors = [];
                
                $sales_order = SalesOrder::find($request->id);

                if($sales_order === null){
                    abort(404);
                }

                if($sales_order->count_rev == 0){
                    if($request->origin_warehouse_id == null){
                        $errors[] = 'Warehouse tidak boleh kosong!';
                    }

                    if($request->rekening == null){
                        $errors[] = 'Rekening tidak boleh kosong!';
                    }
                    
                    $sales_order->code = CodeRepo::generateSO();
                    $sales_order->origin_warehouse_id = $request->origin_warehouse_id;
                    $sales_order->sales_senior_id = $request->sales_senior_id;
                    $sales_order->sales_id = $request->sales_id;
                    
                    $sales_order->ekspedisi_id = $request->ekspedisi ?? null;
                    $sales_order->so_date = $request->so_date;
                    $sales_order->rekening = $request->rekening;
                    $sales_order->shipping_cost_buyer = $request->shipping_cost_buyer ?? 0;
                    $sales_order->status = 4;
                    $sales_order->count_rev = 0;
                    $sales_order->updated_by = Auth::id();
                    if($sales_order->save()){
                        $packing_order = new PackingOrder;
                        $packing_order->code = CodeRepo::generatePO();
                        $packing_order->do_code = $sales_order->code;
                        $packing_order->so_id  = $sales_order->id;
                        $packing_order->customer_id  = $sales_order->customer_id;
                        $packing_order->customer_other_address_id  = $sales_order->customer_other_address_id;
                        $packing_order->warehouse_id = $sales_order->origin_warehouse_id;
                        $packing_order->type_transaction  = $sales_order->type_transaction;
                        $packing_order->idr_rate = $request->idr_rate;
                        $packing_order->other_address = 0 ?? Null;
                        $packing_order->note = $company->note ?? null;
                        $packing_order->pic = $sales_order->customer->pic;
                        $packing_order->officer = $sales_order->member->officer;
                        $packing_order->account_representative = $sales_order->created_by;
                        $packing_order->vendor_id = $sales_order->ekspedisi_id ?? null;
                        $packing_order->status = 2;
                        $packing_order->count_cancel = 0;
                        $packing_order->created_by = Auth::id();
                        $packing_order->save();

                        // definisi hasil penjumlahan di view
                        $discount_agen_idr = $request->disc_agen_idr;
                        $discount_kemasan_idr = $request->disc_kemasan_idr;
                        $sub_total = $request->subtotal_2;
                        $grand_total_idr = $request->grand_total_idr;

                        if($grand_total_idr == null){
                            $errors[] = 'Grand Total tidak boleh kosong!';
                        }

                        // pecah format currency 
                        $discount_agen_idr = str_replace('.', '', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace('.', '', $discount_kemasan_idr);
                        $sub_total = str_replace('.', '', $sub_total);
                        $grand_total_idr = str_replace('.', '', $grand_total_idr);
                        
                        // ubah decimal koma ke titik
                        $discount_agen_idr = str_replace(',', '.', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace(',', '.', $discount_kemasan_idr);
                        $sub_total = str_replace(',', '.', $sub_total);
                        $grand_total_idr = str_replace(',', '.', $grand_total_idr);

                        $packing_order_detail = new PackingOrderDetail;
                        $packing_order_detail->do_id = $packing_order->id;
                        $packing_order_detail->discount_1 = $request->disc_agen_percent;
                        $packing_order_detail->discount_1_idr = $discount_agen_idr;
                        $packing_order_detail->discount_2 = $request->disc_kemasan_percent;
                        $packing_order_detail->discount_2_idr = $discount_kemasan_idr;
                        $packing_order_detail->discount_idr = $request->disc_tambahan_idr;
                        $packing_order_detail->voucher_idr = $request->voucher_idr;
                        $packing_order_detail->purchase_total_idr = $sub_total;
                        if($sales_order->shipping_cost_buyer == 0){
                            $packing_order_detail->delivery_cost_idr = $request->delivery_cost_idr;
                        }elseif($sales_order->shipping_cost_buyer == 1){
                            $packing_order_detail->delivery_cost_idr = 0;
                        }
                        $packing_order_detail->other_cost_idr = 0;
                        $packing_order_detail->grand_total_idr = $grand_total_idr;
                        $packing_order_detail->terbilang = CustomHelper::terbilang($grand_total_idr);
                        $packing_order_detail->created_by = Auth::id();
                        $packing_order_detail->save();

                        $data = [];
                        $product = 0;
                        $out_of_stock = false;
                        foreach($request->repeater as $key => $value){
                            $result = SalesOrderItem::where('id', $value["so_item_id"])->first();

                            $so_item_id = $value["so_item_id"];
                            $price = $value["price"];
                            $so_qty = $value["so_qty"];
                            $do_qty = $value["do_qty"];
                            $rej_qty = $so_qty - $do_qty;
                            $usd_disc = $value["usd_disc"];
                            $percent_disc = 0;
                            $total_discount = 0;

                            if(empty($value["so_item_id"])){
                                $errors[] = 'SO Item ID tidak boleh kosong';
                            }

                            if(empty($value["product_packaging_id"])){
                                $errors[] = 'Product ID tidak boleh kosong';
                            }

                            $qty_total = $do_qty + $rej_qty;
                            $sisa = $so_qty - $do_qty;

                            if($so_qty < $qty_total){
                                $errors[] = 'Jumlah DO,REJ melebihi SO Qty';
                            }
        
                            if($do_qty == 0 && $rej_qty == 0){
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty' => 0
                                ]);
                            }

                            if($do_qty > 0){
                                $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc/100))) * $do_qty);
                                $data[] = [
                                    'do_id' => $packing_order->id,
                                    'product_packaging_id' => $value["product_packaging_id"],
                                    'so_item_id' => $value["so_item_id"],
                                    'packaging_id' => $value["packaging"],
                                    'qty' => $do_qty,
                                    'price' => $price,
                                    'usd_disc' => $usd_disc,
                                    'percent_disc' => $percent_disc,
                                    'total_disc' => $total_disc,
                                    'total' => floatval($do_qty * $price) - $total_disc,
                                    'created_by' => Auth::id(),
                                ];
        
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty_worked' => $do_qty
                                ]);
                            }

                            // Check Stock
                            // $stock = DB::table('master_product_min_stocks')
                            //             ->where('warehouse_id', $request->origin_warehouse_id)
                            //             ->where('product_packaging_id', $value["product_packaging_id"])
                            //             ->first();
                            
                            // if($stock){
                            //     if($stock->quantity < $do_qty){
                            //         $out_of_stock = true;
                            //         $product = $value["product_packaging_id"];
                            //         break;
                            //     }
                            // }

                             // Extract the base product-packaging ID (remove suffix like "_1", "_2")
                             $base_product_packaging_id = preg_replace('/_\d+$/', '', $value["product_packaging_id"]);
                             // dd($base_product_packaging_id);
                             // dd($base_product_packaging_id);
 
                             // Check stock only for the primary variant (no suffix)
                             $stock = DB::table('master_product_min_stocks')
                                         ->where('warehouse_id', $request->origin_warehouse_id)
                                         ->where('product_packaging_id', $base_product_packaging_id) // Only the base ID
                                         ->first();

                            if ($stock) {
                                // Only check stock if do_qty is greater than 0
                                if ($do_qty > 0 && ($stock->quantity < $do_qty || $stock->quantity < 0)) {
                                    $out_of_stock = true;
                                    $product = $value["product_packaging_id"];
                                    break;
                                }
                            }
        
                            if(empty($do_qty) && $rej_qty > 0){
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty_worked' => $do_qty
                                ]);
                            }
                        }

                        if (count($data) == 0) {
                            DB::rollback();
                            $errors[] =  'Not item sales order are ready';
                        }

                        if($out_of_stock){
                            $product_check = ProductPack::find($product);
                            $errors[] = 'Out Of Stock! <b>'.$product_check->code.' - '.$product_check->name.'</b> Please contact Administrator';
                            DB::rollback();
                        }else{
                            foreach ($data as $key => $value) {
                                $insert = PackingOrderItem::create($data[$key]);
                            }

                            // Cetak Invoice disini
                            // if(empty($packing_order->invoicing)){
                            //     $data = [
                            //         'code' => $sales_order->code,
                            //         'do_id' => $packing_order->id,
                            //         'customer_id' => $sales_order->customer_id,
                            //         'customer_other_address_id' => $sales_order->customer_other_address_id,
                            //         'grand_total_idr' => $packing_order_detail->grand_total_idr,
                            //         'created_by' => Auth::id(),
                            //     ];

                            //     $insert_invoice = Invoicing::create($data);
                            // }

                            $invoice = Invoicing::where('do_id', $packing_order->id)->first();

                            if(!$invoice){
                                Invoicing::create([
                                    'code' => $sales_order->code,
                                    'do_id' => $packing_order->id,
                                    'customer_id' => $sales_order->customer_id,
                                    'customer_other_address_id' => $sales_order->customer_other_address_id,
                                    'grand_total_idr' => $packing_order_detail->grand_total_idr,
                                    'created_by' => Auth::id(),
                                ]);
                            }
                        }

                        DB::commit();
                        if($errors) {
                            $response['notification'] = [
                                'alert' => 'block',
                                'type' => 'alert-danger',
                                'header' => 'Error',
                                'content' => $errors,
                            ];
        
                            return $this->response(400, $response);
                        } else {
                            $response['notification'] = [
                                'alert' => 'notify',
                                'type' => 'success',
                                'content' => 'Success',
                            ];
                
                            $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                            return $this->response(200, $response);
                        }
                    }
                }elseif($sales_order->count_rev == 1){
                    if($request->origin_warehouse_id == null){
                        $errors[] = 'Warehouse tidak boleh kosong!';
                    }

                    if($request->rekening == null){
                        $errors[] = 'Rekening tidak boleh kosong!';
                    }
                    
                    if($request->keep_old_code == 1){
                        $sales_order->code = $sales_order->keep_code;
                    }else{
                        $sales_order->code = CodeRepo::generateSO();
                    }
                    $sales_order->origin_warehouse_id = $request->origin_warehouse_id;
                    $sales_order->sales_senior_id = $request->sales_senior_id;
                    $sales_order->sales_id = $request->sales_id;
                    $sales_order->rekening = $request->rekening;
                    $sales_order->so_date = $request->so_date;
                    $sales_order->status = 4;
                    $sales_order->count_rev = 0;
                    $sales_order->updated_by = Auth::id();

                    if($request->origin_warehouse_id == null){
                        $errors[] = 'Warehouse tidak boleh kosong!';
                    }

                    if($request->rekening == null){
                        $errors[] = 'Rekening tidak boleh kosong!';
                    }

                    $valuePoDetail = [];
                    if($sales_order->save()){
                        
                        $jumlahitem = 0;
                        $data = [];

                        $get_po = PackingOrder::where('so_id', $sales_order->id)->first();

                        foreach ($request->repeater as $key => $value) {
                            if (empty($value["so_qty"]) || (!empty($value["so_qty"]) && $value["so_qty"] <= 0)) {
                                continue;
                            }

                            $result = SalesOrderItem::where('id',$value["so_item_id"])->first();
                           

                            $jumlahitem = $jumlahitem + 1;

                            $so_item_id = $value["so_item_id"];
                            $price = $value["price"];
                            $so_qty = $value["so_qty"];
                            $do_qty = $value["do_qty"];
                            $rej_qty = $so_qty - $do_qty;
                            $usd_disc = $value["usd_disc"];
                            $percent_disc = 0;
                            $total_discount = 0;


                            if($value["so_item_id"] == null){
                                $errors[] = 'SO Item ID tidak boleh kosong';
                            }
                            if($value["product_packaging_id"] == null){
                                $errors[] = 'Product ID tidak boleh kosong';
                            }
                            if($value["price"] == null){
                                $errors[] = 'Harga tidak boleh kosong';
                            }

                            $qty_total = $do_qty + $rej_qty;
                            $sisa = $so_qty - $do_qty;

                            if($so_qty < $qty_total){
                                $errors[] = 'Jumlah DO,REJ melebihi SO Qty';
                            }

                            if($do_qty == 0 && $rej_qty == 0){
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty' => 0
                                ]);
                            }

                            if($do_qty > 0){
                                $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc/100))) * $do_qty);
                                $data[] = [
                                    'do_id' => $get_po->id,
                                    'product_packaging_id' => $value["product_packaging_id"],
                                    'so_item_id' => $value["so_item_id"],
                                    'packaging_id' => $result->packaging_id,
                                    'qty' => $do_qty,
                                    'price' => $price,
                                    'usd_disc' => $usd_disc,
                                    'percent_disc' => $percent_disc,
                                    'total_disc' => $total_disc,
                                    'total' => floatval($do_qty * $price) - $total_disc,
                                    'created_by' => Auth::id(),
                                ];
                        
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty_worked' => $do_qty
                                ]);
                            }
                        
                            if(empty($do_qty) && $rej_qty > 0){
                                $updateSO = SalesOrderItem::where('id',$value["so_item_id"])->update([
                                    'qty_worked' => $do_qty
                                ]);
                            }
                        }
    
                        $updatePo = PackingOrder::where('id', $get_po->id)->update([
                            'status' => 2,
                            'do_code' => $sales_order->code,
                            'idr_rate' => $request->idr_rate,
                        ]);

                        // definisi hasil penjumlahan di view
                        $discount_agen_idr = $request->disc_agen_idr;
                        $discount_kemasan_idr = $request->disc_kemasan_idr;
                        $disc_tambahan_idr = $request->disc_tambahan_idr;
                        $sub_total = $request->subtotal_2;
                        $grand_total_idr = $request->grand_total_idr;

                        // pecah format currency 
                        $discount_agen_idr = str_replace('.', '', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace('.', '', $discount_kemasan_idr);
                        $sub_total = str_replace('.', '', $sub_total);
                        $grand_total_idr = str_replace('.', '', $grand_total_idr);
                        
                        // ubah decimal koma ke titik
                        $discount_agen_idr = str_replace(',', '.', $discount_agen_idr);
                        $discount_kemasan_idr = str_replace(',', '.', $discount_kemasan_idr);
                        $sub_total = str_replace(',', '.', $sub_total);
                        $grand_total_idr = str_replace(',', '.', $grand_total_idr);
    
                        $valuePoDetail[] = [
                            'discount_1' => $request->disc_agen_percent,
                            'discount_2' => $request->disc_kemasan_percent,
                            'discount_1_idr' => $discount_agen_idr,
                            'discount_2_idr' => $discount_kemasan_idr,
                            'discount_idr' => $disc_tambahan_idr,
                            'voucher_idr' => $request->voucher_idr,
                            'purchase_total_idr' => $sub_total,
                            'delivery_cost_idr' => $request->delivery_cost_idr,
                            'other_cost_idr' => $request->resi_ongkir ?? 0,
                            'grand_total_idr' => $grand_total_idr,
                            'updated_by' => Auth::id(),
                            'created_by' => Auth::id(),
                        ];
    
                        if($get_po->status == 7){
                            foreach ($valuePoDetail as $key => $value) {
                                $updatePoDetail = PackingOrderDetail::where('do_id', $get_po->id)->update($valuePoDetail[$key]);
                            }
    
                            foreach( $data as $key => $value ){
                                $insertItem = PackingOrderItem::create($data[$key]);
                            }
                            
                        }

                        if(empty($get_po->invoicing)){
                            $data = [
                                'code' => $sales_order->code,
                                'do_id' => $get_po->id,
                                'customer_id' => $sales_order->customer_id,
                                'customer_other_address_id' => $sales_order->customer_other_address_id,
                                'grand_total_idr' => $grand_total_idr,
                                'status' => 1,
                                'created_by' => Auth::id(),
                            ];

                            $insert_invoice = Invoicing::create($data);
                        }else{
                            $data = [
                                'code' => $sales_order->code,
                                'do_id' => $get_po->id,
                                'customer_id' => $sales_order->customer_id,
                                'customer_other_address_id' => $sales_order->customer_other_address_id,
                                'grand_total_idr' => $grand_total_idr,
                                'status' => 1,
                                'created_by' => Auth::id(),
                            ];

                            $update_invoice = Invoicing::where('do_id', $get_po->id)->update($data);
                        }

                        DB::commit();

                        LogActivity::addToLog('Closed SO: ' . $sales_order->so_code);

                        if($errors) {
                            $response['notification'] = [
                                'alert' => 'block',
                                'type' => 'alert-danger',
                                'header' => 'Error',
                                'content' => $errors,
                            ];
        
                            return $this->response(400, $response);
                        } else {
                            $response['notification'] = [
                                'alert' => 'notify',
                                'type' => 'success',
                                'content' => 'Success',
                            ];
                
                            $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                            return $this->response(200, $response);
                        }
                    }
                }

            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $errors,
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function ajax_customer_detail(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                $result = Customer::where('id',$post["id"])->first();
                $data_json["IsError"] = FALSE;
                $data_json["Data"] = $result;
                goto ResultData;

            }catch(\Throwable $e){

                // dd($e);
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultData;
            }
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function ajax_warehouse_detail(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                $result = Warehouse::where('id',$post["id"])->first();

                $data_json["IsError"] = FALSE;
                $data_json["Data"] = $result;
                goto ResultData;

            }catch(\Throwable $e){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultData;
            }
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function print_rejected_so($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrder::where('id',$id)->first();
        $company = Company::first();
        if(empty($result)){
            abort(404);
        }

        $data = [
            'result' => $result,
            'company' => $company
        ];

        $pdf = PDF::loadview($this->view."print_rejected_so",$data)->setPaper('a5','potrait');
        return $pdf->stream($result->code ?? '');
    }

    public function print_proforma($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrder::where('id',$id)->first();

        // GET DO & ITEM
        $get_do = PackingOrder::where('so_id', $result->id)->first();

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\proforma\\proforma.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\proforma\\export\\'.$result->code.'.pdf';
       
        //- Variables - Server Information 
        $my_server = "DEV-SERVER"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";


        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{penjualan_do.id}= $get_do->id";


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

    public function get_product(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "GET"){
            $table = Product::where(function($query2) use($post){
                        if(!empty($post["brand_name"])){
                            $query2->where('brand_name', $post["brand_name"]);
                        }
                    })
                    ->selectRaw(
                        'master_products.id as id, 
                        master_products.name as productName, 
                        master_products.code as productCode, 
                        master_products.selling_price as productPrice'
                    )
                    ->get();
            $data_json["IsError"] = FALSE;
            $data_json["Data"] = $table;
            goto ResultData;
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function get_packaging(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "GET"){
            $table = ProductPack::where(function($query2) use($post){
                if(!empty($post["product_id"])){
                    $query2->where('product_id', $post["product_id"]);
                }
            })
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('master_product_types', 'master_products_packaging.type_id', '=', 'master_product_types.id')
            ->selectRaw(
                'master_packaging.id, master_packaging.pack_name, master_product_types.name as type'
            )
            ->get();
            $data_json["IsError"] = FALSE;
            $data_json["Data"] = $table;
            goto ResultData;
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function destroy_lanjutan(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }

        if ($request->ajax()) {
            $sales_order = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            if ($sales_order->count_rev > 0) {
                return $this->response(400, ['failed' => 'Invoice sudah terbuat!']);
            }

            $sales_order->deleted_by = Auth::id();
            $sales_order->delete();

            if($sales_order->save()){
                foreach ($sales_order->so_detail as $detail) {
                    SalesOrderItem::where('id', $detail->id)->delete();
                }

                LogActivity::addToLog('Deleted SO-Lanjutan: ' . $sales_order->so_code);
                $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                return $this->response(200, $response);
            }
        }
    }


    public function indent(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();

            try{

                $result = SalesOrder::find($id);

                if($result == null){
                    abort(404);
                }

                $result->status = 6;
                $result->code = null;
                $result->indent_status = 1;
                $result->updated_by = Auth::id();

                if($result->save()){
                    DB::commit();
                    $response['redirect_to'] = route('superuser.penjualan.sales_order_indent.index');
                    return $this->response(200, $response);
                }

            }catch (\Exception $e) {
                DB::rollback();
                DD($e);
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

    // indent button from SO Lanjutan

    public function kembali_hold(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();

            try{
                $errors = [];

                $sales_order = SalesOrder::find($id);

                // check invoice apa sudah terbuat?
                $do = PackingOrder::where('so_id', $sales_order->id)->first();

                if(!empty($do->invoicing)){
                    $errors[] = 'Invoice sudah terbuat, tidak bisa melakukan indent!';
                }

                $sales_order->status = 5;
                $sales_order->indent_status = 2;
                $sales_order->catatan = $request->catatan_kembali;
                $sales_order->updated_by = Auth::id();
                if($sales_order->save()){
                    DB::commit();
                    if($errors) {
                        $response['notification'] = [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => $errors,
                        ];
    
                        return $this->response(400, $response);
                    } else {
                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
            
                        $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                        return $this->response(200, $response);
                    }
                }
            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $errors,
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function get_brand(Request $request)
    {
        $brands = BrandLokal::where('status', BrandLokal::STATUS['ACTIVE'])
            ->where(function ($query) use ($request) {
                $query->where('brand_name', 'LIKE', $request->input('q', '') . '%');
            })
            ->get();

        $results = [];

        foreach ($brands as $item) {
            $results[] = [
                'id' => $item->brand_name,
                'text' => $item->brand_name,
            ];
        }

        return ['results' => $results];
    }

    public function get_product_pack(Request $request)
    {
        if ($request->ajax()) {
                $data = [];
                
                $product = Product::where('master_products.brand_name', $request->id)
                        // ->where('master_products.status', 1)
                        ->where('master_products.on_order', 1)
                        ->leftJoin('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
                        ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                        ->leftJoin('master_product_types', 'master_products_packaging.type_id', '=', 'master_product_types.id')
                        ->leftJoin('master_warehouses', 'master_products_packaging.warehouse_id', '=', 'master_warehouses.id')
                        ->select('master_products_packaging.id as id' ,
                                    'master_products_packaging.code as ProductCode', 
                                    'master_products_packaging.name as productName', 
                                    'master_products_packaging.price as productPrice', 
                                    'master_packaging.id as  productPackagingID', 
                                    'master_packaging.pack_name as productPackaging', 
                                    'master_warehouses.name as warehouseName',
                                    'master_product_types.name as typeName',
                        )
                        ->get();

                foreach($product as $key){
                    $data[] = [
                        'id' => $key->id,
                        'code' => $key->ProductCode,
                        'name' => $key->productName,
                        'price' => $key->productPrice,
                        'packName' => $key->productPackaging,
                        'packID' => $key->productPackagingID,
                        'warehouseName' => $key->warehouseName,
                        'typeName' => $key->typeName,
                    ];
                }

                return response()->json(['code' => 200, 'data' => $data]);
        }
    }

    public function print_so($so_id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrder::where('id',$so_id)->first();

       
        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\so\\nota_penjualan.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\so\\export\\'.$result->so_code.'.pdf';

        //- Variables - Server Information 
        $my_server = "LOCAL"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";


        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{penjualan_so.id}= $result->id";


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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\so\\export\\'.$result->so_code.'.pdf';

        $imagick = new Imagick();

        $imgPath = public_path('\cr\\so\\export\\'.$result->so_code.'.pdf');
        $imgSavePath = public_path('\cr\\so\\export\\images\\'.$result->so_code.'.jpg');
        $imagick->setResolution(300, 300);
        $imagick->readImage($imgPath.'[0-4]'); // read only the first 5 pages
        $imagick->resetIterator();
        $imagick = $imagick->appendImages(true);
        $imagick->writeImages($imgSavePath, true); 

        return response()->file($imgSavePath);
    }

    public function updateBrandName(Request $request)
    {
        $sales_order = SalesOrder::leftJoin('penjualan_so_item', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
                                ->select(
                                    'penjualan_so.id as invoice_id', 
                                    'penjualan_so.code as invoice', 
                                    'penjualan_so.brand_name as brand_invoice', 
                                    'penjualan_so.status as status_so', 
                                    'penjualan_so_item.product_packaging_id as product_pack', 
                                )
                                ->where('penjualan_so.status', 4)
                                ->orWhere('penjualan_so.brand_name', NULL)
                                ->get();

        foreach($sales_order as $row){
            $find = false;

            $product = DB::table('penjualan_so_item')
                            ->select(
                                'master_products_packaging.id as child_id',
                                'master_products.id as parent_id',
                                'master_products.brand_name as brand_name',
                            )
                            ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
                            ->leftJoin('master_products', 'master_products.id', '=', 'master_products_packaging.product_id')
                            ->where('penjualan_so_item.so_id', $row->invoice_id)
                            ->get();

            foreach($product as $item){
                if(!$find){
                    $data = SalesOrder::find($row->invoice_id);

                    $data->brand_name = $item->brand_name;
                    $data->save();
                    
                    $find = true;
                }
            }
        }

        return redirect()->back()->with('message', 'Berhasil Update!');
    }

    public function export(Request $request)
    {
        $filename = 'Sales-Order-Report-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new SalesOrderAwalExport, $filename);
    }

    public function search_kontrak(Request $request, $id, $merek)
    {
        // Validate the input data
        $validatedData = $request->validate([
            'q' => 'nullable|string|max:255',
        ]);
    
        // Additional validation for $id and $merek
        if (!is_numeric($id) || empty($merek)) {
            return response()->json([
                'message' => 'Invalid request data.',
                'errors' => [
                    'id' => 'The ID must be a number.',
                    'merek' => 'The brand name is required.'
                ]
            ], 422);
        }
    
        try {

            $excludedCustomerId = 118.1;
            // Perform the query to search for contracts, excluding fulfilled items
            $sales_kontrak = SalesOrderKontrak::where('penjualan_so_kontrak.status', 2)
                ->where('penjualan_so_kontrak.customer_other_address_id', $id)
                ->where('master_products.brand_name', $merek)
                ->when($request->has('q'), function ($query) use ($validatedData) {
                    // Apply search filter if 'q' parameter is provided
                    $query->where('master_products_packaging.name', 'LIKE', '%' . $validatedData['q'] . '%');
                })
                ->leftJoin('penjualan_so_kontrak_item', 'penjualan_so_kontrak.id', '=', 'penjualan_so_kontrak_item.so_kontrak_id')
                ->leftJoin('master_products_packaging', 'penjualan_so_kontrak_item.product_packaging_id', '=', 'master_products_packaging.id')
                ->leftJoin('master_products', 'master_products.id', '=', 'master_products_packaging.product_id')
                ->leftJoin('penjualan_so_kontrak_log', 'penjualan_so_kontrak.id', '=', 'penjualan_so_kontrak_log.so_kontrak_id')
                ->select(
                    'penjualan_so_kontrak.id',
                    'penjualan_so_kontrak.code AS kontrak_code',
                    'master_products_packaging.code AS product_code',
                    'master_products_packaging.name AS product_name',
                    'penjualan_so_kontrak_item.qty AS product_qty',
                    'penjualan_so_kontrak_item.qty_sent AS product_qty_sent',
                    \DB::raw('SUM(penjualan_so_kontrak_log.qty_worked) AS total_qty_worked')
                )
                ->groupBy(
                    'penjualan_so_kontrak.id',
                    'penjualan_so_kontrak.code',
                    'master_products_packaging.code',
                    'master_products_packaging.name',
                    'penjualan_so_kontrak_item.qty',
                    'penjualan_so_kontrak_item.qty_sent'
                )
                // Filter out fulfilled items
                ->havingRaw('SUM(penjualan_so_kontrak_log.qty_worked) < penjualan_so_kontrak_item.qty')
                ->get();
    
            // Format the results for the response
            $results = $sales_kontrak->map(function ($row) {
                return [
                    'id' => $row->id,
                    'text' => "{$row->product_code} - {$row->product_name} / ({$row->kontrak_code})",
                    'product_qty' => $row->product_qty,
                    'total_qty_worked' => $row->total_qty_worked
                ];
            });
    
            return response()->json(['results' => $results], 200);
    
        } catch (\Exception $e) {
            // Catch unexpected errors and respond with a 500 error code
            return response()->json([
                'message' => 'An error occurred while fetching the data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function get_product_kontrak(Request $request)
    {
        if ($request->ajax()) {
            $data = [];

            $sales_kontrak_item = SalesOrderKontrakItem::where('penjualan_so_kontrak_item.so_kontrak_id', $request->so_kontrak)
                            ->leftJoin('master_products_packaging', 'penjualan_so_kontrak_item.product_packaging_id', '=', 'master_products_packaging.id')
                            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                            ->leftJoin('penjualan_so_kontrak', 'penjualan_so_kontrak_item.so_kontrak_id', '=', 'penjualan_so_kontrak.id')
                            ->select(
                                'master_products_packaging.name AS product_name', 
                                'master_products_packaging.code AS product_code', 
                                'penjualan_so_kontrak.id AS kontrak_id',
                                'penjualan_so_kontrak_item.price AS product_price', 
                                'penjualan_so_kontrak_item.disc_usd AS product_disc', 
                                'penjualan_so_kontrak_item.product_packaging_id AS product_id',
                                'master_packaging.id AS packaging_id',
                                'master_packaging.pack_name AS packaging_name',
                            )->get();
            
            foreach($sales_kontrak_item AS $row){
                $data[] = [
                    'product_id' => $row->product_id,
                    'product_code' => $row->product_code,
                    'product_name' => $row->product_name,
                    'product_price' => $row->product_price,
                    'product_disc' => $row->product_disc,
                    'packaging_id' => $row->packaging_id,
                    'packaging_name' => $row->packaging_name,
                    'kontrak_id' => $row->kontrak_id,
                ];
            }
        }

        return response()->json(['code' => 200, 'data' => $data]);
    }

    public function approvalMouSo(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();

            try{
                $errors = [];

                $sales_order = SalesOrder::find($id);

                if($sales_order == null){
                    abort(404);
                }

                $sales_order->approval_mou_status = 1;
                $sales_order->approval_mou_date = date('Y-m-d H:i:s');
                $sales_order->approval_mou_by = Auth::id();
                $sales_order->status = 2;

                if($sales_order->save()){
                    DB::commit();
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Approval MOU berhasil disimpan.',
                    ];
                    $response['redirect_to'] = url()->previous();
                    return response()->json($response, 200);
                }

            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $errors,
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function viewSalesOrderDetail($id)
    {
        // Ambil header satu kali
        $so_header = SalesOrder::join('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->select(
                'penjualan_so.so_date',
                'penjualan_so.so_code',
                'penjualan_so.idr_rate',
                'penjualan_so.catatan as disc_percent',
                'penjualan_so.note',
                'penjualan_so.type_transaction',
                'master_customer_other_addresses.name as customer_name',
                'master_customer_other_addresses.address as customer_address',
                'master_customer_other_addresses.text_kota as customer_city',
                'master_customer_other_addresses.text_provinsi as customer_province'
            )
            ->where('penjualan_so.id', $id)
            ->where('penjualan_so.approval_mou', 1)
            ->first();

        if (!$so_header) {
            return response()->json(['error' => 'SO tidak ditemukan'], 404);
        }

        // Ambil items
        $so_items = DB::table('penjualan_so_item')
            ->join('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->join('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->where('penjualan_so_item.so_id', $id)
            ->select(
                'master_products.code as product_code',
                'master_products.name as product_name',
                'master_packaging.pack_name as packaging_name',
                'penjualan_so_item.qty',
                'penjualan_so_item.price',
                'penjualan_so_item.free_product'
            )
            ->get();

        // Gabungkan
        $data = [
            'so_code' => $so_header->so_code,
            'so_date' => $so_header->so_date,
            'idr_rate' => $so_header->idr_rate,
            'disc_percent' => $so_header->disc_percent,
            'note' => $so_header->note,
            'type_transaction' => $so_header->type_transaction,
            'customer_name' => $so_header->customer_name,
            'customer_address' => $so_header->customer_address,
            'customer_city' => $so_header->customer_city,
            'customer_province' => $so_header->customer_province,
            'so_items' => $so_items
        ];

        return response()->json($data);
    }
}