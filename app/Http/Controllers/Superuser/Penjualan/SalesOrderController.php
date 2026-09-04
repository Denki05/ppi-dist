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
use App\Services\SalesOrderCalculationService;
use Illuminate\Support\Facades\Log;
use Validator;
use Auth;
use DB;
use Carbon;
use Excel;

class SalesOrderController extends Controller
{
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
    }

    public function detail($id)
    {
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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $queryService = new \App\Services\SalesOrderQueryService();
        $result = $queryService->getDataSo($id);

        return response()->json($result['data']);
    }

    public function create(Request $request, $step, $member, $brand, $type, $indent, $approval, $note, $kurs, $disc_percent, $disc_idr, $disc_usd, $disc_kemasan, $packaging = null)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $queryService = new \App\Services\SalesOrderQueryService();
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

                    $storeService = new \App\Services\SalesOrderStoreService();
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

    public function show($id)
    {
        //
    }

    public function edit($id, $step)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $queryService = new \App\Services\SalesOrderQueryService();
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
        $result = \App\Entities\Penjualan\SalesOrderItem::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $product_category = \App\Entities\Master\ProductCategory::all();
        $product_type = \App\Entities\Master\ProductType::all();
        $data = [
            'product_category' => $product_category,
            'product_type' => $product_type,
            'result' => $result,
        ];
        return view($this->view."edit_item",$data);
    }

    public function update(Request $request)
    {
        $data_json = [];
        if($request->method() == "POST"){
            DB::beginTransaction();
            try {
                $updateService = new \App\Services\SalesOrderUpdateService();
                $result = $updateService->update($request);

                if (!$result['success']) {
                    DB::rollback();
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = $result['message'];
                    return response()->json($data_json, 400);
                }

                DB::commit();
                $data_json["IsError"] = FALSE;
                $data_json["Message"] = $result['message'];
                return response()->json($data_json, 200);
            } catch (\Exception $e) {
                DB::rollback();
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = "Sales Order Gagal Diubah: " . $e->getMessage();
                return response()->json($data_json, 400);
            }
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            return response()->json($data_json, 400);
        }
    }

    public function update_item(Request $request)
    {
        $data_json = [];
        if($request->method() == "POST"){
            $updateService = new \App\Services\SalesOrderUpdateService();
            $result = $updateService->updateItem($request);

            $data_json["IsError"] = !$result['success'];
            $data_json["Message"] = $result['message'];
            return response()->json($data_json, 200);
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            return response()->json($data_json, 400);
        }
    }

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
                $workflowService = new \App\Services\SalesOrderWorkflowService();
                $result = $workflowService->lanjutkan($sales_order);

                if ($result['type'] === 'lanjutan') {
                    $workflowService->sendLanjutkanNotification($result['sales_order']);
                }

                DB::commit();

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

                $workflowService = new \App\Services\SalesOrderWorkflowService();
                $workflowService->kembali($sales_order);

                if($errors) {
                    DB::rollBack();
                    $response['notification'] = [
                        'alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $errors,
                    ];
                    return $this->response(400, $response);
                }

                DB::commit();
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
                $destroyService = new \App\Services\SalesOrderDestroyService();
                $result = $destroyService->destroy($sales_order);

                if (!$result['success']) {
                    DB::rollBack();
                    return redirect()->back()->with('error', $result['message']);
                }

                DB::commit();
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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();
        try{
            $destroyService = new \App\Services\SalesOrderDestroyService();
            $result = $destroyService->destroyItem($request);

            if (!$result['success']) {
                DB::rollBack();
                return redirect()->back()->with('error', $result['message']);
            }

            DB::commit();
            return redirect()->back()->with('success', $result['message']);
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
                $workflowService = new \App\Services\SalesOrderWorkflowService();
                $workflowService->tidakLanjut($sales_order, $post["keterangan"]);
                    
                DB::commit();

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
                $closingService = new \App\Services\SalesOrderClosingService();
                
                $sales_order = SalesOrder::find($request->id);

                if($sales_order === null){
                    abort(404);
                }

                $closingService->validateClosingRequest($request, $errors);
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

                $suffix = ($sales_order->count_rev == 0 && $request->has('keep_old_code')) ? 'Rev' : '';
                $closingService->createMutasiShowroom($sales_order, $request, $mutasiItems, $suffix);

                if (count($packingOrderItems) == 0) {
                    DB::rollback();
                    $errors[] = 'Not item sales order are ready';
                }

                foreach ($packingOrderItems as $item) {
                    PackingOrderItem::create($item);
                }

                $closingService->upsertPackingOrderDetail($packing_order, $sales_order, $request);
                $closingService->insertStockLogs($stockLogs);

                DB::commit();

                if($errors) {
                    $response['notification'] = [
                        'alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $errors,
                    ];
                    return $this->response(400, $response);
                } else {
                    $response['notification'] = [
                        'alert' => 'notify', 'type' => 'success', 'content' => 'Success',
                    ];
                    $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
                    return $this->response(200, $response);
                }

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
            $queryService = new \App\Services\SalesOrderQueryService();
            $result = $queryService->getCustomerDetail($post["id"]);

            $data_json["IsError"] = !$result['success'];
            $data_json["Data"] = $result['data'] ?? null;
            $data_json["Message"] = $result['message'] ?? '';
            return response()->json($data_json, 200);
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            return response()->json($data_json, 400);
        }
    }

    public function ajax_warehouse_detail(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            $queryService = new \App\Services\SalesOrderQueryService();
            $result = $queryService->getWarehouseDetail($post["id"]);

            $data_json["IsError"] = !$result['success'];
            $data_json["Data"] = $result['data'] ?? null;
            $data_json["Message"] = $result['message'] ?? '';
            return response()->json($data_json, 200);
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            return response()->json($data_json, 400);
        }
    }

    public function print_rejected_so($id){
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = SalesOrder::where('id',$id)->first();
        $company = \App\Entities\Master\Company::first();
        if(empty($result)){
            abort(404);
        }

        $data = [
            'result' => $result,
            'company' => $company
        ];

        $pdf = \PDF::loadview($this->view."print_rejected_so",$data)->setPaper('a5','potrait');
        return $pdf->stream($result->code ?? '');
    }

    public function print_proforma($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $crService = new \App\Services\CrystalReportService();
        $result = $crService->printProforma($id);

        if (!$result['success']) {
            abort(500, $result['message']);
        }

        $file = $result['file'];
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
        ob_clean();
        flush();
        readfile($file);
        exit();
    }

    public function get_product(Request $request){
        $data_json = [];
        if($request->method() == "GET"){
            $queryService = new \App\Services\SalesOrderQueryService();
            $result = $queryService->getProductsByBrand($request->brand_name);

            $data_json["IsError"] = FALSE;
            $data_json["Data"] = $result['data'];
            return response()->json($data_json, 200);
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            return response()->json($data_json, 400);
        }
    }

    public function get_packaging(Request $request){
        $data_json = [];
        if($request->method() == "GET"){
            $queryService = new \App\Services\SalesOrderQueryService();
            $result = $queryService->getPackagingByProduct($request->product_id);

            $data_json["IsError"] = FALSE;
            $data_json["Data"] = $result['data'];
            return response()->json($data_json, 200);
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            return response()->json($data_json, 400);
        }
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

            $destroyService = new \App\Services\SalesOrderDestroyService();
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

                $workflowService = new \App\Services\SalesOrderWorkflowService();
                $workflowService->indent($result);

                DB::commit();
                $response['redirect_to'] = route('superuser.penjualan.sales_order_indent.index');
                return $this->response(200, $response);

            }catch (\Exception $e) {
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => "Internal Server Error",
                ];
                return $this->response(400, $response);
            }
        }
    }

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
                $sales_order = SalesOrder::find($id);

                $workflowService = new \App\Services\SalesOrderWorkflowService();
                $result = $workflowService->kembaliHold($sales_order, $request->catatan_kembali);

                if(!$result['success']){
                    DB::rollBack();
                    $response['notification'] = [
                        'alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $result['errors'],
                    ];
                    return $this->response(400, $response);
                }

                DB::commit();
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

    public function get_brand(Request $request)
    {
        $brands = \App\Entities\Master\BrandLokal::where('status', \App\Entities\Master\BrandLokal::STATUS['ACTIVE'])
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
        if (!$request->ajax()) {
            abort(403, 'Unauthorized');
        }

        try {
            $queryService = new \App\Services\SalesOrderQueryService();
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

        $crService = new \App\Services\CrystalReportService();
        $result = $crService->printSo($so_id);

        if (!$result['success']) {
            abort(500, $result['message']);
        }

        return response()->file($result['file']);
    }

    public function updateBrandName(Request $request)
    {
        $queryService = new \App\Services\SalesOrderQueryService();
        $queryService->updateBrandName();

        return redirect()->back()->with('message', 'Berhasil Update!');
    }

    public function export(Request $request)
    {
        $filename = 'Sales-Order-Report-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new SalesOrderAwalExport, $filename);
    }

    public function search_kontrak(Request $request, $id, $merek)
    {
        try {
            $queryService = new \App\Services\SalesOrderQueryService();
            $result = $queryService->searchKontrak($request, $id, $merek);

            if (!$result['success']) {
                return response()->json(['message' => $result['message'], 'errors' => $result['errors'] ?? []], $result['code']);
            }

            return response()->json(['results' => $result['data']], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function get_product_kontrak(Request $request)
    {
        if ($request->ajax()) {
            $queryService = new \App\Services\SalesOrderQueryService();
            $result = $queryService->getProductKontrak($request->so_kontrak);

            return response()->json(['code' => 200, 'data' => $result['data']]);
        }

        return response()->json(['code' => 400, 'data' => []]);
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

                $workflowService = new \App\Services\SalesOrderWorkflowService();
                $workflowService->approvalMou($sales_order);

                DB::commit();
                $response['notification'] = [
                    'alert' => 'notify', 'type' => 'success', 'content' => 'Approval MOU berhasil disimpan.',
                ];
                $response['redirect_to'] = url()->previous();
                return response()->json($response, 200);

            }catch (\Exception $e) {
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $e->getMessage(),
                ];
                return $this->response(400, $response);
            }
        }
    }

    public function viewSalesOrderDetail($id)
    {
        $queryService = new \App\Services\SalesOrderQueryService();
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
}