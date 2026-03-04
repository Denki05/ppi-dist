<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Gudang\ReceivingQcLogs;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Gudang\PurchaseOrderSummary;
use App\Entities\Penjualan\SaleReturnDetail;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Entities\Setting\UserMenu;
use DB;

class ReceivingDetailController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.receiving.detail.";
        $this->route = "superuser.gudang.receiving.detail";
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

    public function get_sku_json(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['code' => 301]);
        }

        $receivingId = $request->receiving_id;
        $productPackId = $request->id;

        $receiving = Receiving::findOrFail($receivingId);

        if ($receiving->type == 1) {
            $returDetail = DB::table('penjualan_retur_detail AS d')
                ->join('penjualan_retur AS r', 'r.id', '=', 'd.retur_id')
                ->join('master_products_packaging AS p', 'p.id', '=', 'd.product_packaging_id')
                ->where('r.status', 1)
                ->whereNull('r.deleted_at')
                ->where('r.type', 1)
                ->where('d.product_packaging_id', $productPackId)
                ->select(
                    'd.product_packaging_id AS product_pack_id',
                    'p.name AS product_name',
                    'p.code AS sku',
                    'p.id AS packaging_id',
                    'd.qty AS retur_qty',
                    'r.id AS retur_id',
                    'r.code AS retur_code'
                )
                ->first();

            if (!$returDetail) {
                return response()->json(['code' => 404, 'message' => 'Data retur tidak ditemukan']);
            }

            // Hitung qty yang sudah pernah dimasukkan dalam receiving_detail
            $qtyCurrent = DB::table('receiving_detail')
                ->where('receiving_id', $receivingId)
                ->where('product_packaging_id', $productPackId)
                ->sum('quantity_po');

            $available = max($returDetail->retur_qty - $qtyCurrent, 0);

            return response()->json([
                'code' => 200,
                'data' => [
                    'product_pack_id' => $returDetail->product_pack_id,
                    'product' => [
                        'id' => $returDetail->packaging_id,
                        'sku' => $returDetail->sku,
                        'product_name' => $returDetail->product_name,
                    ],
                    'packaging' => null,
                    'po_id' => $returDetail->retur_id, // opsional
                    'quantity' => $available,
                    'retur_code' => $returDetail->retur_code,
                ]
            ]);
        }

        // Default: Inbond dari PO
        $productPack = ProductPack::with('packaging')->findOrFail($productPackId);

        $totalRemaining = PurchaseOrderSummary::where([
            ['status', 2],
            ['product_packaging_id', $productPack->id],
        ])->sum('quantity');

        $qtyCurrent = ReceivingDetail::where([
            ['receiving_id', $receivingId],
            ['product_packaging_id', $productPack->id],
        ])->sum('quantity_po');

        $available = max($totalRemaining - $qtyCurrent, 0);

        $firstPO = PurchaseOrderSummary::where([
            ['status', 2],
            ['product_packaging_id', $productPack->id],
        ])->value('po_id');

        return response()->json([
            'code' => 200,
            'data' => [
                'product_pack_id' => $productPack->id,
                'product' => $productPack,
                'packaging' => $productPack->packaging,
                'po_id' => $firstPO,
                'quantity' => $available
            ]
        ]);
    }

    public function show($id, $detail_id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['receiving'] = Receiving::findOrFail($id);
        $data['receiving_detail'] = ReceivingDetail::findOrFail($detail_id);

        return view('superuser.gudang.receiving_detail.show', $data);
    }
    
    public function create($id)
    {
        $receiving = Receiving::findOrFail($id);
        $products = collect();

        if ($receiving->type == 1) {
            // Retur customer
            $products = DB::table('penjualan_retur_detail AS d')
                ->join('penjualan_retur AS r', 'r.id', '=', 'd.retur_id')
                ->join('master_products_packaging AS p', 'p.id', '=', 'd.product_packaging_id')
                ->select(
                    'p.id AS product_pack_id',
                    'p.code',
                    'p.name',
                    DB::raw('NULL AS pack_name'),
                    'd.qty AS qty_available',
                    'r.code AS retur_code'
                )
                ->where('r.status', 1)
                ->whereNull('r.deleted_at')
                ->where('r.type', 1)
                ->get();
        } else {
            // Inbond dari PO
            $summaries = PurchaseOrderSummary::where('status', 2)
                ->with(['product.packaging'])
                ->get()
                ->groupBy('product_packaging_id');

            foreach ($summaries as $pack_id => $grp) {
                $first = $grp->first();
                $qty_remaining = $grp->sum('quantity');

                $qty_current = ReceivingDetail::where([
                    ['receiving_id', $receiving->id],
                    ['product_packaging_id', $pack_id]
                ])->sum('quantity_po');

                $sisa_dropdown = $qty_remaining - $qty_current;

                if ($sisa_dropdown > 0) {
                    $products->push((object) [
                        'product_pack_id' => $pack_id,
                        'code'            => $first->product->code,
                        'name'            => $first->product->name,
                        'pack_name'       => $first->product->packaging->pack_name,
                        'qty_remaining'   => $qty_remaining,
                        'qty_current'     => $qty_current,
                        'qty_available'   => $sisa_dropdown,
                    ]);
                }
            }
        }

        return view('superuser.gudang.receiving_detail.create', compact('receiving', 'products'));
    }

    public function store(Request $request, $id)
    {
        if (!$request->ajax()) {
            return $this->response(400, ['notification' => ['alert'=>'block','type'=>'alert-danger','content'=>'Invalid']]);
        }

        $receiving = Receiving::find($id);
        if (!$receiving) abort(404);

        $available = 0;

        if ($receiving->type == 1) {
            $available = floatval(SaleReturnDetail::where([
                ['product_packaging_id', $request->product_pack_id]
            ])->sum('qty'));
        } else {
            $available = floatval(PurchaseOrderSummary::where([
                ['status', 2],
                ['product_packaging_id', $request->product_pack_id]
            ])->sum('quantity'));
        }

        $validator = Validator::make($request->all(), [
            'product_pack_id' => 'required|exists:master_products_packaging,id',
            'no_batch'        => 'nullable|string|max:50',
            'quantity'        => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($available, $receiving) {
                    if ($value > $available) {
                        $fail('Jumlah melebihi stok yang tersedia.');
                    }
                }
            ],
            'description'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->response(400, [
                'notification' => [
                    'alert'   => 'block',
                    'type'    => 'alert-danger',
                    'header'  => 'Error',
                    'content' => $validator->errors()->all(),
                ]
            ]);
        }

        /* ambil PO id pertama yg masih UNDONE utk produk tsb */
        $poId = null;

        if ($receiving->type == 1) {
            // Retur → cari dari penjualan_retur_detail
            $returId = \DB::table('penjualan_retur_detail AS d')
                ->join('penjualan_retur AS r', 'r.id', '=', 'd.retur_id')
                ->where('r.status', 1)
                ->whereNull('r.deleted_at')
                ->where('r.type', 1)
                ->where('d.product_packaging_id', $request->product_pack_id)
                ->orderBy('r.id')
                ->value('r.id');

            $poId = $returId; // ← disisipkan ke field po_id
        } else {
            // Inbond → ambil dari PurchaseOrderSummary
            $poId = PurchaseOrderSummary::where([
                ['status', 2],
                ['product_packaging_id', $request->product_pack_id]
            ])->orderBy('id')->value('po_id');
        }

        $detail                         = new ReceivingDetail;
        $detail->receiving_id           = $receiving->id;
        $detail->po_id                  = $poId;                    // opsional, bisa NULL
        $detail->product_packaging_id   = $request->product_pack_id;
        $detail->quantity_po            = $request->quantity;
        if ($receiving->type == 1) {
            $detail->no_batch               = $request->no_batch;
        }else {
            $detail->no_batch               = $request->no_batch; // untuk Inbond, no_batch bisa NULL
        }
        $detail->note                   = $request->description ?? null;

        if (!$detail->save()) {
            return $this->response(500, ['notification'=>['alert'=>'block','type'=>'alert-danger','content'=>'Save failed']]);
        }

        /* 4. response sukses ───────────────────────── */
        return $this->response(200, [
            'notification' => [
                'alert'   => 'notify',
                'type'    => 'success',
                'content' => 'Success',
            ],
            'redirect_to' => route('superuser.gudang.receiving.step', $id),
        ]);
    }

    public function edit($id, $detail_id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['receiving'] = Receiving::findOrFail($id);
        $data['receiving_detail'] = ReceivingDetail::findOrFail($detail_id);

        return view('superuser.gudang.receiving_detail.edit', $data);
    }

    public function update(Request $request, $id, $detail_id)
    {
        if ($request->ajax()) {
            $receiving_detail = ReceivingDetail::find($detail_id);

            if ($receiving_detail == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'description' => 'nullable',
                'delivery_cost' => 'nullable|numeric'
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
                $receiving_detail->no_batch = $request->no_batch;
                // $receiving_detail->delivery_cost = $request->delivery_cost ?? 0;

                if ($receiving_detail->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.step', ['id' => $id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id, $detail_id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            $receiving = Receiving::find($id);
            $receiving_detail = ReceivingDetail::find($detail_id);

            if ($receiving === null OR $receiving_detail === null) {
                abort(404);
            }

            if ($receiving_detail->delete()) {
                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }

    public function storeQc(Request $req, $detailId)
    {
        try {
            if ($req->ajax()) {
                $detail    = ReceivingDetail::findOrFail($detailId);
                $receiving = $detail->receiving;

                $req->validate([
                    'qty_qc'      => 'required|numeric|min:0.1',
                    'status_qc'   => ['required', Rule::in(['OK','NOT OK','Not OK'])],
                    'is_sellable' => 'sometimes|boolean',
                ]);

                $qtyQc      = floatval($req->qty_qc);
                $statusOk   = strtoupper($req->status_qc) === 'OK';
                $isSellable = $req->boolean('is_sellable');

                $existingTotalQc = $detail->qcLogs()->sum('qty_qc');
                $newTotal = $existingTotalQc + $qtyQc;

                if ($newTotal > $detail->quantity_po) {
                    return response()->json([
                        'notification' => [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Validasi Gagal',
                            'content' => "Total QC melebihi jumlah PO. Sisa maksimal: " . number_format($detail->quantity_po - $existingTotalQc, 1) . " kg"
                        ]
                    ], 400);
                }

                // dd($detail->product_packaging_id);

                // Simpan QC log - tidak proses stok di sini
                ReceivingQcLogs::create([
                    'receiving_details_id'  => $detail->id,
                    'product_packaging_id'  => $detail->product_packaging_id,
                    'qty_qc'                => $qtyQc,
                    'status_qc'             => $statusOk ? 1 : 0,
                    'is_sellable'           => $isSellable,
                    'is_approved'           => 0,
                ]);

                return $this->response(200, [
                    'notification' => [
                        'alert'   => 'notify',
                        'type'    => 'success',
                        'content' => 'QC berhasil disimpan, menunggu approval jika saleable.'
                    ],
                    'redirect_to' => url()->previous()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function approveQc(Request $req, $id)
    {
        try {
            $qcLog = ReceivingQcLogs::findOrFail($id);

            if ($qcLog->is_approved) {
                return response()->json([
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Validasi Gagal',
                        'content' => 'Log QC ini sudah pernah di-approve.'
                    ]
                ], 400);
            }

            if ($qcLog->status_qc != 1 || !$qcLog->is_sellable) {
                return response()->json([
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Validasi Gagal',
                        'content' => 'Log QC ini tidak valid untuk di-approve.'
                    ]
                ], 400);
            }

            $detail = $qcLog->detail;
            $receiving = $detail->receiving;
            $qtyQc = $qcLog->qty_qc;

            DB::transaction(function () use ($detail, $receiving, $qcLog, $qtyQc) {
                $sisaToCut = $qtyQc;

                $summaries = PurchaseOrderSummary::where([
                    ['product_packaging_id', $detail->product_packaging_id],
                    ['status', 2]
                ])->orderBy('id')->lockForUpdate()->get();

                foreach ($summaries as $sum) {
                    if ($sisaToCut <= 0) break;

                    $ambil = min($sisaToCut, $sum->quantity);
                    $sum->quantity -= $ambil;
                    $sisaToCut -= $ambil;

                    if ($sum->quantity <= 0) {
                        $sum->status = 1;
                    }

                    $sum->save();
                }

                if ($sisaToCut > 0) {
                    return response()->json([
                        'notification' => [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Validasi Gagal',
                            'content' => 'Stok PO Summary tidak mencukupi untuk produk ini.'
                        ]
                    ], 400);
                }

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

                $minStock->quantity += $qtyQc;
                $minStock->save();

                StockMove::create([
                    'warehouse_id'         => $receiving->warehouse_id,
                    'product_packaging_id' => $detail->product_packaging_id,
                    'code_transaction'     => 'RI-' . $receiving->code,
                    'stock_in'             => $qtyQc,
                    'stock_out'            => 0,
                    'stock_balance'        => $minStock->quantity,
                    'created_by'           => Auth::id(),
                ]);

                // Set QC sudah diproses
                $qcLog->is_approved = 1;
                $qcLog->save();
            });

            return $this->response(200, [
                    'notification' => [
                        'alert'   => 'notify',
                        'type'    => 'success',
                        'content' => 'QC berhasil di-approve dan masuk ke stok.'
                    ],
                    'redirect_to' => url()->previous()
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function destroyQc(Request $request, $id)
    {
        if ($request->ajax()) {
            if (Auth::user()->is_superuser == 0) {
                if (empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda tidak punya akses untuk menghapus data QC.'
                    ], 403);
                }
            }

            $qcLog = ReceivingQcLogs::find($id);

            if (!$qcLog) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data QC tidak ditemukan.'
                ], 404);
            }

            if ($qcLog->is_sellable && $qcLog->is_approved) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat menghapus log QC yang sudah masuk ke stok.'
                ], 400);
            }

            if ($qcLog->delete()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Log QC berhasil dihapus.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus log QC.'
            ], 500);
        }

        return abort(403, 'Invalid Request');
    }
}