<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Gudang\PurchaseOrderSummary;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Entities\Setting\UserMenu;

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

        $productPack = ProductPack::with('packaging')->findOrFail($request->id);

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
                'product'         => $productPack,
                'packaging'      => $productPack->packaging,
                'po_id'          => $firstPO,
                'quantity'       => $available      // sisa yang tersedia
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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['receiving'] = $receiving = Receiving::findOrFail($id);

        // semua summary status 2 (UNDONE)
        $summaries = PurchaseOrderSummary::where('status', 2)
                    ->with(['product.packaging'])
                    ->get() 
                    ->groupBy('product_packaging_id');

        $products = collect();

        foreach ($summaries as $pack_id => $grp) {

            $first          = $grp->first();
            $qty_remaining  = $grp->sum('quantity');   // sisa global (summary sudah dipotong ACC)

            // qty yang SUDAH dipilih di receiving ini (draft)
            $qty_current = ReceivingDetail::where([
                                ['receiving_id', $receiving->id],
                                ['product_packaging_id', $pack_id]
                        ])->sum('quantity_po');      // atau quantity_ri jika Anda pakai field lain

            $sisa_dropdown = $qty_remaining - $qty_current;

            if ($sisa_dropdown > 0) {
                $products->push((object)[
                    'product_pack_id' => $pack_id,
                    'code'            => $first->product->code,
                    'name'            => $first->product->name,
                    'pack_name'       => $first->product->packaging->pack_name,
                    'qty_remaining'   => $qty_remaining,
                    'qty_current'     => $qty_current,
                    'qty_available'   => $sisa_dropdown,   // tampil di UI
                ]);
            }
        }

        $data['products'] = $products;
        return view('superuser.gudang.receiving_detail.create', $data);
    }

    public function store(Request $request, $id)
    {
        if (!$request->ajax()) {
            return $this->response(400, ['notification' => ['alert'=>'block','type'=>'alert-danger','content'=>'Invalid']]);
        }

        /* 1. validasi dasar ─────────────────────────── */
        $available = PurchaseOrderSummary::where([
                        ['status', 2],
                        ['product_packaging_id', $request->product_pack_id]
                    ])->sum('quantity');

        $validator = Validator::make($request->all(), [
            'product_pack_id' => 'required|string|exists:master_products_packaging,id',
            'no_batch'        => 'nullable|string|max:50',
            'quantity'        => "required|numeric|min:1|max:$available",
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

        /* 2. ambil Receiving & simpan detail ────────── */
        $receiving = Receiving::find($id);
        if (!$receiving) abort(404);

        /* ambil PO id pertama yg masih UNDONE utk produk tsb */
        $poId = PurchaseOrderSummary::where([
                    ['status', 2],
                    ['product_packaging_id', $request->product_pack_id]
                ])->orderBy('id')->value('po_id');

        $detail                         = new ReceivingDetail;
        $detail->receiving_id           = $receiving->id;
        $detail->po_id                  = $poId;                    // opsional, bisa NULL
        $detail->product_packaging_id   = $request->product_pack_id;
        $detail->quantity_po            = $request->quantity;
        $detail->no_batch               = $request->no_batch;
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

    public function qty_qc(Request $request, $id)
    {
        if (!$request->ajax()) {
            return $this->response(400, ['notification'=>[
                'alert'=>'block','type'=>'alert-danger','content'=>'Invalid request'
            ]]);
        }

        $detail = ReceivingDetail::find($id);
        if (!$detail) abort(404);

        /* 1. validasi: angka >= 0 */
        $validator = Validator::make($request->all(), [
            'qc' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return $this->response(400, ['notification'=>[
                'alert'=>'block',
                'type'=>'alert-danger',
                'header'=>'Error',
                'content'=>$validator->errors()->all()
            ]]);
        }

        /* 2. logika reset / update */
        $qc = floatval($request->qc);

        if ($qc <= 0) {
            // • reset
            $detail->quantity_ri = null;   // atau 0 jika Anda lebih suka
            $detail->selisih     = null;
        } else {
            // • update normal
            $detail->quantity_ri = $qc;
            $detail->selisih     = $detail->quantity_po - $qc;
        }

        $detail->save();

        /* 3. kirim respon sukses */
        return $this->response(200, [
            'notification'=>[
                'alert'=>'notify',
                'type'=>'success',
                'content'=>'Quantity QC diperbarui'
            ],
            // redirect ke halaman receiving (pakai parent‑id!)
            'redirect_to' => route(
                'superuser.gudang.receiving.step',
                ['id'=>$detail->receiving_id]
            )
        ]);
    }
}
