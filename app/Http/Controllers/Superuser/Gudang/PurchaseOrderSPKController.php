<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Gudang\PurchaseOrderSummary;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\DataTables\Gudang\PurchaseOrderSPKTable;
use App\Exports\Gudang\PurchaseOrderDetailImportTemplate;
use App\Imports\Gudang\PurchaseOrderDetailImport;
use App\Entities\Gudang\MutasiOut;
use App\Entities\Gudang\MutasiOutDetail;
use App\Entities\Master\Warehouse;
use App\Services\PurchaseOrder\PurchaseOrderService;
use App\Helper\LogActivity;
use Illuminate\Support\Facades\Log;
use Auth;
use COM;
use DB;
use Excel;
use PDF;
use Validator;
use Carbon\Carbon;

class PurchaseOrderSPKController extends Controller
{
    protected $service;

    public function __construct(PurchaseOrderService $service){
        $this->service = $service;
        $this->view = "superuser.gudang.purchase_order_spk.";
        $this->route = "superuser.gudang.purchase_order_spk";
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

    public function json(Request $request, PurchaseOrderSPKTable $datatable)
    {
        return $datatable->build();
    }

    public function search_sku(Request $request)
    {
        $products = ProductPack::where('name', 'LIKE', '%'.$request->input('q', '').'%')
            ->where('status', ProductPack::STATUS['ACTIVE'])
            ->get(['id', 'code as text', 'name']);
        return ['results' => $products];
    }

    public function search_kemasan(Request $request)
    {
        $packagings = Packaging::where('pack_name', 'LIKE', '%'.$request->input('q', '').'%')
            ->where('status', Packaging::STATUS['ACTIVE'])
            ->get(['id', 'pack_name as text']);
        return ['results' => $packagings];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::where('type', PurchaseOrder::TYPE['SPK'])->get();
        // Sama seperti PO biasa: dropdown modal create di index.
        $data['warehouse'] = Warehouse::get();
        $data['brands'] = BrandLokal::where('status', BrandLokal::STATUS['ACTIVE'])->orderBy('brand_name')->get();

        return view($this->view."index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouse'] = Warehouse::get();
        // Sama seperti PO biasa: brand didefinisikan di awal (mengunci produk di step).
        $data['brands'] = BrandLokal::where('status', BrandLokal::STATUS['ACTIVE'])->orderBy('brand_name')->get();

        return view($this->view."create", $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {
            // Sama seperti PO biasa: brand wajib di awal (type SPK dipertahankan).
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:purchase_order,code',
                'warehouse' => 'required|integer',
                'brand_lokal_id' => 'required|integer|exists:master_brand_lokal,id',
                'etd'  =>  'required|date',
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
                $purchase_order = new PurchaseOrder;

                $purchase_order->code = $request->code;
                $purchase_order->warehouse_id = $request->warehouse;
                $purchase_order->brand_lokal_id = $request->brand_lokal_id;
                $purchase_order->etd = $request->etd;
                $purchase_order->type = 0;
                $purchase_order->note = $request->note;
                $purchase_order->created_by = Auth::id();

                $purchase_order->status = PurchaseOrder::STATUS['DRAFT'];

                if ($purchase_order->save()) {
                    LogActivity::addToLog('Created a new SPK: ' . $purchase_order->code);
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.step', ['id' => $purchase_order->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::find($id);

        return view($this->view."show", $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['purchase_order'] = PurchaseOrder::find($id);
        $data['warehouse'] = Warehouse::get();
        // Sama seperti PO biasa: brand bisa diubah dari halaman edit.
        $data['brands'] = BrandLokal::where('status', BrandLokal::STATUS['ACTIVE'])->orderBy('brand_name')->get();

        return view($this->view."edit", $data);
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
        if ($request->ajax()) {
            // Sama seperti PO biasa: update via service (termasuk pengaman ganti
            // brand bila masih ada baris beda brand). Type SPK dipertahankan
            // service karena form tidak mengirim field type.
            $result = $this->service->updatePo($id, $request->all());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            if ($result["status"] === "validation") {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $result["errors"],
                ];

                return $this->response(400, $response);
            }

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.step', ['id' => $result["po"]->id]);

            return $this->response(200, $response);
        }
    }

    public function step($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Sama seperti PO biasa: info header + tab kemasan dari service.
        $data['purchase_order'] = PurchaseOrder::with('brandLokal')->findOrFail($id);
        $data['merek'] = BrandLokal::get();
        $data['warehouses'] = Warehouse::get();
        $data['ref_po_code'] = $data['purchase_order']->ref_po_id
            ? PurchaseOrder::where('id', $data['purchase_order']->ref_po_id)->value('code')
            : null;

        $tabs = (new \App\Services\PurchaseOrder\PurchaseOrderDetailService)->packTabs();
        $data['pack_tabs'] = $tabs['pack_tabs'];
        $data['fixed_pack_ids'] = $tabs['fixed_pack_ids'];
        $data['other_packs'] = $tabs['other_packs'];
        $data['other_pack_ids'] = $tabs['other_pack_ids'];

        if($data['purchase_order']->status == PurchaseOrder::STATUS['ACC'] OR $data['purchase_order']->status == PurchaseOrder::STATUS['DELETED']) {
            return abort(404);
        }

        return view($this->view."step", $data);
    }

    public function detail_json($purchase_id)
    {
        // Sama seperti PO biasa: daftar detail untuk hot-reload per tab.
        $data = $this->service->listDetails($purchase_id, $this->route . '.detail');

        return response()->json(['IsError' => false, 'Data' => $data], 200);
    }

    public function publish(Request $request, $id)
    {
        if ($request->ajax()) {
            // Sama seperti PO biasa: pindah status via service.
            $result = $this->service->changeStatus($id, PurchaseOrder::STATUS['ACTIVE'], Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');

            return $this->response(200, $response);
        }
    }

    public function unpublish(Request $request, $id)
    {
        if ($request->ajax()) {
            // Sama seperti PO biasa: pindah status via service.
            $result = $this->service->changeStatus($id, PurchaseOrder::STATUS['DRAFT'], Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');

            return $this->response(200, $response);
        }
    }

    public function save_modify(Request $request, $id, $save_type)
    {
        if ($request->ajax()) {
            // Sama seperti PO biasa: logika save/ACC via service
            // (sekaligus memperbaiki $failed yang undefined di catch lama).
            $result = $this->service->saveModify($id, $save_type, Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            if ($result["status"] === "error") {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $result["message"],
                ];

                return $this->response(400, $response);
            }

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');

            return $this->response(200, $response);
        }
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order === null) {
                abort(404);
            }

            DB::beginTransaction();
            try{

                $purchase_order->acc_by = Auth::id();
                $purchase_order->acc_at = Carbon::now()->toDateTimeString();
                $purchase_order->status = PurchaseOrder::STATUS['ACC'];

                if ($purchase_order->save()) {
                     /**
                     * ===============================
                     * VALIDASI UNTUK CREATE MUTASI OUT
                     * ===============================
                     */

                    if (
                        is_null($purchase_order->ref_po_id) &&
                        $purchase_order->count_send_spk == 0
                    ){

                        // Validasi kode mutasi belum pernah dipakai
                        $existingMutasi = MutasiOut::where('code', $purchase_order->code)->first();

                        if ($existingMutasi) {
                            throw new \Exception('Kode Mutasi Out sudah digunakan sebelumnya.');
                        }

                        /**
                         * ===============================
                         * CREATE MUTASI OUT HEADER
                         * ===============================
                         */

                        // AMBIL ID GUDANG ARAYA
                        $warehouseAraya = Warehouse::where(function ($q) {
                            $q->where('name', 'like', '%araya%');
                        })
                        ->orderBy('id') // jika mau konsisten ambil yang paling lama
                        ->first();

                        // dd($warehouseAraya);

                        $mutasi = MutasiOut::create([
                            'code'           => $purchase_order->code, // pakai kode PO
                            'date'           => Carbon::now(),
                            'warehouse_from' => $warehouseAraya->id,
                            'warehouse_to'   => $purchase_order->warehouse_id ?? null, // sesuaikan
                            'note'           => $purchase_order->note ?? null,
                            'status'         => MutasiOut::STATUS['PUBLISH'],
                            'created_by'     => Auth::id(),
                        ]);

                        /**
                         * ===============================
                         * CREATE MUTASI OUT DETAIL
                         * ===============================
                         */

                        foreach ($purchase_order->purchase_order_detail as $detail) {

                            MutasiOutDetail::create([
                                'mutasi_out_id'        => $mutasi->id,
                                'product_packaging_id' => $detail->product_packaging_id,
                                'quantity'             => $detail->quantity,
                                'is_checked'           => 0,
                            ]);
                        }

                        // ===============================
                        // UPDATE PO → SET ref_mut_out_id
                        // ===============================

                        $purchase_order->ref_mut_out_id = $mutasi->id;
                        $purchase_order->updated_by = Auth::id();
                        $purchase_order->save();
                    }
                    
                    DB::commit();
                    $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');
                    return $this->response(200, $response);
                }
            }catch (\Exception $e) {
                // Sama seperti PO biasa: tanpa dd(), pesan error asli + log.
                DB::rollback();
                \Log::error('SPK acc failed', ['po_id' => $id, 'error' => $e->getMessage()]);
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $e->getMessage(),
                ];

                return $this->response(400, $response);
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
            // Sama seperti PO biasa: soft-delete via service (plus pencatatan log).
            $result = $this->service->deletePo($id, Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');
            return $this->response(200, $response);
        }
    }

    public function print_pdf($id)
    {
        if (empty($id) || !is_numeric($id)) {
            abort(404, 'PO ID tidak valid.');
        }

        $result = PurchaseOrder::find($id);
        if (!$result) {
            abort(404, 'PO tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.gudang.purchase_order_spk.print_pdf', $data)
                ->setPaper('a5', 'landscape');

        $generate = false; // Ubah sesuai logika bisnis.

        if ($generate) {
            return $pdf->download("PO-{$result->code}.pdf");
        }

        return $pdf->stream("PO-{$result->code}.pdf");
    }

    public function import_template()
    {
        $filename = 'purchase-order-detail-import-template.xlsx';
        return Excel::download(new PurchaseOrderDetailImportTemplate, $filename);
    }

    public function import(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new PurchaseOrderDetailImport($id);
            Excel::import($import, $request->import_file);
        
            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }
    
    public function cancel_acc(Request $request, $id)
    {
        try {

            // ===============================
            // VALIDASI ACCESS
            // ===============================
            if (Auth::user()->is_superuser == 0) {
                if (
                    empty($this->access) ||
                    empty($this->access->user) ||
                    $this->access->can_approve == 0
                ) {
                    abort(405);
                }
            }

            // ===============================
            // VALIDASI AJAX
            // ===============================
            if (!$request->ajax()) {
                abort(405);
            }

            // ===============================
            // CARI PURCHASE ORDER
            // ===============================
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order === null) {
                abort(404);
            }

            // ===============================
            // VALIDASI TIDAK BOLEH CANCEL
            // ===============================
            if (!is_null($purchase_order->ref_mut_out_id)) {

                $response['notification'] = [
                    'alert'   => 'block',
                    'type'    => 'alert-warning',
                    'header'  => 'Gagal',
                    'content' => 'Tidak bisa di cancel karena sudah ada proses Checker logistik',
                ];

                return $this->response(400, $response);
            }

            // ===============================
            // PROSES CANCEL
            // ===============================
            $purchase_order->acc_at = null;
            $purchase_order->acc_by = null;
            $purchase_order->updated_by = Auth::id();
            $purchase_order->status = PurchaseOrder::STATUS['DRAFT'];

            if ($purchase_order->save()) {

                $response['redirect_to'] = route(
                    'superuser.gudang.purchase_order_spk.index'
                );

                return $this->response(200, $response);
            }

            // ===============================
            // JIKA SAVE GAGAL
            // ===============================
            $response['notification'] = [
                'alert'   => 'block',
                'type'    => 'alert-danger',
                'header'  => 'Gagal',
                'content' => 'Data Purchase Order gagal di cancel.',
            ];

            return $this->response(500, $response);

        } catch (\Exception $e) {

            // ===============================
            // LOG ERROR
            // ===============================
            \Log::error('Gagal cancel approval Purchase Order', [
                'purchase_order_id' => $id,
                'user_id'            => Auth::id(),
                'message'            => $e->getMessage(),
                'file'               => $e->getFile(),
                'line'               => $e->getLine(),
            ]);

            // ===============================
            // RESPONSE ERROR
            // ===============================
            $response['notification'] = [
                'alert'   => 'block',
                'type'    => 'alert-danger',
                'header'  => 'Terjadi Kesalahan',
                'content' => 'Terjadi kesalahan saat membatalkan approval Purchase Order.',
            ];

            return $this->response(500, $response);
        }
    }

    public function send(Request $request, $id)
    {
        if ($request->ajax()) {
            // Sama seperti PO biasa: kirim + bentuk summary via service.
            $result = $this->service->sendPo($id, Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = route('superuser.gudang.purchase_order_spk.index');

            return $this->response(200, $response);
        }
    } 

    public function summary()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $summary = DB::table('purchase_order_summary')
            ->leftJoin('master_products_packaging', 'purchase_order_summary.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('purchase_order', 'purchase_order_summary.po_id', '=', 'purchase_order.id')
            ->select(
                'master_products_packaging.id as id',
                'master_products_packaging.name as produk_name',
                'master_products_packaging.code as produk_code',
                'master_packaging.pack_name as kemasan',
                DB::raw('SUM(purchase_order_summary.quantity) as total_quantity'),
                DB::raw('GROUP_CONCAT(DISTINCT purchase_order.code ORDER BY purchase_order.code SEPARATOR ", ") as kode_po'),
                'purchase_order_summary.created_at as created_at'
            )
            ->where('purchase_order_summary.status', PurchaseOrderSummary::STATUS['UNDONE'])
            ->where('purchase_order.type', 0)
            ->groupBy(
                'master_products_packaging.id',
                'master_products_packaging.name',
                'master_products_packaging.code',
                'master_packaging.pack_name'
            )
            ->orderBy('master_products_packaging.name', 'ASC')
            ->get();

        return view($this->view."summary", compact('summary'));
    }

    public function cancel_send(Request $request, $id)
    {
        if ($request->ajax()) {
            // Sama seperti PO biasa: batal kirim via service (termasuk blokir
            // bila sudah ada penerimaan terkait).
            $result = $this->service->cancelSend($id, Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            if (in_array($result["status"], ["has_receiving", "error"])) {
                return $this->response(400, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'content' => $result["message"],
                    ]
                ]);
            }

            return $this->response(200, [
                'notification' => [
                    'alert'   => 'notify',
                    'type'    => 'success',
                    'content' => $result["message"] !== "" ? $result["message"] : 'PO berhasil dibatalkan dari status SENT'
                ],
                'redirect_to' => route('superuser.gudang.purchase_order_spk.index')
            ]);
        }
    }

    public function listRefPo(Request $request)
    {
        $query = PurchaseOrder::where('type', 1)
            ->where('status', 4);

        // tambahkan filter pencarian kalau ada parameter q
        if ($request->has('q') && $request->q != '') {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%");
            });
        }

        // kasih limit supaya tidak terlalu banyak hasil
        $purchaseOrders = $query->limit(20)->get();

        return response()->json($purchaseOrders);
    }


    public function updateRefPo(Request $request, $id)
    {
        $request->validate([
            'ref_po_id' => 'required|exists:purchase_order,id',
        ]);

        $po = PurchaseOrder::findOrFail($id);
        $po->ref_po_id = $request->ref_po_id;
        $po->save();

        return response()->json([
            'success' => true,
            'message' => 'Ref PO berhasil diupdate',
            'data'    => $po,
        ]);
    }
}