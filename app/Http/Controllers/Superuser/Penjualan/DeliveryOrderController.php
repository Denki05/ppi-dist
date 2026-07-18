<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Customer;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Company;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderCost;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderLogPrint;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrderKontrak;
use App\Entities\Penjualan\SalesOrderKontrakItem;
use App\Entities\Penjualan\SalesOrderKontrakLog;
use App\Entities\Penjualan\SalesOrderKontrakPivot;
use App\DataTables\Penjualan\DeliveryOrdersTable;
use App\Entities\Finance\Invoicing;
use App\Entities\Master\Vendor;
use App\Entities\Master\Warehouse;
use App\Entities\Gudang\StockMove;
use App\Entities\Setting\UserMenu;
use App\Entities\Account\User;
use App\Notifications\DoNotification;
use Illuminate\Support\Facades\Log;
use App\Repositories\CodeRepo;
use Illuminate\Support\Collection;
use App\Services\StockService;
use GuzzleHttp\Client;
use Validator;
use App\Helper\LogActivity;
use PDF;
use DB;
use Auth;
use COM;


class DeliveryOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.penjualan.delivery_order.";
        $this->route = "superuser.penjualan.delivery_order";
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

    public function json(Request $request, DeliveryOrdersTable $datatable)
    {
        return $datatable->with('show', $request->show)->build($request);
    }

    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $field = $request->input('field');
        $search = $request->input('search');
        $type_transaction = $request->input('type_transaction');
        $table = PackingOrder::where(function($query2) use($field,$search){
                                if(!empty($field) && !empty($search)) {
                                    $fieldDb = '';
                                    $ids = array();
                                    if ($field == 'customer') {
                                        $customerDb = Customer::where('name', 'like', '%'.$search.'%')->get();
                                        for($c=0; $c<count($customerDb); $c++) $ids[$c] = $customerDb[$c]->id;
                                        $fieldDb = 'customer_id';
                                    } else if ($field == 'sales') {
                                        $salesDb = Sales::where('name', 'like', '%'.$search.'%')->get();
                                        for($c=0; $c<count($salesDb); $c++) $ids[$c] = $salesDb[$c]->id;
                                        $fieldDb = 'sales_id';
                                    } else if ($field == 'transaksi') {
                                        if (str_contains('cash', strtolower($search))) {
                                            $ids = [1];
                                        } else if (str_contains('tempo', strtolower($search))) {
                                            $ids = [2];
                                        } else if (str_contains('marketplace', strtolower($search))) {
                                            $ids = [3];
                                        }
                                        $fieldDb = 'type_transaction';
                                    } else if ($field == 'referensiSO') {
                                        // Cari SO > lalu cari SO Item > lalu cari DO Item > lalu cari Id nya

                                        // Cari SO
                                        $salesOrderDb = SalesOrder::where('code', 'like', '%'.$search.'%')->where('type_so', 'nonppn')->get();
                                        $salesOrderId = array();
                                        for($c=0; $c<count($salesOrderDb); $c++) $salesOrderId[$c] = $salesOrderDb[$c]->id;

                                        // Cari SO Item
                                        $salesOrderItemDb = SalesOrderItem::whereIn('so_id', $salesOrderId)->get();
                                        $salesOrderItemId = array();
                                        for($c=0; $c<count($salesOrderItemDb); $c++) $salesOrderItemId[$c] = $salesOrderItemDb[$c]->id;

                                        // Cari DO Item
                                        $packingOrderItemDb = PackingORderItem::whereIn('so_item_id', $salesOrderItemId)->get();
                                        for($c=0; $c<count($packingOrderItemDb); $c++) $ids[$c] = $packingOrderItemDb[$c]->do_id;

                                        $fieldDb = 'id';
                                    }
                                    
                                    if ($fieldDb != '') {
                                        $query2->where(function($query3)  use ($field, $fieldDb, $ids){
                                            if ($field == 'sales') {
                                                $query3->where('sales_senior_id',$ids);
                                                $query3->orWhereIn('sales_id',$ids);
                                            } else {
                                                $query3->whereIn($fieldDb, $ids);
                                            }
                                        });
                                    } else {
                                        $query2->where($field, 'like', '%'.$search.'%');
                                    }
                                }
                            })
                            ->whereIn('status', [2 ,3, 4, 5, 6])
                            ->orderBy('id','DESC')
                            ->get();

        // $table->withPath('delivery_order?field='.$field.'&search='.$search);
        $customer = Customer::all();
        $packing = PackingOrder::first();
        $data = [
            'table' => $table,
            'customer' => $customer,
            'packing' => $packing,
        ];
        return view($this->view."index",$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

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
    public function edit($id)
    {
        //
    }

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();
        $ekspedisi = Vendor::where('type', 1)->get();

        if(empty($result)){
            abort(404);
        }
        $data = [
            'result' => $result,
            'ekspedisi' => $ekspedisi
        ];
        return view($this->view."detail_new",$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function print($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();
        
        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\do\\do.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\'.$result->do_code.'.pdf';

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
        $creport->RecordSelectionFormula = "{penjualan_do.id}= $result->id";


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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\'.$result->do_code.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function packed(Request $request)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0) {
                return redirect()->route('superuser.index')
                    ->with('error', 'Anda tidak punya akses');
            }
        }

        DB::beginTransaction();

        try {

            $request->validate([
                'id' => 'required',
                'confirmed_items' => 'required|array'
            ]);

            $packing = PackingOrder::with(['do_detail.product_pack','so'])
                ->findOrFail($request->id);

            if ($packing->status != 3) {
                throw new \Exception('Status tidak valid untuk diproses.');
            }

            if ($packing->do_detail->count() == 0) {
                throw new \Exception('Tidak ada item untuk diproses.');
            }

            // ======================================
            // VALIDASI CHECKLIST (ANTI BYPASS JS)
            // ======================================

            $doItemIds = $packing->do_detail->pluck('id')->toArray();

            $confirmedIds = collect($request->confirmed_items)
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();

            if (count($doItemIds) !== count($confirmedIds)) {
                throw new \Exception('Semua item harus dikonfirmasi sebelum diproses.');
            }

            foreach ($doItemIds as $id) {
                if (!in_array($id, $confirmedIds)) {
                    throw new \Exception('Checklist item tidak lengkap.');
                }
            }

            // ======================================
            // VALIDASI LOGS & POTONG STOK FISIK
            // ======================================
            $stockService = new \App\Services\StockService();

            // ✅ 1. KELOMPOKKAN QTY CHECKER PER PRODUK
            $checkerQtys = [];

            foreach ($packing->do_detail as $item) {

                $base_id = preg_replace('/_\d+$/', '', $item->product_packaging_id);

                if (!isset($checkerQtys[$base_id])) {
                    $checkerQtys[$base_id] = 0;
                }

                $checkerQtys[$base_id] += $item->qty;
            }

            // ✅ 2. VALIDASI TOTAL CHECKER VS TOTAL LOGS
            foreach ($checkerQtys as $base_id => $totalCheckerQty) {

                $logQty = DB::table('do_stock_deduction_logs')
                    ->where('do_id', $packing->id)
                    ->where('product_packaging_id', $base_id)
                    ->where('status', 1)
                    ->sum('qty');

                if ((float)$totalCheckerQty > (float)$logQty) {
                    throw new \Exception(
                        "Gagal: Total Qty Checker untuk produk {$base_id} ({$totalCheckerQty}) melebihi kuota Pesanan di Log ({$logQty})."
                    );
                }
            }

            // ✅ 3. JIKA SEMUA VALID, LANGSUNG POTONG FISIK
            foreach ($packing->do_detail as $item) {

                $base_id = preg_replace('/_\d+$/', '', $item->product_packaging_id);

                $stockService->deductPhysicalStock(
                    $packing->warehouse_id,
                    $base_id,
                    $item->qty
                );
            }

            $packing->update(['status' => 4]);

            DB::commit();

            return redirect()
                ->route('superuser.penjualan.delivery_order.index')
                ->with('success', 'DO berhasil diubah ke Siap Kirim!');

        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function sending(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $result = PackingOrder::where('id',$post["id"])->first();
            $do_cost = PackingOrderDetail::where('do_id', $result->id)->first();

            PackingOrder::where('id',$result->id)->update([
                'date_sent' => date('Y-m-d')
            ]);

            if($result->status == 1){
                return redirect()->route('superuser.penjualan.packing_order.index')->with('error','Tidak bisa mengirim packing order yang masih baru dibuat');
            }
            if(count($result->do_detail) == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Tidak ada item sama sekali');
            }
            if($do_cost->grand_total_idr == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Harga didalam packing list belum di set');
            }
            $update = PackingOrder::where('id',$post["id"])->update(['status' => 5]);

            DB::commit();
            return redirect()->route('superuser.penjualan.delivery_order.index')->with('success','Delivery Order berhasil diubah ke delivery!');
            
        }catch(\Throwable $e){
            dd($e);
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function upload_image(Request $request) {
        $post = $request->all();
        $request->validate([
            'do_id' => 'required'
        ]);

        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->back()->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        if($request->method() == "POST"){
            DB::beginTransaction();

            try {
                $image = $request->file('image');

                if(!empty($image)){
                    $extension = $image->getClientOriginalExtension();
                    $valid_ext = ['jpeg','png','jpg','gif'];
    
                    if(!in_array(strtolower($extension), $valid_ext)){
                        return redirect()->route('superuser.penjualan.delivery_order.detail')->with('error',"Format image diperbolehkan yaitu jpeg,jpg,png,gif");
                    }
                
                    $data = [
                        'image' => (empty($image)) ? null : $image->store('images/delivery_order/expedition_receipt', 'public'),
                        'updated_by' => Auth::id(),
                    ];

                    $update = PackingOrder::where('id',$post["do_id"])->update($data);
                    
                    DB::commit();

                    return redirect()->route('superuser.penjualan.delivery_order.detail', $post['do_id'])->with('success','Image berhasil diupload');
                }
            }   catch(\Throwable $e){
                DB::rollback();
                return redirect()->back()->with('error',$e->getMessage());
            }
        }

        ResultData:
        return response()->json($data_json,200);
    }

    public function sent(Request $request)
    {
        // Initialize response data
        $data_json = [];

        // Validate request
        $validated = $request->validate([
            'do_id' => 'required|integer',
            'delivery_cost_idr' => 'nullable|numeric',
            'other_cost_idr' => 'nullable|numeric',
            'delivery_cost_note' => 'nullable|string',
            'other_cost_note' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        // Check user access
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0) {
                $data_json["IsError"] = true;
                $data_json["Message"] = 'Anda tidak punya akses untuk membuka menu terkait';
                return response()->json($data_json, 200);
            }
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            $post = $request->all();
            $do_id = $post["do_id"];

            // ======================================================
            // 1. TAMBAHKAN LOCK DISINI (Paling Atas) ANTI DOUBLE CLICK
            // ======================================================
            $get_do = PackingOrder::where('id', $do_id)->lockForUpdate()->firstOrFail();

            // 2. CEK STATUS: Mencegah Eksekusi Ulang
            if ($get_do->status == 6) {
                DB::rollBack();
                return redirect()->route('superuser.penjualan.delivery_order.index')
                                 ->with('success','DO sudah berhasil update resi sebelumnya!');
            }

            // ======================================================
            // ✅ VALIDASI STATUS LOG AKTIF SEBELUM UPDATE RESI
            // ======================================================
            $activeLogExists = DB::table('do_stock_deduction_logs')
                ->where('do_id', $do_id)
                ->where('status', 1) // 1 = Active
                ->exists();

            if (!$activeLogExists) {
                throw new \Exception('Update resi ditolak: Tidak ditemukan log kuota pesanan yang aktif. Dokumen ini kemungkinan sedang ditarik kembali ke SO.');
            }

            // ======================================================
            // 3. GENERATE & UPDATE DO SECARA LANGSUNG
            // ======================================================
            if (empty($get_do->do_code)) {
                $get_do->do_code = CodeRepo::generateDO();
                $get_do->date_sent = now()->format('Y-m-d');
            }

            $get_do->status = 6;
            
            if ($request->hasFile('image')) {
                $get_do->image = $request->file('image')->store('images/delivery_order/expedition_receipt', 'public');
            }
            if ($request->hasFile('image2')) {
                $get_do->image2 = $request->file('image2')->store('images/delivery_order/expedition_receipt', 'public');
            }
            
            $get_do->updated_by = Auth::id();
            $get_do->save(); // Simpan Data Utama

            $transactionCode = $get_do->do_code;

            // Get necessary data
            $result_cost = PackingOrderDetail::where('do_id', $do_id)->firstOrFail();
            $get_so = SalesOrder::where('id', $get_do->so_id)->firstOrFail();
            $customer = CustomerOtherAddress::where('id', $get_do->customer_other_address_id)->firstOrFail();
            $isProforma = optional($get_do->so)->is_proforma == 1;

            // Prepare update data
            $updateData = [
                'delivery_cost_note' => trim(htmlentities($post["delivery_cost_note"] ?? '')),
                'delivery_cost_idr' => $post["delivery_cost_idr"],
                'other_cost_note' => trim(htmlentities($post["other_cost_note"] ?? '')),
                'other_cost_idr' => $post["other_cost_idr"] ?? 0,
                'updated_by' => Auth::id(),
                'status_resi' => 1,
            ];

            if ($get_do->type_transaction == "TEMPO" && $customer->free_shipping == 1) {
                $updateData['other_cost_idr'] = $post["other_cost_idr"];
            } elseif ($get_do->type_transaction == "CASH" && $customer->free_shipping == 1) {
                $updateData['other_cost_idr'] = $post["other_cost_idr"];
            } elseif ($get_do->type_transaction == "TEMPO" && $customer->free_shipping == 0) {
                $updateData['delivery_cost_idr'] = $post["other_cost_idr"];
            } elseif ($get_do->type_transaction == "CASH" && $customer->free_shipping == 0) {
                $updateData['other_cost_idr'] = $post["other_cost_idr"];
            }

            $purchase_total = $result_cost->purchase_total_idr ?? 0;
            $updateData['grand_total_idr'] = $purchase_total + ($updateData['delivery_cost_idr'] ?? 0);

            // Update PackingOrderDetail
            PackingOrderDetail::where('do_id', $do_id)->update($updateData);

            // ======================================================
            // INSERT STOCK MOVE (OUTBOUND - DO DELIVERED)
            // ======================================================
            $alreadyMoved = \App\Entities\Gudang\StockMove::where('code_transaction', $transactionCode)->exists();
            
            if (!$alreadyMoved) {
                $stockService = new StockService();
                $items = PackingOrderItem::where('do_id', $do_id)->get();
            
                // ✅ DETEKSI LINTAS BULAN
                $soDate    = \Carbon\Carbon::parse($get_so->so_date);
                $now       = now();
                $isCrossMonth = $soDate->format('Y-m') !== $now->format('Y-m');
            
                $transactionDate = $isCrossMonth
                    ? $soDate->copy()->endOfDay()
                    : $now;
            
                if ($isCrossMonth) {
                    \Illuminate\Support\Facades\Log::info('⚠️ StockMove backdate terdeteksi', [
                        'do_code'          => $transactionCode,
                        'so_date'          => $soDate->toDateString(),
                        'update_resi_date' => $now->toDateString(),
                        'backdate_to'      => $transactionDate->toDateTimeString(),
                    ]);
                }
            
                // GROUPING PRODUCT (GABUNG FREE + NON FREE)
                $grouped = [];
                foreach ($items as $item) {
                    $pid = preg_replace('/_\d+$/', '', $item->product_packaging_id);
                    if (!isset($grouped[$pid])) {
                        $grouped[$pid] = 0;
                    }
                    $grouped[$pid] += (float) $item->qty;
                }
            
                // INSERT STOCK MOVE
                foreach ($grouped as $pid => $totalQty) {
                    $note = $transactionCode . ' - ' 
                          . ($get_do->member->name ?? '') . ' ' 
                          . ($get_do->member->text_kota ?? '');
            
                    $stockService->recordAdministrativeLog(
                        $get_do->warehouse_id,
                        $pid,
                        round($totalQty, 2),
                        $transactionCode,
                        $note,
                        $transactionDate 
                    );
                }
            }

            // Update SalesOrder items and log
            $salesOrderItemDB = SalesOrderItem::where('so_id', $get_do->so_id)->where('kontrak', 1)->get();
            foreach ($salesOrderItemDB as $item) {

                SalesOrderKontrakLog::create([
                    'code' => $get_so->code,
                    'customer_other_address_id' => $get_so->customer_other_address_id,
                    'so_kontrak_id' => $item->kontrak_id,
                    'so_id' => $item->so_id,
                    'qty_worked' => $item->qty_worked,
                    'created_at' => now(),
                ]);
            }

            // check invoice total = grand_total_idr
            $get_inv = DB::table('finance_invoicing')->where('do_id', $do_id)->first();
            $do_details = PackingOrderDetail::where('do_id', $do_id)->first();

            if ($get_inv && $do_details) {
                $new_grand_total = $do_details->purchase_total_idr + $do_details->delivery_cost_idr;

                if ($get_inv->grand_total_idr != $new_grand_total) {
                    DB::table('finance_invoicing')->where('do_id', $do_id)->update([
                        'grand_total_idr' => $new_grand_total
                    ]);
                }
            }

            // ======================================================
            // ✅ TUNTASKAN LOGS KE STATUS 2 (DONE)
            // ======================================================
            DB::table('do_stock_deduction_logs')
                ->where('do_id', $do_id)
                ->where('status', 1)
                ->update([
                    'status' => 2, // 2 = Done
                    'note' => 'Selesai (Update Resi)',
                    'updated_at' => now()
                ]);

            // Commit transaction
            DB::commit();

            $userIds = [32, 36];
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                $user->notify(new DoNotification($get_do));
            }

            LogActivity::addToLog('Update Resi DO: ' . $get_do->do_code);
            return redirect()->route('superuser.penjualan.delivery_order.index')->with('success','DO berhasil update resi!');

        } catch (\Throwable $e) {
            DB::rollback();
            $data_json["IsError"] = true;
            $data_json["Message"] = $e->getMessage();
            return response()->json($data_json, 200);
        }
    }

    public function get_cost(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                if(empty($post["do_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Order ID tidak boleh kosong";
                    goto ResultData;
                }

                $get = PackingOrderCost::where('do_id',$post["do_id"])->get();

                $data_json["IsError"] = FALSE;
                $data_json["Data"] = $get;

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
    
    Public function print_label($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::find($id);

        if($result == null){
            abort(404);
        }

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\do\\label_penerima.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelKirim-'.$result->member->name.'.pdf';

        $my_server      = "LOCAL"; 
        $my_user        = "root"; 
        $my_password    = ""; 
        $my_database    = "ppi-dist";
        $COM_Object     = "CrystalDesignRunTime.Application";


        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{penjualan_do.id}= $result->id";


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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelKirim-'.$result->member->name.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    Public function print_label_pengirim(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\do\\label_pengirim.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelPengirim-'.'.pdf';

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
        // $creport->RecordSelectionFormula = "{penjualan_do.id}= $result->id";


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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelPengirim-'.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    Public function print_label_unboxing(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\do\\label_unboxing.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelUnboxing-'.'.pdf';

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
        // $creport->RecordSelectionFormula = "{penjualan_do.id}= $result->id";


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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelUnboxing-'.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    Public function print_label_unboxing2(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\do\\label_unboxing2.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelUnboxing2-'.'.pdf';

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
        // $creport->RecordSelectionFormula = "{penjualan_do.id}= $result->id";


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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelUnboxing-'.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function print_manifest(Request $request, $id)
    {
        // =========================
        // CEK AKSES USER
        // =========================
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_print == 0) {
                return redirect()->route('superuser.index')
                    ->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        try {

            // =========================
            // CARI SO
            // =========================
            $so = DB::table('penjualan_so')->where('id', $id)->first();

            if ($so) {
                $result = PackingOrder::where('so_id', $so->id)->first();
            } else {
                $result = PackingOrder::where('id', $id)->first();
            }

            if (!$result) {
                abort(404);
            }

            // =========================
            // UPDATE PRINT COUNT
            // =========================
            $result->increment('print_count');

            // =========================
            // PATH CRYSTAL REPORT
            // =========================
            $reportPath = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\packing_plan\\packing_plan_rev.rpt";
            $exportPath = "C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\packing_plan\\export\\";
            $pdfFile = $exportPath . $result->code . ".pdf";

            // =========================
            // DATABASE CONFIG
            // =========================
            $server = "LOCAL";
            $database = "ppi-dist";
            $username = "root";
            $password = "";

            $COM_Object = "CrystalDesignRunTime.Application";

            // =========================
            // LOAD CRYSTAL REPORT
            // =========================
            $crapp = new COM($COM_Object) or die("Unable to Create Object");

            $creport = $crapp->OpenReport($reportPath, 1);

            $creport->Database->Tables(1)->SetLogOnInfo(
                $server,
                $database,
                $username,
                $password
            );

            $creport->EnableParameterPrompting = false;

            $creport->RecordSelectionFormula = "{penjualan_do.id}= " . $result->id;

            // =========================
            // EXPORT PDF
            // =========================
            $creport->ExportOptions->DiskFileName = $pdfFile;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = 31;

            $creport->Export(false);

            // =========================
            // RELEASE OBJECT
            // =========================
            $creport = null;
            $crapp = null;

            // =========================
            // DOWNLOAD FILE
            // =========================
            if (!file_exists($pdfFile)) {
                abort(404, 'File PDF tidak ditemukan');
            }

            return response()->download($pdfFile, $result->code . '.pdf');

        } catch (\Throwable $e) {

            return redirect()->back()->with('error', $e->getMessage());

        }
    }

    public function cancel_proses(Request $request)
    {
        $pass_code = $request->input('pass');
        $id = $request->input('id');

        if ($pass_code != 1122) {
            return response()->json(['message' => 'Token tidak sah!'], 401);
        }

        DB::beginTransaction();

        try {
            $do = PackingOrder::select('penjualan_do.*', 'coa.name', 'coa.text_kota')
                ->leftJoin('master_customer_other_addresses as coa', function ($join) {
                    $join->on(DB::raw('CAST(penjualan_do.customer_other_address_id AS UNSIGNED)'), '=', 'coa.id');
                })
                ->where('penjualan_do.id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($do->cashback_status == 1) {
                return response()->json([
                    'message' => 'Pesanan dengan cashback atau sudah update resi tidak dapat dibatalkan!'
                ], 403);
            }

            if ($do->status == 7) {
                return response()->json([
                    'message' => 'DO sudah pernah dibatalkan.'
                ], 400);
            }

            if (!in_array($do->status, [4,5,6])) {
                return response()->json([
                    'message' => 'Status DO tidak bisa dibatalkan.'
                ], 400);
            }

            $stockService = new StockService();

            foreach ($do->do_detail as $item) {
                $base_id = preg_replace('/_\d+$/', '', $item->product_packaging_id);
                $freeQty = $freeMap[$base_id] ?? 0;
                $normalQty = (float)$item->qty - (float)$freeQty;

                if ($normalQty <= 0) {
                    continue;
                }

                // Selalu kembalikan fisik stok ke rak (biar balance awal kembali netral)
                $stockService->undoDeductPhysicalStock(
                    $do->warehouse_id,
                    $base_id,
                    $normalQty
                );
            }

            // ======================================================
            // 🛑 LANGSUNG HAPUS (DELETE) DARI KARTU STOK AGAR CLEAN
            // ======================================================
            if (!empty($do->do_code)) {
                \App\Entities\Gudang\StockMove::where('code_transaction', $do->do_code)->delete();
            }

            // Simpan status lama
            if ($do->prev_sataus === null) {
                $do->prev_sataus = $do->status;
            }

            // Ubah status ke cancel
            $do->status = 7;
            $do->count_cancel += 1;
            $do->updated_by = auth()->id();
            $do->save();

            DB::commit();

            return response()->json([
                'message' => 'Proses berhasil dibatalkan dan record kartu stok telah dibersihkan!'
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function do_edit(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $post = $request->all();
        $result = PackingOrder::where('id', $post["id"])->first();
        $ekspedisi = Vendor::where('type', 1)->get();
        $warehouse = Warehouse::get();
        $rekening = DB::table('rekening')->get();

        if(empty($result)){
            abort(404);
        }
        $data = [
            'result' => $result,
            'ekspedisi' => $ekspedisi,
            'warehouse' => $warehouse,
            'rekening' => $rekening,
        ];
        return view($this->view."do_update",$data);
    }

    public function do_update(Request $request)
    {
        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $do = PackingOrder::findOrFail($request->id);
                $do_detail = PackingOrderDetail::findOrFail($request->cost_id);

                if ($do->count_cancel == 0) {
                    return response()->json(['failed' => 'DO belum di-cancel']);
                }

                if (empty($request->idr_rate)) {
                    return response()->json(['failed' => 'Kurs IDR tidak boleh kosong']);
                }

                $do->warehouse_id = $do->warehouse_id;
                $do->idr_rate = $this->parseCurrency($request->idr_rate);
                $do->status = $do_detail->status_resi == 1 ? 6 : 4;
                $do->updated_by = Auth::id();
                $do->save();

                // LOOP ITEM DO
                foreach ($request->repeater as $item) {
                    $poItem = PackingOrderItem::find($item['do_item_id']);
                    if (!$poItem) continue;

                    // Bersihkan format currency pada tiap item di dalam repeater
                    $usd_disc = $this->parseCurrency($item['usd_disc']);
                    $qty      = $this->parseCurrency($item['do_qty']);
                    $price    = $this->parseCurrency($item['price']);
                    $percent_disc = floatval($poItem->percent_disc);

                    $total_disc = $percent_disc > 0
                        ? ($usd_disc + (($price - $usd_disc) * $percent_disc / 100)) * $qty
                        : $usd_disc * $qty;

                    $total = ($price * $qty) - $total_disc;

                    $poItem->update([
                        'usd_disc'   => $usd_disc,
                        'qty'        => $qty,
                        'total_disc' => $total_disc,
                        'total'      => $total,
                        'price'      => $price,
                    ]);
                }

                // Hitung total IDR
                $items = PackingOrderItem::where('do_id', $request->id)->get();
                $rate = $do->idr_rate;
                $idr_total = $items->sum(function ($i) use ($rate) {
                    return (($i->price * $rate) * $i->qty) - ($i->total_disc * $rate);
                });

                // ============================================================
                // PARSE SEMUA INPUT CURRENCY AGAR JADI DECIMAL(16,2) STANDAR
                // ============================================================
                $disc1 = $this->parseCurrency($request->disc_agen_percent) / 100;
                $disc2 = $this->parseCurrency($request->disc_kemasan_percent) / 100;
                
                // Variabel di bawah ini dulu terlewat, sekarang sudah diparse dengan benar:
                $disc_agen_idr    = $this->parseCurrency($request->disc_agen_idr);
                $disc_kemasan_idr = $this->parseCurrency($request->disc_kemasan_idr);
                $disc_idr         = $this->parseCurrency($request->disc_tambahan_idr);
                
                $voucher  = $this->parseCurrency($request->voucher_idr);
                $delivery = $this->parseCurrency($request->delivery_cost_idr);
                $other    = $this->parseCurrency($request->resi_ongkir);

                // Gunakan round() alih-alih ceil() untuk akurasi presisi desimal
                $total_disc_idr     = round(($idr_total * $disc1) + (($idr_total - ($idr_total * $disc1)) * $disc2) + $disc_idr, 2);
                $purchase_total_idr = round($idr_total - $total_disc_idr - $voucher, 2);
                $grand_total_idr    = round($purchase_total_idr + $delivery + $other, 2);

                if ($total_disc_idr > $grand_total_idr) {
                    return response()->json(['failed' => 'Total diskon melebihi total belanja']);
                }

                // Update Detail DO
                $do_detail->update([
                    'discount_1'         => $request->disc_agen_percent,
                    'discount_1_idr'     => $disc_agen_idr,
                    'discount_2'         => $request->disc_kemasan_percent,
                    'discount_2_idr'     => $disc_kemasan_idr,
                    'discount_idr'       => $disc_idr,
                    'total_discount_idr' => $total_disc_idr,
                    'voucher_idr'        => $voucher,
                    'purchase_total_idr' => $purchase_total_idr,
                    'delivery_cost_idr'  => $delivery,
                    'other_cost_idr'     => $other,
                    'grand_total_idr'    => $grand_total_idr,
                    'updated_by'         => Auth::id(),
                ]);

                if ($do->invoicing && $do->invoicing->grand_total_idr != $grand_total_idr) {
                    $do->invoicing->update([
                        'grand_total_idr' => $grand_total_idr
                    ]);
                }

                // ======================================================
                // 🔥 LOGIKA MENGISI KEMBALI KARTU STOK PASCA UPDATE
                // ======================================================
                $stockService = new \App\Services\StockService();

                // 1. Potong kembali fisik stok ke rak berdasarkan Qty Baru (hasil editan)
                foreach ($items as $item) {
                    $base_id = preg_replace('/_\d+$/', '', $item->product_packaging_id);
                    $stockService->deductPhysicalStock($do->warehouse_id, $base_id, $item->qty);
                }

                // 2. Jika statusnya diset langsung ke 6 (Delivered / Update Resi)
                if ($do->status == 6) {
                    $transactionCode = $do->do_code;
                    $alreadyMoved = \App\Entities\Gudang\StockMove::where('code_transaction', $transactionCode)->exists();
                    
                    if (!$alreadyMoved && !empty($transactionCode)) {
                        $get_so = \App\Entities\Penjualan\SalesOrder::find($do->so_id);
                        $soDate = \Carbon\Carbon::parse($get_so->so_date);
                        $now = now();
                        $isCrossMonth = $soDate->format('Y-m') !== $now->format('Y-m');
                        $transactionDate = $isCrossMonth ? $soDate->copy()->endOfDay() : $now;

                        // Grouping product (Gabung qty)
                        $grouped = [];
                        foreach ($items as $item) {
                            $pid = preg_replace('/_\d+$/', '', $item->product_packaging_id);
                            if (!isset($grouped[$pid])) {
                                $grouped[$pid] = 0;
                            }
                            $grouped[$pid] += (float) $item->qty;
                        }

                        // Tulis baris baru ke Kartu Stok (Stock Move)
                        foreach ($grouped as $pid => $totalQty) {
                            $note = $transactionCode . ' - ' 
                                  . ($do->member->name ?? '') . ' ' 
                                  . ($do->member->text_kota ?? '');

                            $stockService->recordAdministrativeLog(
                                $do->warehouse_id,
                                $pid,
                                round($totalQty, 2),
                                $transactionCode,
                                $note,
                                $transactionDate
                            );
                        }
                    }
                }
                // ======================================================
                // END LOGIKA KARTU STOK
                // ======================================================

                DB::commit();
                return $this->response(200, [
                    'notification' => [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success, DO berhasil di update',
                    ],
                    'redirect_to' => route('superuser.penjualan.sales_order.index_lanjutan'),
                ]);

            } catch (\Exception $e) {
                dd($e);
                DB::rollBack();
                \Log::error("DO Update Error: " . $e->getMessage());
                return response()->json([
                    'notification' => [
                        'type' => 'error',
                        'content' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    ]
                ]);
            }
        }
    }

    // Helper function
    private function parseCurrency($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Ubah jadi string agar aman di-replace
        $value = (string) $value;

        // 1. Hapus titik (.) yang bertindak sebagai pemisah ribuan
        // Contoh: "1.800.000,50" -> "1800000,50"
        $value = str_replace('.', '', $value);

        // 2. Ubah koma (,) menjadi titik (.) agar PHP dan Database memahaminya sebagai desimal
        // Contoh: "1800000,50" -> "1800000.50"
        $value = str_replace(',', '.', $value);

        // Terakhir konversi ke float (aman untuk tipe decimal 16,2 di DB)
        return floatval($value);
    }

    public function unread_notif(Request $request, $id, $do)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if ($notification && $notification->notifiable_id == Auth::id()) {
            DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);

        }

        Return redirect()->route('superuser.penjualan.delivery_order.detail', ['id' => $do])->with('success', 'Notification marked as read.');
    }

    public function getNotifData()
    {
        $user = Auth::id(); // Get the currently authenticated user ID
        $notifications = DB::table('notifications')
            ->where('notifiable_id', $user)
            ->where('read_at', null)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $notifCount = DB::table('notifications')
            ->where('notifiable_id', $user)
            ->where('read_at', null)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'notifCount' => $notifCount,
        ]);
    }
    
    public function multiCancel(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $request->validate(['ids' => 'required|array']);
    
            $doList = PackingOrder::with('do_detail')
                ->whereIn('id', $request->ids)
                ->lockForUpdate()
                ->get();
    
            $stockService = new \App\Services\StockService();
    
            foreach ($doList as $do) {
    
                if ($do->status == 6) {
                    throw new \Exception("DO {$do->do_code} sudah Delivered dan tidak bisa dicancel.");
                }
    
                // ===============================
                // 5 â†’ 4 (Mundur dari Siap Kirim ke Packed)
                // ===============================
                if ($do->status == 5) {
                    $do->update(['status' => 4]);
                    continue;
                }
    
                // ===============================
                // 4 â†’ 3 (MUNDUR DARI PACKED KE CHECKER)
                // ===============================
                if ($do->status == 4) {
                    
                    // Cukup loop dari do_detail karena packed() juga memotong berdasarkan do_detail.
                    // undoDeductPhysicalStock otomatis akan:
                    // 1. Mengembalikan stok fisik (+ quantity)
                    // 2. Mengembalikan status booking (+ reserved_quantity)
                    foreach ($do->do_detail as $item) {
                        $base_id = preg_replace('/_\d+$/', '', $item->product_packaging_id);
    
                        $stockService->undoDeductPhysicalStock(
                            $do->warehouse_id,
                            $base_id,
                            (float)$item->qty
                        );
                    }
    
                    $do->update(['status' => 3]);
                    continue;
                }
    
                // ===============================
                // 3 â†’ 2 (Mundur dari Checker ke Draft)
                // ===============================
                if ($do->status == 3) {
                    $do->update(['status' => 2]);
                    continue;
                }
    
                throw new \Exception("Status DO {$do->do_code} tidak valid untuk cancel.");
            }
    
            DB::commit();
            return redirect()->back()->with('success', 'Berhasil dikembalikan/cancel.');
    
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}