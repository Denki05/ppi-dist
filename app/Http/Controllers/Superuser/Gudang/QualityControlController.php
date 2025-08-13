<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\DataTables\Gudang\QualityControlTable; 
use App\Entities\Master\Warehouse;
use App\Entities\Gudang\QualityControl;
use App\Entities\Gudang\QualityControlDetail;
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

class QualityControlController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.quality_control.";
        $this->route = "superuser.gudang.quality_control";
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

    public function json(Request $request, QualityControlTable $datatable)
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
        if (!$request->ajax()) return;  // guard

        $validator = Validator::make($request->all(), [
            'code'      => 'required|string|unique:receiving,code',
            'warehouse' => 'required|integer'
        ]);
        if ($validator->fails()) return $this->validationError($validator);

        $receiving               = new Receiving;
        $receiving->code         = $request->code;
        $receiving->type         = $request->type ?? Receiving::TYPE['INBOUND'];
        $receiving->warehouse_id = $request->warehouse;
        $receiving->pbm_date     = $request->pbm_date;
        $receiving->note         = $request->note ?? null;
        $receiving->status       = Receiving::STATUS['ACTIVE'];
        $receiving->save();

        return $this->response(200, [
            'notification' => [
                'alert'   => 'notify',
                'type'    => 'success',
                'content' => 'Receiving created successfully',
            ],
            'redirect_to' => route('superuser.gudang.receiving.step', $receiving->id)
        ]);
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

        $data['receiving'] = QualityControl::findOrFail($id);

        return view('superuser.gudang.quality_control.step', $data);
    }

    // public function publish(Request $request, $id)
    // {
    //     try {
    //         $receiving = QualityControl::findOrFail($id);

    //         // Publish to QC
    //         if ($receiving->status == QualityControl::STATUS['ACTIVE']) {
    //             if ($receiving->details->isEmpty()) {
    //                 return redirect()
    //                     ->back()
    //                     ->with('error', 'Receiving tidak memiliki list Product');
    //             }

    //             $receiving->status = QualityControl::STATUS['QC'];
    //             $receiving->save();

    //             return redirect()
    //                 ->route('superuser.gudang.receiving.step', $receiving->id)
    //                 ->with('message', 'Receiving berhasil dipindah ke tahap QC');
    //         }

    //         // Publish to Ready
    //         if ($receiving->status == QualityControl::STATUS['QC']) {

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
            $receiving = QualityControl::findOrFail($id);

            // Publish to QC
            if ($receiving->status == QualityControl::STATUS['ACTIVE']) {
                if ($receiving->details->isEmpty()) {
                    return redirect()
                        ->back()
                        ->with('error', 'Receiving tidak memiliki list Product');
                }

                $receiving->status = QualityControl::STATUS['QC'];
                $receiving->save();

                return redirect()
                    ->route('superuser.gudang.quality_control.step', $receiving->id)
                    ->with('message', 'Receiving berhasil dipindah ke tahap QC');
            }

            // Publish to Ready
            if ($receiving->status == QualityControl::STATUS['QC']) {

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
                $receiving->status = QualityControl::STATUS['READY'];
                $receiving->save();

                return redirect()
                    ->route('superuser.gudang.quality_control.step', $receiving->id)
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
        if ($request->ajax()) {
            DB::beginTransaction();

            try {
                $receiving = QualityControl::findOrFail($id);

                if ($receiving->status != QualityControl::STATUS['READY']) {
                    return $this->response(400, [
                        'notification' => [
                            'alert'   => 'block',
                            'type'    => 'alert-danger',
                            'content' => 'Receiving tidak dalam status READY'
                        ]
                    ]);
                }

                foreach ($receiving->details as $detail) {
                    $qtyToStock = $detail->qcLogs()
                                        ->where('is_sellable', 0)
                                        ->sum('qty_qc');

                    if ($qtyToStock <= 0) {
                        continue;
                    }

                    /** Jika type = 0 (inbond), lakukan pemotongan PO summary */
                    if ($receiving->type == 0) {
                        $sisaToCut = $qtyToStock;

                        $summaries = PurchaseOrderSummary::where([
                                ['product_packaging_id', $detail->product_packaging_id],
                                ['status', 2]
                            ])->orderBy('id')
                            ->lockForUpdate()
                            ->get();

                        foreach ($summaries as $sum) {
                            if ($sisaToCut <= 0) break;

                            $ambil = min($sisaToCut, $sum->quantity);
                            $sum->quantity -= $ambil;
                            $sisaToCut -= $ambil;

                            if ($sum->quantity == 0) {
                                $sum->status = 1;
                            }

                            $sum->save();
                        }

                        if ($sisaToCut > 0) {
                            DB::rollBack();
                            return $this->response(400, [
                                'notification' => [
                                    'alert'   => 'block',
                                    'type'    => 'alert-danger',
                                    'content' => 'Stok PO Summary tidak mencukupi untuk '
                                            . $detail->product_pack->code . ' - '
                                            . $detail->product_pack->name
                                ]
                            ]);
                        }
                    }elseif ($receiving->type == 1) {
                        // update warehouse_id pada retur
                        $getRetur = DB::table('penjualan_retur')
                            ->where('id', $detail->po_id)
                            ->update(['warehouse_id' => $receiving->warehouse_id]);
                    }
                    // Jika type == 1 (retur), tidak perlu potong PO

                    /** Update / insert ke ProductMinStock */
                    $minStock = ProductMinStock::firstOrNew([
                        'product_packaging_id' => $detail->product_packaging_id,
                        'warehouse_id'         => $receiving->warehouse_id,
                    ]);

                    if (!$minStock->exists) {
                        $prodPack = ProductPack::find($detail->product_packaging_id);
                        $minStock->unit_id       = $prodPack->unit_id ?? 1;
                        $minStock->selling_price = 0;
                        $minStock->quantity      = 0;
                    }

                    $minStock->quantity += $qtyToStock;
                    $minStock->save();

                    /** Insert ke StockMove */
                    StockMove::create([
                        'warehouse_id'         => $receiving->warehouse_id,
                        'product_packaging_id' => $detail->product_packaging_id,
                        'code_transaction'     => 'RI-'.$receiving->code,
                        'stock_in'             => $qtyToStock,
                        'stock_out'            => 0,
                        'stock_balance'        => $minStock->quantity,
                        'created_by'           => Auth::id(),
                    ]);
                }

                $receiving->status = QualityControl::STATUS['ACC'];
                $receiving->acc_by = Auth::id();
                $receiving->acc_at = now();
                $receiving->save();

                DB::commit();

                return $this->response(200, [
                    'notification' => [
                        'alert'   => 'notify',
                        'type'    => 'success',
                        'content' => 'Receiving berhasil di ACC'
                    ],
                    'redirect_to' => route('superuser.gudang.quality_control.index')
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return $this->response(500, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'content' => 'Terjadi kesalahan: '.$e->getMessage()
                    ]
                ]);
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