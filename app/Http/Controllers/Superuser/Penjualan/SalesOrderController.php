<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Setting\UserMenu;
use App\DataTables\Penjualan\SalesOrderAwalTable;
use App\DataTables\Penjualan\SalesOrderLanjutanTable;
use App\Exports\Penjualan\SalesOrderAwalExport;
use App\Helper\CustomHelper;
use App\Helper\LogActivity;
use App\Services\SalesOrder\SalesOrderCalculationService;
use Illuminate\Support\Facades\Log;
use Validator;
use Auth;
use DB;
use Carbon;
use Excel;
use COM;
use Imagick;

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
        $customers = \App\Entities\Master\Customer::get();
        $brand = \App\Entities\Master\BrandLokal::get();

        $userDivision = Auth::user()->division;
        if(!in_array($userDivision, ['Admin', 'Developer', 'Management'])){
            $allowedBrands = ['GCF', 'Senses', 'PPI FF', 'PPI NON FF'];
            $brand = $brand->filter(function($b) use ($allowedBrands){
                return in_array($b->brand_name, $allowedBrands);
            });
        }

        $packing_order = \App\Entities\Penjualan\PackingOrder::get();
        $packaging = \App\Entities\Master\Packaging::where('status', \App\Entities\Master\Packaging::STATUS['ACTIVE'])->orderBy('pack_name')->get();
        
        $filtered_other_address = \App\Entities\Master\CustomerOtherAddress::get()->filter(function($address) {
            return $address->checkStore();
        });

        $data = [
            'customers' => $customers,
            'other_address' => $filtered_other_address,
            'packing_order' => $packing_order,
            'packaging' => $packaging,
            'brand' => $brand,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step] ?? '',
            'session' => $session,
        ];

        return view($this->view . "index_awal", $data);
    }

    private function getSoProgressQuery(Request $request)
    {
        $filter_periode = $request->filter_periode ?? 'harian';
        $query = \App\Entities\Penjualan\PackingOrder::query();

        if ($filter_periode == 'harian') {
            $query->whereDate('created_at', Carbon\Carbon::today());
        } elseif ($filter_periode == 'bulanan') {
            $query->whereMonth('created_at', Carbon\Carbon::now()->month)
                ->whereYear('created_at', Carbon\Carbon::now()->year);
        } elseif ($filter_periode == 'custom' && $request->tanggal_dari && $request->tanggal_sampai) {
            $query->whereBetween('created_at', [$request->tanggal_dari, $request->tanggal_sampai]);
        }

        return $query;
    }
    
    public function index_lanjutan(Request $request, $step = 2)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $packing_order = \App\Entities\Penjualan\PackingOrder::whereMonth('created_at', Carbon\Carbon::now()->month)
                            ->whereYear('created_at', Carbon\Carbon::now()->year)
                            ->get();

        $filter_periode = $request->filter_periode ?? 'harian';
        $so_progress = $this->getSoProgressQuery($request)->get();

        $data = [
            'packing_order' => $packing_order,
            'so_progress' => $so_progress,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step] ?? '',
            'filter_periode' => $filter_periode,
        ];

        return view($this->view . "index_lanjutan", $data);
    }

    public function so_progress_partial(Request $request)
    {
        $so_progress = $this->getSoProgressQuery($request)->get();
        return view('superuser.penjualan.sales_order.partials._so_progress_rows', compact('so_progress'))->render();
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

    public function create(Request $request, $step, $member, $brand, $type, $indent, $approval, $note, $kurs, $disc_percent, $disc_idr, $disc_usd, $disc_kemasan, $packaging = null)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $queryService = new \App\Services\SalesOrder\SalesOrderQueryService();
        $result = $queryService->getCreateFormData($step, $member, $brand, $type, $indent, $approval, $note, $kurs, $disc_percent, $disc_idr, $disc_usd, $disc_kemasan, $packaging);

        $data = $result['data'];

        return view($this->view."create",$data);
    }

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

            if ($validator->passes()) {
                try {
                    DB::beginTransaction();

                    $storeService = new \App\Services\SalesOrder\SalesOrderStoreService();
                    $result = $storeService->create($request, $member);

                    if (!$result['success']) {
                        DB::rollBack();
                        $response['notification'] = [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => $result['errors'],
                        ];
                        return $this->response(400, $response);
                    }

                    DB::commit();

                    // Sinkron ke AO (best-effort): input via web transaksi ikut masuk AO (pull fallback via /list)
                    try {
                        $created = $result['sales_order'];
                        if ($created) {
                            $created->refresh();
                            \App\Services\AoProgressPushService::push($created, 'CREATE', []);
                        }
                    } catch (\Exception $pushEx) {
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
                    $response['redirect_to'] = route('superuser.penjualan.sales_order.index_awal');
                    return $this->response(200, $response);

                } catch (\Exception $e) {
                    DB::rollBack();
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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $queryService = new \App\Services\SalesOrder\SalesOrderQueryService();
        $result = $queryService->getEditFormData($id, $step);

        if (!$result['success']) {
            abort(404);
        }

        $data = $result['data'];

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
                        $listItem = [];
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

    // public function lanjutkan(Request $request, $id)
    // {
    //     if ($request->ajax()) {
    //         if(Auth::user()->is_superuser == 0){
    //             if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
    //                 return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
    //             }
    //         }

    //         $sales_order = SalesOrder::find($id);

    //         if ($sales_order === null) {
    //             abort(404);
    //         }

    //         $sales_order->status = 2;

    //         if($sales_order->save()) {
    //             $user = User::find(33);
    //             $user->notify(new SoNotification($sales_order));

    //             $response['notification'] = [
    //                 'alert' => 'notify',
    //                 'type' => 'success',
    //                 'content' => 'Success',
    //             ];
    
    //             $response['redirect_to'] = route('superuser.penjualan.sales_order.index_awal');
    //             return $this->response(200, $response);
    //         }
            
    //     }
    // }

    public function lanjutkan(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')
                        ->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $sales_order = SalesOrder::find($id);
            if ($sales_order === null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                $workflowService = new \App\Services\SalesOrder\SalesOrderWorkflowService();
                $result = $workflowService->lanjutkan($sales_order);

                if ($result['type'] === 'lanjutan') {
                    $workflowService->sendLanjutkanNotification($result['sales_order']);
                }

                DB::commit();

                // Sync ke AO (best-effort): lanjutkan
                try {
                    $sales_order->refresh();
                    \App\Services\AoProgressPushService::push($sales_order, 'LANJUTAN');
                } catch (\Exception $pushEx) {
                }

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];
                $response['redirect_to'] = route('superuser.penjualan.sales_order.index_awal');
                return $this->response(200, $response);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e);
                return $this->response(500, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
                    ]
                ]);
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
                    $errors[] = 'Sales Order tidak ditemukan!';
                }

                $workflowService = new \App\Services\SalesOrder\SalesOrderWorkflowService();
                $workflowService->kembali($sales_order);

                if($errors) {
                    DB::rollBack();
                    $response['notification'] = [
                        'alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $errors,
                    ];
                    return $this->response(400, $response);
                }

                DB::commit();
                // Sync ke AO (best-effort): revisi -> AO reopen order
                try {
                    $sales_order->refresh();
                    \App\Services\AoProgressPushService::push($sales_order, 'REVISI', ['note' => 'Admin mengembalikan SO untuk revisi']);
                } catch (\Exception $pushEx) {
                }
                $response['notification'] = [
                    'alert' => 'notify', 'type' => 'success', 'content' => 'Success',
                ];
                $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                return $this->response(200, $response);

            }catch (\Exception $e) {
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $e->getMessage(),
                ];
                return $this->response(400, $response);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        if ($request->ajax()) {
            $sales_order = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                $destroyService = new \App\Services\SalesOrder\SalesOrderDestroyService();
                $result = $destroyService->destroy($sales_order);

                if (!$result['success']) {
                    DB::rollBack();
                    return redirect()->back()->with('error', $result['message']);
                }

                DB::commit();
                // Sync ke AO: SO dihapus -> AO tandai DIHAPUS (tidak ikut terhapus)
                try {
                    \App\Services\AoProgressPushService::push($sales_order, 'DELETE', ['note' => 'SO dihapus di transaksi (masa transisi SO Awal -> AO)']);
                } catch (\Exception $pushEx) {
                }
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
                return response()->json($data_json, 400);
            }

            DB::beginTransaction();
            try {
                $workflowService = new \App\Services\SalesOrder\SalesOrderWorkflowService();
                $workflowService->tidakLanjut($sales_order, $post["keterangan"]);
                    
                DB::commit();

                // Sync ke AO: tidak lanjut dianggap revisi + note agar AO tahu
                try {
                    $sales_order->refresh();
                    \App\Services\AoProgressPushService::push($sales_order, 'REVISI', ['note' => $post["keterangan"]]);
                } catch (\Exception $pushEx) {
                }

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Sales Order Berhasil Diubah";
                return response()->json($data_json, 200);
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
                $closingService = new \App\Services\SalesOrder\SalesOrderClosingService();
                
                $sales_order = SalesOrder::find($request->id);

                if($sales_order === null){
                    abort(404);
                }

                // Validasi dulu SEBELUM ada tulisan ke DB, supaya submit invalid
                // (misal grand total kosong karena kalkulasi JS belum jalan) tidak
                // menyisakan SO tertutup / DO dengan grand_total 0.
                $closingService->validateClosingRequest($request, $errors);
                if ($errors) {
                    DB::rollBack();
                    $response['notification'] = [
                        'alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $errors,
                    ];
                    return $this->response(400, $response);
                }

                $sales_order = $closingService->prepareClosing($sales_order, $request);
                $packing_order = $closingService->getOrCreatePackingOrder($sales_order, $request);

                $repeaterData = collect($request->repeater)->map(function($item) use ($packing_order) {
                    $item['do_id'] = $packing_order->id;
                    return $item;
                })->toArray();

                list($stockLogs, $mutasiItems) = $closingService->processStockReservation(
                    $request, $sales_order, $repeaterData, $errors
                );

                $packingOrderItems = $closingService->processItems(
                    $sales_order, $request->repeater, $packing_order->id, $errors
                );

                if (count($packingOrderItems) == 0) {
                    $errors[] = 'Not item sales order are ready';
                }

                // Ada error item (ID kosong, qty melebihi SO, dsb) -> batalkan SEMUA,
                // jangan sisakan DO setengah jadi yang bikin warning di logistik.
                if ($errors) {
                    DB::rollBack();
                    $response['notification'] = [
                        'alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $errors,
                    ];
                    return $this->response(400, $response);
                }

                $suffix = ($sales_order->count_rev == 0 && $request->has('keep_old_code')) ? 'Rev' : '';
                $closingService->createMutasiShowroom($sales_order, $request, $mutasiItems, $suffix);

                foreach ($packingOrderItems as $item) {
                    PackingOrderItem::create($item);
                }

                $closingService->upsertPackingOrderDetail($packing_order, $sales_order, $request);
                $closingService->insertStockLogs($stockLogs);

                DB::commit();

                // Sync ke AO (best-effort): tutup_so -> update invoice/synced_to (do_code + nota)
                try {
                    $sales_order->refresh();
                    $packing_order->refresh();
                    \App\Services\AoProgressPushService::push($sales_order, 'TUTUP', [
                        'do_code' => $packing_order->do_code ?? $packing_order->code ?? null,
                        'nota_code' => $sales_order->code,
                    ]);
                } catch (\Exception $pushEx) {
                }

                $response['notification'] = [
                    'alert' => 'notify', 'type' => 'success', 'content' => 'Success',
                ];
                $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                return $this->response(200, $response);

            } catch (\Exception $e) {
                DB::rollback();
                $errors[] = $e->getMessage();

                if ($request->ajax()) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $errors,
                    ];
                    return $this->response(400, $response);
                }

                return redirect()->back()->with('errors', $errors);
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
        $creport->DiscardSavedData();
        $creport->VerifyOnEveryPrint = false;

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

            $destroyService = new \App\Services\SalesOrder\SalesOrderDestroyService();
            $result = $destroyService->destroyLanjutan($sales_order);

            if (!$result['success']) {
                return $this->response(400, ['failed' => $result['message']]);
            }

            $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
            return $this->response(200, $response);
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

    // public function get_product_pack(Request $request)
    // {
    //     if ($request->ajax()) {
    //             $data = [];
                
    //             $product = Product::where('master_products.brand_name', $request->id)
    //                     // ->where('master_products.status', 1)
    //                     ->where('master_products.on_order', 1)
    //                     ->leftJoin('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
    //                     ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
    //                     ->leftJoin('master_product_types', 'master_products_packaging.type_id', '=', 'master_product_types.id')
    //                     ->leftJoin('master_warehouses', 'master_products_packaging.warehouse_id', '=', 'master_warehouses.id')
    //                     ->select('master_products_packaging.id as id' ,
    //                                 'master_products_packaging.code as ProductCode', 
    //                                 'master_products_packaging.name as productName', 
    //                                 'master_products_packaging.price as productPrice', 
    //                                 'master_packaging.id as  productPackagingID', 
    //                                 'master_packaging.pack_name as productPackaging', 
    //                                 'master_warehouses.name as warehouseName',
    //                                 'master_product_types.name as typeName',
    //                     )
    //                     ->get();

    //             foreach($product as $key){
    //                 $data[] = [
    //                     'id' => $key->id,
    //                     'code' => $key->ProductCode,
    //                     'name' => $key->productName,
    //                     'price' => $key->productPrice,
    //                     'packName' => $key->productPackaging,
    //                     'packID' => $key->productPackagingID,
    //                     'warehouseName' => $key->warehouseName,
    //                     'typeName' => $key->typeName,
    //                 ];
    //             }

    //             return response()->json(['code' => 200, 'data' => $data]);
    //     }
    // }

    public function get_product_pack(Request $request)
    {
        if (!$request->ajax()) {
            abort(403, 'Unauthorized');
        }

        try {
            $queryService = new \App\Services\SalesOrder\SalesOrderQueryService();
            $result = $queryService->getProductPack($request);

            if (!$result['success']) {
                return response()->json(['code' => $result['code'], 'message' => $result['message']]);
            }

            return response()->json([
                'code' => $result['code'],
                'data' => $result['data'],
                'count' => $result['count'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error' => $e->getMessage(),
            ]);
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
        $creport->DiscardSavedData();
        $creport->VerifyOnEveryPrint = false;

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
        $queryService = new \App\Services\SalesOrder\SalesOrderQueryService();
        $result = $queryService->viewSalesOrderDetail($id);

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 404);
        }

        return response()->json($result['data']);
    }

    public function sales_estimate_pdf($id)
    {
        $sales_order = SalesOrder::find($id);
        
        if (!$sales_order) abort(404, 'Data SO tidak ditemukan');

        $kalkulasiService = new SalesOrderCalculationService();
        $data_kalkulasi = $kalkulasiService->calculateEstimate($sales_order);

        $terbilang = trim(CustomHelper::terbilang($data_kalkulasi['grand_total'])); 

        $pdf = \PDF::loadView('superuser.penjualan.sales_order.pdf_sales_estimate', [
            'so'             => $sales_order,
            'data_kalkulasi' => $data_kalkulasi,
            'terbilang'      => $terbilang,
            'idr_rate'       => $data_kalkulasi['idr_rate']
        ])->setPaper('A5', 'landscape');

        return $pdf->stream('Sales_Estimate_' . $sales_order->so_code . '.pdf');
    }

    /**
     * Arsipkan satu SO Awal secara manual (tombol per-baris di index_awal).
     * Dipanggil via AJAX oleh saveConfirmation(), jadi response wajib JSON
     * dengan redirect_to (lihat Responder + common.js), bukan redirect().
     * Kriteria disamakan dengan cron so:archive-old-awal: hanya status AWAL.
     */
    public function archive_one_awal($id)
    {
        $userDivision = Auth::user()->division;
        if (!in_array($userDivision, ['Admin', 'Developer', 'Management'])) {
            return $this->response(400, ['message' => 'Anda tidak punya akses untuk mengarsipkan SO.']);
        }

        $sales_order = SalesOrder::find($id);
        if (!$sales_order) {
            return $this->response(404, ['message' => 'Sales Order tidak ditemukan.']);
        }

        if ($sales_order->is_archived == 1) {
            return $this->response(400, ['message' => 'SO sudah diarsipkan sebelumnya.']);
        }

        if ($sales_order->status != 1) {
            return $this->response(400, ['message' => 'Hanya SO berstatus AWAL yang bisa diarsipkan manual.']);
        }

        $sales_order->update([
            'is_archived' => 1,
            'archived_at' => now(),
        ]);

        $response['notification'] = [
            'alert' => 'notify',
            'type' => 'success',
            'content' => 'SO Awal ' . $sales_order->so_code . ' berhasil diarsipkan.',
        ];
        $response['redirect_to'] = 'reload()';
        return $this->response(200, $response);
    }

    public function archive_awal()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $query = SalesOrder::where('so_indent', SalesOrder::INDENT['NO'])
            ->where('is_archived', 1)
            ->whereIn('type_transaction', ['CASH', 'TEMPO']);

        $archives = $query->orderBy('archived_at', 'desc')->get();

        return view('superuser.penjualan.sales_order.archive_awal', compact('archives'));
    }

    public function archive_awal_restore($id)
    {
        $sales_order = SalesOrder::findOrFail($id);

        $sales_order->update([
            'is_archived' => 0,
            'archived_at' => null,
        ]);

        return redirect()->route('superuser.penjualan.sales_order.archive_awal')
            ->with('success', 'SO Awal berhasil dikembalikan.');
    }

    public function archive_awal_print_estimate($id)
    {
        $so = SalesOrder::with(['so_detail.product_pack', 'member'])
            ->findOrFail($id);

        $kalkulasiService = new SalesOrderCalculationService();
        $data_kalkulasi = $kalkulasiService->calculateEstimate($so);

        $terbilang = trim(CustomHelper::terbilang($data_kalkulasi['grand_total']));

        $pdf = \PDF::loadView('superuser.penjualan.sales_order.pdf_sales_estimate', [
            'so'             => $so,
            'data_kalkulasi' => $data_kalkulasi,
            'terbilang'      => $terbilang,
            'idr_rate'       => $data_kalkulasi['idr_rate']
        ])->setPaper('A5', 'landscape');

        return $pdf->stream('Sales_Estimate_Archive_' . $so->so_code . '.pdf');
    }

    /**
     * Membersihkan format angka dari input (titik ribuan -> hilang, koma desimal -> titik)
     * agar aman disimpan ke kolom decimal.
     */
    private function cleanCurrency($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        $value = str_replace('.', '', $value);   // buang titik ribuan
        $value = str_replace(',', '.', $value);  // koma jadi titik desimal
        return $value;
    }
}