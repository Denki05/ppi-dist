<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\DataTables\Gudang\ReceivingTable; 
use App\Entities\Master\Warehouse;
use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Exports\Gudang\ReceivingDetailImportTemplate;
use App\Imports\Gudang\ReceivingDetailImport;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Gudang\PurchaseOrderSummary;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\ProductPack;
use App\Entities\Finance\SettingFinance;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Entities\Setting\UserMenu;
use Auth;
use Excel;
use Carbon\Carbon;
use DomPDF;
use Validator;
use DB;

class ReceivingController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.receiving.";
        $this->route = "superuser.gudang.receiving";
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

    public function json(Request $request, ReceivingTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view($this->view."index");
    }

    public function create()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouses'] = Warehouse::get();

        return view($this->view."create", $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code'              => 'required|string|unique:receiving,code',
                'warehouse'         => 'required|integer',
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
                $receiving               = new Receiving;
                $receiving->code         = $request->code;
                $receiving->type         = $request->type ?? Receiving::TYPE['INBOUND'];
                $receiving->warehouse_id = $request->warehouse;
                $receiving->pbm_date     = $request->pbm_date;
                $receiving->note         = $request->note ?? null;
                $receiving->status       = Receiving::STATUS['ACTIVE'];

                if ($receiving->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.step', ['id' => $receiving->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['receiving'] = Receiving::find($id);

        return view($this->view."edit", $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $receiving = Receiving::find($id);

            if ($receiving == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:receiving,code,' . $receiving->id,
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
                $receiving->code = $request->code;
                $receiving->pbm_date = $request->pbm_date;
                $receiving->note = $request->note;

                if ($receiving->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.step', ['id' => $receiving->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function step($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.gudang.receiving.step', $data);
    }

    // public function publish(Request $request, $id)
    // {
    //     try {
    //         $receiving = Receiving::findOrFail($id);

    //         // Publish to QC
    //         if ($receiving->status == Receiving::STATUS['ACTIVE']) {
    //             if ($receiving->details->isEmpty()) {
    //                 return redirect()
    //                     ->back()
    //                     ->with('error', 'Receiving tidak memiliki list Product');
    //             }

    //             $receiving->status = Receiving::STATUS['QC'];
    //             $receiving->save();

    //             return redirect()
    //                 ->route('superuser.gudang.receiving.step', $receiving->id)
    //                 ->with('message', 'Receiving berhasil dipindah ke tahap QC');
    //         }

    //         // Publish to Ready
    //         if ($receiving->status == Receiving::STATUS['QC']) {

    //             /* ────────────────────────────────────────────────────────────────
    //             1. Re-kalkulasi quantity_ri & selisih untuk setiap detail
    //             ----------------------------------------------------------------*/
    //             foreach ($receiving->details as $detail) {

    //                 // hanya menghitung log ber-status OK
    //                 $qtyOkTotal = $detail->qcLogs()
    //                                     ->where('status_qc', 1)
    //                                     ->sum('qty_qc');

    //                 $detail->quantity_ri = $qtyOkTotal;
    //                 $detail->selisih     = $detail->quantity_po - $qtyOkTotal;
    //                 $detail->save();
    //             }

    //             /* ────────────────────────────────────────────────────────────────
    //             2. Validasi: semua detail harus sudah punya quantity_ri > 0
    //             ----------------------------------------------------------------*/
    //             $hasRi = $receiving->details->contains(function ($d) {
    //                 return is_null($d->quantity_ri) || $d->quantity_ri <= 0;
    //             });

    //             if ($hasRi) {
    //                 return redirect()
    //                     ->back()
    //                     ->with('error', 'Semua detail Receiving harus memiliki data QC yang valid');
    //             }

    //             /* ────────────────────────────────────────────────────────────────
    //             3. Ubah status → READY
    //             ----------------------------------------------------------------*/
    //             $receiving->status = Receiving::STATUS['READY'];
    //             $receiving->save();

    //             return redirect()
    //                 ->route('superuser.gudang.receiving.step', $receiving->id)
    //                 ->with('message', 'Receiving berhasil dipindah ke tahap Ready');
    //         }

    //         return redirect()
    //             ->back()
    //             ->with('error', 'Status tidak valid untuk diproses');
    //     } catch (\Exception $e) {
    //         return redirect()
    //             ->back()
    //             ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

    public function publish(Request $request, $id)
    {
        try {
            $receiving = Receiving::findOrFail($id);

            // Publish to QC
            if ($receiving->status == Receiving::STATUS['ACTIVE']) {
                if ($receiving->details->isEmpty()) {
                    return redirect()
                        ->back()
                        ->with('error', 'Receiving tidak memiliki list Product');
                }

                $receiving->status = Receiving::STATUS['QC'];
                $receiving->save();

                return redirect()
                    ->route('superuser.gudang.receiving.step', $receiving->id)
                    ->with('message', 'Receiving berhasil dipindah ke tahap QC');
            }

            // Publish to Ready
            if ($receiving->status == Receiving::STATUS['QC']) {

                // 1. Re-kalkulasi quantity_ri dan selisih
                foreach ($receiving->details as $detail) {
                    // Akumulasi semua log QC
                    $totalQtyQc = $detail->qcLogs()->sum('qty_qc');

                    $detail->quantity_ri = $totalQtyQc;
                    $detail->selisih     = $detail->quantity_po - $totalQtyQc;
                    $detail->save();

                    // 🔒 Tambahan validasi: cek apakah ada log QC yang belum di-approve
                    $hasUnapprovedSellable = $detail->qcLogs()
                        ->where('is_sellable', 1)
                        ->where('is_approved', 0)
                        ->exists();

                    if ($hasUnapprovedSellable) {
                        return redirect()
                            ->back()
                            ->with('error', "Terdapat item QC yang sudah ditandai saleable namun belum di-approve pada produk: {$detail->product_pack->name}");
                    }
                }

                // 2. Validasi: setiap detail harus memiliki quantity_ri > 0
                $hasInvalidRi = $receiving->details->contains(function ($d) {
                    return is_null($d->quantity_ri) || $d->quantity_ri <= 0;
                });

                if ($hasInvalidRi) {
                    return redirect()
                        ->back()
                        ->with('error', 'Semua detail Receiving harus memiliki data QC yang valid (jumlah yang diterima > 0).');
                }

                // 3. Update status menjadi READY
                $receiving->status = Receiving::STATUS['READY'];
                $receiving->save();

                return redirect()
                    ->route('superuser.gudang.receiving.step', $receiving->id)
                    ->with('message', 'Receiving berhasil dipindah ke tahap Ready');
            }


            return redirect()
                ->back()
                ->with('error', 'Status tidak valid untuk diproses');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function acc_ri(Request $request, $id)
    {
        DB::beginTransaction();
    
        try {
            $receiving = Receiving::findOrFail($id);
    
            // Validasi status
            if ($receiving->status != Receiving::STATUS['READY']) {
                return $this->response(400, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'content' => 'Receiving tidak dalam status READY'
                    ]
                ]);
            }
    
            // ✅ Lock receiving untuk prevent concurrent ACC
            $receiving = Receiving::where('id', $id)
                ->lockForUpdate()
                ->first();
    
            // Initialize StockService
            $stockService = app(\App\Services\StockService::class);
    
            // Track total per product & PO untuk smart deduction
            $poDeductionMap = []; // Format: [po_id => [product_id => qty_to_deduct]]
    
            foreach ($receiving->details as $detail) {
                // Hanya proses yang sellable (is_sellable = 0)
                $qtyToStock = $detail->qcLogs()
                                    ->where('is_sellable', 0)
                                    ->sum('qty_qc');
    
                if ($qtyToStock <= 0) {
                    continue;
                }
    
                $poId = $detail->po_id;
                $productId = $detail->product_packaging_id;
    
                // Track untuk deduction PO nanti
                if (!isset($poDeductionMap[$poId])) {
                    $poDeductionMap[$poId] = [];
                }
                if (!isset($poDeductionMap[$poId][$productId])) {
                    $poDeductionMap[$poId][$productId] = 0;
                }
                $poDeductionMap[$poId][$productId] += $qtyToStock;
    
                // ✅ Input stock pakai StockService (atomic + balance accurate)
                $stockService->replayHistoricalLog(
                    $receiving->warehouse_id,
                    $productId,
                    $qtyToStock,
                    'IN',  // IN karena receiving
                    'RI-' . $receiving->code,
                    now(),  // transactionDate
                    'Receiving ACC - ' . $detail->product_pack->name
                );
            }
    
            // ✅ POTONG PO SUMMARY (hanya jika tipe INBOND)
            if ($receiving->type == 0) { // INBOND
                foreach ($poDeductionMap as $poId => $productMap) {
                    foreach ($productMap as $productId => $qtyToCut) {
                        
                        // ✅ FIX #1: Query yang benar dan simple
                        // Cari PO Summary untuk PO ini + Product ini dengan status Active (2)
                        $summaries = PurchaseOrderSummary::where([
                                ['product_packaging_id', $productId],
                                ['po_id', $poId],  // ✅ DIRECT column, bukan subquery!
                                ['status', 2]  // Status = Active/Open
                            ])
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->get();
    
                        $sisaToCut = (float)$qtyToCut;
    
                        // ✅ FIX #2: Track yang sudah dikurangi
                        $deductedQty = 0;
    
                        foreach ($summaries as $sum) {
                            if ($sisaToCut <= 0) break;
    
                            $ambil = min($sisaToCut, (float)$sum->quantity);
                            $sum->quantity -= $ambil;
                            $sisaToCut -= $ambil;
                            $deductedQty += $ambil;
    
                            if ($sum->quantity == 0) {
                                $sum->status = 1; // Mark as done
                            }
    
                            $sum->save();
                        }
    
                        // ✅ FIX #3: Better error handling (tidak disabled, tapi informative)
                        if ($sisaToCut > 0) {
                            \Log::warning('PO deduction incomplete', [
                                'receiving_id' => $receiving->id,
                                'po_id' => $poId,
                                'product_id' => $productId,
                                'qty_to_cut' => $qtyToCut,
                                'deducted' => $deductedQty,
                                'remaining' => $sisaToCut,
                                'summaries_count' => $summaries->count(),
                            ]);
    
                            // Opsi: Throw error atau just log?
                            // Uncomment jika mau strict validation:
                            // throw new \Exception(
                            //     "PO {$poId} Product {$productId}: "
                            //     . "Qty tidak cukup. Perlu: {$qtyToCut}, "
                            //     . "Hanya ada: {$deductedQty}. Kurang: {$sisaToCut}"
                            // );
                        }
                    }
                }
            } elseif ($receiving->type == 1) { // RETUR
                // Update warehouse_id pada retur
                foreach ($receiving->details as $detail) {
                    DB::table('penjualan_retur')
                        ->where('id', $detail->po_id)
                        ->update(['warehouse_id' => $receiving->warehouse_id]);
                }
            }
    
            // ✅ Update receiving status
            $receiving->status = Receiving::STATUS['ACC'];
            $receiving->acc_by = Auth::id();
            $receiving->acc_at = now();
            $receiving->save();
    
            DB::commit();
    
            return $this->response(200, [
                'notification' => [
                    'alert'   => 'notify',
                    'type'    => 'success',
                    'content' => 'Receiving berhasil di-ACC'
                ],
                'redirect_to' => route('superuser.gudang.receiving.index')
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            \Log::error('ACC Receiving error', [
                'receiving_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->response(500, [
                'notification' => [
                    'alert'   => 'block',
                    'type'    => 'alert-danger',
                    'content' => 'Terjadi kesalahan: ' . $e->getMessage()
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

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.gudang.receiving.show', $data);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    abort(405);
                }
            }

            $receiving = Receiving::find($id);

            if ($receiving === null) {
                abort(404);
            }

            $receiving->status = Receiving::STATUS['DELETED'];

            if ($receiving->save()) {

                $response['redirect_to'] = route('superuser.gudang.receiving.index');
                return $this->response(200, $response);
            }
        }
    }
    
    public function import_template()
    {
        $filename = 'receiving-detail-import-template.xlsx';
        return Excel::download(new ReceivingDetailImportTemplate, $filename);
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
            $import = new ReceivingDetailImport($id);
            Excel::import($import, $request->import_file);
        
            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }

    public function cancel(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                    abort(405);
                }
            }

            DB::beginTransaction();

            try {
                $receiving = Receiving::with('details.qcLogs')->findOrFail($id);

                // Cek apakah ada log QC
                $hasQcLogs = $receiving->details->pluck('qcLogs')->flatten()->isNotEmpty();
                if ($hasQcLogs) {
                    // return response()->json(['status' => 'error', 'message' => 'Receiving tidak bisa dibatalkan karena sudah ada item QC.']);
                    return $this->response(400, [
                            'notification' => [
                                'alert'   => 'block',
                                'type'    => 'alert-danger',
                                'content' => 'Receiving tidak bisa dibatalkan karena sudah ada item QC.'
                            ]
                    ]);
                }

                $receiving->status = Receiving::STATUS['ACTIVE'];
                $receiving->save();

                DB::commit();

                return $this->response(200, [
                    'notification' => [
                        'alert'   => 'notify',
                        'type'    => 'success',
                        'content' => 'Receiving berhasil di batalkan'
                    ],
                    'redirect_to' => route('superuser.gudang.receiving.index')
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->response(500, [
                    'notification' => [
                        'alert'   => 'notify',
                        'type'    => 'error',
                        'content' => 'Terjadi kesalahan: ' . $e->getMessage()
                    ]
                ]);
            }
        }
    }
}