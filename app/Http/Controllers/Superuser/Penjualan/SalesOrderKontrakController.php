<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SalesorderKontrak;
use App\Entities\Penjualan\SalesOrderKontrakItem;
use App\Entities\Penjualan\SalesOrderKontrakLog;
use App\Entities\Penjualan\SalesOrderKontrakPivot;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Master\CustomerOtherAddress;
use App\DataTables\Penjualan\SalesOrderKontrakTable;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Packaging;
use App\Entities\Master\Product;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Vendor;
use App\Repositories\CodeRepo;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Str;
use App\Helper\LogActivity;
use Carbon\Carbon;
use Validator;
use Auth;
use COM;
use DB;


class SalesOrderKontrakController extends Controller
{
    public function __construct(){
        $this->view = "superuser.penjualan.sales_order_kontrak.";
        $this->route = "superuser.penjualan.sales_order_kontrak";
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

    public function json(Request $request, SalesOrderKontrakTable $datatable)
    {
        return $datatable->build();
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

    public function get_product(Request $request)
    {
        $brand_name = $request->brand_name;

        $product = Product::where('brand_name', $brand_name)
                        ->where('status', 1)
                        ->select(
                            'id as id' ,
                            'code as ProductCode', 
                            'name as productName', 
                        )
                        ->get();
        
        foreach($product as $item){
            echo "<option value='$item->id'>$item->ProductCode - $item->productName</option>";
        }
    }

    public function get_packaging(Request $request)
    {
        $product_id = $request->product_id;

            $products = ProductPack::where('master_products_packaging.product_id', $product_id)
                ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                ->select(
                    'master_packaging.id AS packID', 
                    'master_packaging.pack_name as packName'
                )
                ->get();

        foreach ($products as $product){
            echo "<option value='$product->packID'>$product->packName</option>";
        }
    }

    public function get_packaging_edit(Request $request)
    {
        $product_id = $request->product_id;

            $products = ProductPack::where('master_products_packaging.id', $product_id)
                ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                ->select(
                    'master_packaging.id AS packID', 
                    'master_packaging.pack_name as packName'
                )
                ->get();

        foreach ($products as $product){
            echo "<option value='$product->packID'>$product->packName</option>";
        }
    }

    public function index(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrderKontrak::get();

        $data = [
            'result' => $result,
        ];

        return view($this->view."index", $data);
    }

    public function create(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $customer = CustomerOtherAddress::get();
        $product = Product::get();

        $data = [
            'customer' => $customer,
            'product' => $product,
        ];

        return view($this->view."create", $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $failed = "";

            DB::beginTransaction();

            try{

                $validator = Validator::make($request->all(), [
                    'durasi_kontrak' => 'required',
                    'customer_other_address_id' => 'required',
                    'sales_senior' => 'required',
                    'sales_junior' => 'required',
                    'brand_name' => 'required',
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
                    // generate id 
                    $collec_data = array(
                        'customer' => $request->customer_other_address_id,
                        'item' => $request->product_name,
                        'pack' => $request->packaging_id,
                        'qty' => $request->qty,
                        'range' => $request->durasi_kontrak,
                        'code_str' => CodeRepo::generateSoKontrak(),
                    );

                    $generate_code = implode("_", $collec_data);
                    // check id input
                    if (SalesorderKontrak::where('id', $generate_code)->exists()) {
                        $response['notification'] = [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => 'Kontrak sudah ada!',
                        ];
        
                        return $this->response(400, $response);
                    }else{
                        $sales_kontrak = new SalesorderKontrak;
                        $sales_kontrak->id = $generate_code;
                        $sales_kontrak->brand_name = $request->brand_name;
                        $sales_kontrak->code = CodeRepo::generateSokontrakCode();
                        $sales_kontrak->contract_range = $request->durasi_kontrak;
                        $sales_kontrak->customer_other_address_id = $request->customer_other_address_id;
                        $sales_kontrak->sales_senior = $request->sales_senior;
                        $sales_kontrak->sales_junior = $request->sales_junior;
                        $sales_kontrak->note = $request->note;
                        $sales_kontrak->created_by = Auth::id();
                        $sales_kontrak->status = SalesOrderKontrak::STATUS['ACTIVE'];
                        if($sales_kontrak->save()){
                            $sales_order_detail = new SalesOrderKontrakItem;
                            $sales_order_detail->so_kontrak_id = $sales_kontrak->id;
                            $sales_order_detail->product_packaging_id = $request->product_name.'-'.$request->packaging_id;
                            $sales_order_detail->packaging_id = $request->packaging_id;
                            $sales_order_detail->price = $request->price;
                            $sales_order_detail->qty = $request->qty;
                            $sales_order_detail->disc_usd = $request->disc_usd;
                            $sales_order_detail->save();

                            DB::commit();
                            LogActivity::addToLog('Created a new SO-Kontrak: ' . $sales_kontrak->code);
                            $response['notification'] = [
                                'alert' => 'notify',
                                'type' => 'success',
                                'content' => 'Success',
                            ];
        
                            $response['redirect_to'] = route('superuser.penjualan.sales_order_kontrak.index');
                            return $this->response(200, $response);
                        }
                    }

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

    public function edit(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $decode = base64_decode($id);

        // Retrieve data or return a 404 error if not found
        $data['kontrak'] = SalesOrderKontrak::findOrFail($decode);
        $data['customer'] = CustomerOtherAddress::all();
        $data['product'] = ProductPack::all()->unique('id');
        $data['packaging'] = Packaging::all();

        return view($this->view . "edit", $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            // Find the SalesOrderKontrak or return 404 if not found
            $sales_kontrak = SalesOrderKontrak::find($id);
            if (!$sales_kontrak) {
                abort(404);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:penjualan_so_kontrak,code,' . $sales_kontrak->id,
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
                // Splitting the ID and product for future use
                $pecah_id = explode("_", $sales_kontrak->id);
                $pecah_product = explode("-", $request->product_name);

                // Preparing data for updating the kontrak
                $collec_data = [
                    'customer' => $request->customer_other_address_id,
                    'item' => $request->product_name . '-' . $request->packaging_id,
                    'qty' => $request->qty,
                    'range' => $request->durasi_kontrak,
                    'year' => date('Y'),
                    'code_str' => $pecah_id[5] ?? '', // Handle case where $pecah_id has less than 6 elements
                ];

                // Generate the new kontrak ID code
                $generate_code = implode("_", $collec_data);

                // Update SalesOrderKontrak
                $sales_kontrak->id = $generate_code;
                $sales_kontrak->code = $request->code;
                $sales_kontrak->contract_range = $request->durasi_kontrak;

                if ($sales_kontrak->save()) {
                    // Delete previous kontrak items and insert new ones
                    SalesOrderKontrakItem::where('so_kontrak_id', $sales_kontrak->id)->delete();

                    // Create new kontrak item
                    $sales_kontrak_item = new SalesOrderKontrakItem;
                    $sales_kontrak_item->so_kontrak_id = $sales_kontrak->id;
                    $sales_kontrak_item->product_packaging_id = $pecah_product[0] . '-' . $request->packaging_id;
                    $sales_kontrak_item->packaging_id = $request->packaging_id;
                    $sales_kontrak_item->price = $request->price;
                    $sales_kontrak_item->qty = $request->qty;
                    $sales_kontrak_item->disc_usd = $request->disc_usd;
                    $sales_kontrak_item->save();

                    // Response for successful update
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    // Log the update action
                    LogActivity::addToLog('Updated SO-Kontrak: ' . $sales_kontrak->code);

                    // Redirect to the index page
                    $response['redirect_to'] = route('superuser.penjualan.sales_order_kontrak.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    // public function acc(Request $request, $id)
    // {
    //     if ($request->ajax()) {
    //         DB::beginTransaction();
    //         try{

    //             if(Auth::user()->is_superuser == 0){
    //                 if(empty($this->access) || empty($this->access->user) || $this->access->can_aprove == 0){
    //                     return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
    //                 }
    //             }

    //             // dd($id);
    
    //             $decode = base64_decode($id);
    
    //             $kontrak = SalesOrderKontrak::findOrFail($decode);
    
    //             if($kontrak == null){
    //                 abort(404);
    //             }
    
    //             $kontrak->status = SalesOrderKontrak::STATUS['ACC'];
    //             $kontrak->acc_by = Auth::id();
    //             if ($kontrak->save()) {
    //                 DB::commit();

    //                 $response['redirect_to'] = route('superuser.penjualan.sales_order_kontrak.index');
    //                 return $this->response(200, $response);
    //             }

    //         }catch (\Exception $e) {
    //             DB::rollback();
    //             DD($e);
    //             $response['notification'] = [
    //                 'alert' => 'block',
    //                 'type' => 'alert-danger',
    //                 'header' => 'Error',
    //                 'content' => "Internal Server Error",
    //             ];

    //             return $this->response(400, $response);
    //         }
    //     }
    // }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }
            
            $decode = base64_decode($id);

            $kontrak = SalesOrderKontrak::find($decode);

            if ($kontrak === null) {
                abort(404);
            }

            DB::beginTransaction();
            try{

                $kontrak->status = SalesOrderKontrak::STATUS['ACC'];
                $kontrak->acc_by = Auth::id();

                if ($kontrak->save()) {

                    
                    DB::commit();
                    LogActivity::addToLog('ACC SO-Kontrak: ' . $kontrak->code);
                    $response['redirect_to'] = route('superuser.penjualan.sales_order_kontrak.index');
                    return $this->response(200, $response);
                }
            }catch (\Exception $e) {
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $failed,
                ];
    
                return $this->response(400, $response);
            }
        }
    }

    public function complete(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $decode = base64_decode($id);

            $kontrak = SalesOrderKontrak::where('id', $decode)->first();
            $kontrak_item = SalesOrderKontrakItem::where('so_kontrak_id', $kontrak->id)->first();
            // DD($kontrak_item);

            if($kontrak == null){
                abort(404);
            }

            $condition = SalesOrderKontrak::CONDITION['INVALID'];
            $complete_code = $kontrak->id.'.'.$condition;

            $kontrak->id = $complete_code;
            $kontrak->status = SalesOrderKontrak::STATUS['COMPLETE'];
            $kontrak->updated_by = Auth::id();
            if ($kontrak->save()) {
                $kontrak_item->so_kontrak_id = $complete_code;
                $kontrak_item->save();
                // DD($kontrak_item->so_kontrak_id);
                
                LogActivity::addToLog('Completed SO-Kontrak: ' . $kontrak->code);
                $response['redirect_to'] = route('superuser.penjualan.sales_order_kontrak.index');
                return $this->response(200, $response);
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

        $decode = base64_decode($id);
        $kontrak = SalesOrderKontrak::where('id', $decode)->first();
        $kontrak_item = SalesOrderKontrakItem::where('so_kontrak_id', $kontrak->id)->first();
        $log_kontrak = SalesOrderKontrakLog::where('penjualan_so_kontrak_log.so_kontrak_id', $decode)
            ->join('penjualan_so_kontrak', 'penjualan_so_kontrak_log.so_kontrak_id', '=', 'penjualan_so_kontrak.id')
            ->join('penjualan_so', 'penjualan_so_kontrak_log.so_id', '=', 'penjualan_so.id')
            ->join('penjualan_do', 'penjualan_so.id', '=', 'penjualan_do.so_id')
            ->join('penjualan_so_kontrak_item', function($join) {
                $join->on('penjualan_so_kontrak.id', '=', 'penjualan_so_kontrak_item.so_kontrak_id');
            })
            ->where('penjualan_do.status', 6)
            ->select(
                'penjualan_do.do_code AS invoice_code',
                'penjualan_so.so_code AS so_code',
                'penjualan_so_kontrak_log.qty_worked AS qty_sent',
                'penjualan_so_kontrak_item.qty AS qty_ordered'
            )
            ->get();
        
        // dd($log_kontrak);

        $data = [
            'kontrak' => $kontrak,
            'kontrak_item' => $kontrak_item,
            'log_kontrak' => $log_kontrak,
        ];

        return view($this->view."show", $data);
    }

    public function revisi(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $decode = base64_decode($id);
        $data['kontrak'] = SalesOrderKontrak::where('id', $decode)->first();
        $data['customer'] = CustomerOtherAddress::get();
        $data['product'] = ProductPack::get();
        $data['packaging'] = Packaging::get();

        return view($this->view."revisi", $data);
    }

    public function update_revisi(Request $request, $id)
    {
        if ($request->ajax()) {
            

            DB::beginTransaction();

            try{
                $errors = [];

                $sales_kontrak = SalesOrderKontrak::find($id);
                $log_kontrak = SalesOrderKontrakLog::where('so_kontrak_id', $sales_kontrak->id)->first();
                $total_log = SalesOrderKontrakLog::where('so_kontrak_id', $sales_kontrak->id)->sum(DB::raw('COALESCE(qty_worked, 0)'));

                $currentDateNow = Carbon::now()->format('Y-m-d');
                $cal_range = $sales_kontrak->contract_range * Carbon::now()->daysInMonth;
                $start_date = Carbon::parse($sales_kontrak->created_at);
                $end_date = $start_date->copy()->addDays($cal_range);

                if (!$log_kontrak) {
                    $errors[] = 'Pengambilan Qty Kontrak belum ada!';
                }

                if ($sales_kontrak) {
                    if ($sales_kontrak->item->qty != ($total_log ?? 0)) {
                        $errors[] = 'Qty kontrak masih ada!';
                    }
                }

                // if($sales_kontrak){
                //     $qtyWorkedSum = $log_kontrak->sum(function($log) {
                //         return $log->qty_worked ?? 0; // If qty_worked is null, use 0
                //     });
                
                //     if($sales_kontrak->item->qty != $qtyWorkedSum){
                //         $errors[] = 'Qty kontrak masih ada!';
                //     }
                // }

                if($currentDateNow >= $end_date->format('Y-m-d')){
                    $errors[] = 'Melebihi tanggal kontrak!';
                }

                // update ID
                $explodeID = explode("_", $sales_kontrak->id);
                $updateID = array(
                    'customer' => $explodeID[0],
                    'product' => $explodeID[1],
                    'packaging' => $explodeID[2],
                    'qtyKontrak' => $explodeID[3] + $request->qty_plus,
                    'range_contract' => $explodeID[4] + $request->durasi_kontrak,
                    'str' => $explodeID[5]. "." . SalesOrderKontrak::CONDITION['VALID'],
                );

                $generate_code = implode("_", $updateID);

                $sales_kontrak->id = $generate_code;
                $sales_kontrak->contract_range = $request->durasi_kontrak;
                $sales_kontrak->updated_by = Auth::id();
                if($sales_kontrak->save()){
                    $kontrak_item = SalesOrderKontrakItem::where('id', $request->kontrak_item)->first();
                    
                    $cal_new_qty = floatval($kontrak_item->qty + $request->qty_plus);
                    $update_item = SalesOrderKontrakItem::where('id', $kontrak_item->id)->update(['qty' => $cal_new_qty]);
                }


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
                    LogActivity::addToLog('Revisi SO-Kontrak: ' . $sales_kontrak->code);

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
        
                    $response['redirect_to'] = route('superuser.penjualan.sales_order_kontrak.index');
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

    public function cancel_acc(Requeest $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $decode = base64_decode($id);
            $kontrak = SalesOrderKontrak::where('id', $decode)->first();
            $kontrak_item = SalesOrderKontrakItem::where('so_kontrak_id', $kontrak->id)->first();

            if($kontrak == null){
                abort(404);
            }

            
        }
    }

    public function cancel_aprove(Request $request, $id)
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

                $decode = base64_decode($id);
                $kontrak = SalesOrderKontrak::where('id', $decode)->first();
                $log_kontrak = SalesOrderKontrakLog::where('so_kontrak_id', $kontrak)->first();
    
                if($kontrak == null){
                    abort(404);
                }

                // check log kontak
                if (isset($log_kontrak)) {
                    $errors[] = 'Kontrak sudah ada pengambilan!';
                } else {
                    $kontrak->status = SalesOrderKontrak::STATUS['ACTIVE'];
                    $kontrak->updated_by = Auth::id();
                    $kontrak->acc_by = null;
                    $kontrak->save();
                }

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
                    LogActivity::addToLog('Cancel Acc SO-Kontrak: ' . $kontrak->code);
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
        
                    $response['redirect_to'] = route('superuser.penjualan.sales_order_kontrak.index');
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

    public function destroy(Request $request , $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $decode = base64_decode($id);
            $kontrak = SalesOrderKontrak::where('id', $decode)->first();
            $kontrak_item = SalesOrderKontrakItem::where('so_kontrak_id', $kontrak->id)->first();


            if($kontrak == null){
                abort(404);
            }

            $date = Carbon::now();
            $condition = SalesOrderKontrak::CONDITION['INVALID'];
            $complete_code = $kontrak->id.'.'.$condition;

            $kontrak->id = $complete_code;
            $kontrak->status = SalesOrderKontrak::STATUS['DELETED'];
            $kontrak->updated_by = Auth::id();
            $kontrak->deleted_by = Auth::id();
            if ($kontrak->save()) {
                LogActivity::addToLog('Deleted SO-Kontrak: ' . $kontrak->code);
                // $kontrak_item->delete();
                $response['redirect_to'] = route('superuser.penjualan.sales_order_kontrak.index');
                return $this->response(200, $response);
            }
        }
    }

    public function print_log()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\report\\operasional\\log_kontrak\\log_kontrak_rev_2.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\log_kontrak\\export\\Report-Log_Kontrak.pdf';

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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\report\\operasional\\log_kontrak\\export\\Report-Log_Kontrak.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function update_pivot()
    {
        // Query to search SO data
        $searchSo = SalesOrder::leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
            ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->where('penjualan_so_item.kontrak', 1)
            ->where('penjualan_so.status', 4)
            ->select(
                'penjualan_so.id AS so_id',
                'penjualan_so.code AS so_code',
                'penjualan_so_item.kontrak_id AS kontrak_id', 
                'master_products_packaging.id AS product_id', 
                'master_customer_other_addresses.id AS customer_id'
            )
            ->get();

        // Query to search Kontrak data
        $searchKontrak = SalesOrderKontrak::leftJoin('penjualan_so_kontrak_item', 'penjualan_so_kontrak.id', '=', 'penjualan_so_kontrak_item.so_kontrak_id')
            ->leftJoin('master_customer_other_addresses', 'penjualan_so_kontrak.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->where('penjualan_so_kontrak.status', SalesOrderKontrak::STATUS['ACC'])
            ->select(
                'penjualan_so_kontrak.id AS kontrak_id',
                'penjualan_so_kontrak.code AS code', 
                'penjualan_so_kontrak_item.product_packaging_id AS IDProduct',
                'master_customer_other_addresses.id AS IDCustomer'
            )
            ->get();

        $soData = [];
        foreach ($searchSo as $so) {
            $soData[$so->customer_id][$so->product_id] = $so;

            foreach ($searchKontrak as $kontrak) {
                $customerId = $kontrak->IDCustomer;
                $productId = $kontrak->IDProduct;

                // Check if matching SO data exists
                if (isset($soData[$customerId][$productId])) {
                    $so = $soData[$customerId][$productId];

                    // If kontrak_id is null, update it
                    if (is_null($so->kontrak_id)) {
                        try {
                            SalesOrderItem::where('so_id', $so->so_id)
                                ->where('product_packaging_id', $productId)
                                ->update([
                                    'kontrak_id' => $kontrak->kontrak_id
                                ]);

                            session()->flash('success', 'Updated successfully!');
                        } catch (\Exception $e) {
                            session()->flash('error', 'Failed to update: ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        return redirect()->back(); // Redirect after the update
    }

    public function update_log()
    {
        // Initialize an empty array to store the logs for bulk insert
        $logs = [];

        // Process the sales orders in chunks to avoid memory overload
        SalesOrder::leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
            ->where('penjualan_so_item.kontrak', 1)
            ->where('penjualan_so.status', 4)
            ->select(
                'penjualan_so.id AS soID',
                'penjualan_so.code AS so_code',
                'penjualan_so_item.kontrak AS status_kontrak',
                'penjualan_so_item.kontrak_id AS kontrakID',
                'penjualan_so.customer_other_address_id AS customerID',
                'penjualan_so_item.qty_worked AS qty_worked'
            )
            ->chunk(100, function ($soChunk) use (&$logs) {
                foreach ($soChunk as $item) {
                    try {
                        // Fetch the corresponding SalesOrderKontrakItem record
                        $so_kontrak_item = SalesOrderKontrakItem::where('so_kontrak_id', $item->kontrakID)->first();

                        // Check if SalesOrderKontrakItem exists and qty_worked is numeric
                        if ($so_kontrak_item && is_numeric($item->qty_worked)) {
                            // Calculate the remaining quantity (outstanding_qty)
                            $peng_kontrak_qty = $so_kontrak_item->qty - $item->qty_worked;

                            // Prepare log entry for bulk insert
                            $logs[] = [
                                'code' => $item->so_code,
                                'customer_other_address_id' => $item->customerID,
                                'so_kontrak_id' => $item->kontrakID,
                                'so_id' => $item->soID,
                                'qty_worked' => $item->qty_worked,
                                'outstanding_qty' => $peng_kontrak_qty,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    } catch (\Exception $e) {
                        // Log the exception
                        session()->flash('error', 'Failed to update: ' . $e->getMessage());
                        continue; // Continue with the next item
                    }
                }

                // Insert logs in bulk to optimize performance
                if (!empty($logs)) {
                    DB::transaction(function () use (&$logs) {
                        SalesOrderKontrakLog::insert($logs);
                        $logs = []; // Clear the logs array after insertion to avoid duplicate entries
                        session()->flash('success', 'Updated successfully!');
                    });
                }
            });
        return redirect()->back(); // Redirect after the update
    }
}
