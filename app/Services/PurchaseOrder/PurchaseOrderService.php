<?php

namespace App\Services\PurchaseOrder;

use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Gudang\PurchaseOrderSummary;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Packaging;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Warehouse;
use App\Helper\LogActivity;
use App\Repositories\CodeRepo;
use Carbon\Carbon;
use DB;
use Validator;

/**
 * Logika bisnis Purchase Order (header + workflow).
 *
 * Controller hanya mengurus HTTP (request/response + hak akses),
 * semua validasi, query, dan transaksi database terpusat di sini.
 */
class PurchaseOrderService
{
    /**
     * Data dropdown untuk form index/create/edit.
     */
    public function formData()
    {
        return [
            'warehouse' => Warehouse::get(),
            'brands' => BrandLokal::where('status', BrandLokal::STATUS['ACTIVE'])->orderBy('brand_name')->get(),
            'sub_type' => PurchaseOrder::SUB_TYPE,
        ];
    }

    /**
     * Buat PO baru (status DRAFT).
     *
     * @return array [status => ok|validation, errors => [], po => PurchaseOrder|null]
     */
    public function createPo(array $input, $user_id)
    {
        $validator = Validator::make($input, [
            'code' => 'required|string|unique:purchase_order,code',
            'warehouse' => 'required|integer',
            'brand_lokal_id' => 'required|integer|exists:master_brand_lokal,id',
            'etd'  =>  'required|date',
        ]);

        if ($validator->fails()) {
            return ["status" => "validation", "errors" => $validator->errors()->all(), "po" => null];
        }

        $purchase_order = new PurchaseOrder;
        $purchase_order->code = $input["code"];
        $purchase_order->warehouse_id = $input["warehouse"];
        $purchase_order->brand_lokal_id = $input["brand_lokal_id"];
        $purchase_order->type = 1;
        $purchase_order->sub_type = isset($input["sub_type"]) ? $input["sub_type"] : 0;
        $purchase_order->etd = $input["etd"];
        $purchase_order->note = isset($input["note"]) ? $input["note"] : null;
        $purchase_order->created_by = $user_id;
        $purchase_order->status = PurchaseOrder::STATUS['DRAFT'];

        if ($purchase_order->save()) {
            LogActivity::addToLog('Created a new PO: ' . $purchase_order->code);

            return ["status" => "ok", "errors" => [], "po" => $purchase_order];
        }

        return ["status" => "validation", "errors" => ["Gagal menyimpan PO"], "po" => null];
    }

    /**
     * Update header PO.
     *
     * @return array [status => ok|validation|not_found, errors => [], po => PurchaseOrder|null]
     */
    public function updatePo($id, array $input)
    {
        $purchase_order = PurchaseOrder::find($id);

        if ($purchase_order == null) {
            return ["status" => "not_found", "errors" => [], "po" => null];
        }

        $validator = Validator::make($input, [
            'code' => 'required|string|unique:purchase_order,code,' . $purchase_order->id,
            'warehouse' => 'required|integer',
            'brand_lokal_id' => 'required|integer|exists:master_brand_lokal,id',
            'etd'  =>  'required|date',
        ]);

        if ($validator->fails()) {
            return ["status" => "validation", "errors" => $validator->errors()->all(), "po" => null];
        }

        $purchase_order->code = $input["code"];
        $purchase_order->warehouse_id = $input["warehouse"];
        $purchase_order->brand_lokal_id = $input["brand_lokal_id"];
        $purchase_order->type = isset($input["type"]) ? $input["type"] : $purchase_order->type;
        $purchase_order->etd = $input["etd"];
        $purchase_order->note = isset($input["note"]) ? $input["note"] : $purchase_order->note;

        if ($purchase_order->save()) {
            LogActivity::addToLog('Updated PO: ' . $purchase_order->code);

            return ["status" => "ok", "errors" => [], "po" => $purchase_order];
        }

        return ["status" => "validation", "errors" => ["Gagal menyimpan PO"], "po" => null];
    }

    /**
     * Pindah status sederhana: publish (ACTIVE), unpublish (DRAFT), destroy (DELETED).
     *
     * @return array [status => ok|not_found, po => PurchaseOrder|null]
     */
    public function changeStatus($id, $status, $user_id)
    {
        $purchase_order = PurchaseOrder::find($id);

        if ($purchase_order == null) {
            return ["status" => "not_found", "po" => null];
        }

        $purchase_order->updated_by = $user_id;
        $purchase_order->status = $status;

        if ($purchase_order->save()) {
            return ["status" => "ok", "po" => $purchase_order];
        }

        return ["status" => "not_found", "po" => null];
    }

    /**
     * Hapus PO (soft delete via status DELETED).
     *
     * @return array [status => ok|not_found, po => PurchaseOrder|null]
     */
    public function deletePo($id, $user_id)
    {
        $result = $this->changeStatus($id, PurchaseOrder::STATUS['DELETED'], $user_id);

        if ($result["status"] === "ok") {
            LogActivity::addToLog('Deleted PO: ' . $result["po"]->code);
        }

        return $result;
    }

    /**
     * Save / ACC dari halaman step (save_modify).
     *
     * @return array [status => ok|not_found|error, message => string]
     */
    public function saveModify($id, $save_type, $user_id)
    {
        $purchase_order = PurchaseOrder::find($id);

        if ($purchase_order == null) {
            return ["status" => "not_found", "message" => ""];
        }

        DB::beginTransaction();
        try {
            if ($save_type == 'save') {
                $purchase_order->edit_counter += 1;
            } else {
                $purchase_order->acc_by = $user_id;
                $purchase_order->acc_at = Carbon::now()->toDateTimeString();
            }

            $purchase_order->status = $save_type == 'save' ? PurchaseOrder::STATUS['ACTIVE'] : PurchaseOrder::STATUS['ACC'];
            $purchase_order->save();

            DB::commit();

            return ["status" => "ok", "message" => ""];
        } catch (\Exception $e) {
            DB::rollback();

            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    /**
     * ACC PO + auto generate SPK bila memenuhi syarat.
     *
     * @return array [status => ok|not_found|error, message => string, spk => PurchaseOrder|null]
     */
    public function accPo($id, $user_id)
    {
        $purchase_order = PurchaseOrder::with('purchase_order_detail')->find($id);

        if ($purchase_order == null) {
            return ["status" => "not_found", "message" => "", "spk" => null];
        }

        DB::beginTransaction();
        try {
            $purchase_order->acc_by = $user_id;
            $purchase_order->acc_at = now();
            $purchase_order->status = PurchaseOrder::STATUS['ACC'];
            $purchase_order->save();

            // AUTO GENERATE SPK jika memenuhi syarat
            $spk = $this->generateSpkFromPo($purchase_order, $user_id);

            DB::commit();

            LogActivity::addToLog('ACC PO: ' . $purchase_order->code);

            if ($spk) {
                LogActivity::addToLog("Auto Generate SPK {$spk->code}");
            }

            return ["status" => "ok", "message" => "", "spk" => $spk];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return ["status" => "error", "message" => "Gagal ACC dan generate SPK", "spk" => null];
        }
    }

    /**
     * Batalkan ACC: kembali ke DRAFT + hapus SPK turunannya bila masih ACC.
     *
     * @return array [status => ok|error, message => string]
     */
    public function cancelAcc($id, $user_id)
    {
        DB::beginTransaction();
        try {
            $po = PurchaseOrder::findOrFail($id);

            $po->update([
                'acc_at' => null,
                'acc_by' => null,
                'status' => PurchaseOrder::STATUS['DRAFT'],
                'updated_by' => $user_id,
                'count_send_spk' => 0
            ]);

            $spk = PurchaseOrder::where('ref_po_id', $po->id)->first();

            if ($spk) {
                if ($spk->status == PurchaseOrder::STATUS['ACC']) {
                    PurchaseOrderDetail::where('po_id', $spk->id)->delete();
                    $spk->delete();
                }
            }

            DB::commit();

            return ["status" => "ok", "message" => ""];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    /**
     * Kirim PO (SENT) + bentuk summary per produk.
     *
     * @return array [status => ok|not_found]
     */
    public function sendPo($id, $user_id)
    {
        $purchase_order = PurchaseOrder::find($id);

        if ($purchase_order === null) {
            return ["status" => "not_found"];
        }

        $purchase_order->updated_by = $user_id;
        $purchase_order->status = PurchaseOrder::STATUS['SENT'];

        if ($purchase_order->save()) {
            $purchase_order_details = PurchaseOrderDetail::where('po_id', $id)->get();
            foreach ($purchase_order_details as $detail) {
                $summary = new PurchaseOrderSummary;
                $summary->po_id = $id;
                $summary->product_packaging_id = $detail->product_packaging_id;
                $summary->quantity = $detail->quantity;
                $summary->status = PurchaseOrderSummary::STATUS['UNDONE'];
                $summary->save();
            }

            return ["status" => "ok"];
        }

        return ["status" => "not_found"];
    }

    /**
     * Batalkan pengiriman PO (kembali ke ACC) + hapus summary UNDONE.
     *
     * @return array [status => ok|not_found|has_receiving|error, message => string]
     */
    public function cancelSend($id, $user_id)
    {
        $purchase_order = PurchaseOrder::find($id);

        if ($purchase_order === null) {
            return ["status" => "not_found", "message" => ""];
        }

        if ($purchase_order->receiving_detail()->exists()) {
            return ["status" => "has_receiving", "message" => "PO tidak dapat dibatalkan karena sudah ada penerimaan yang terkait."];
        }

        DB::beginTransaction();
        try {
            $purchase_order->updated_by = $user_id;
            $purchase_order->status = PurchaseOrder::STATUS['ACC'];
            $purchase_order->save();

            PurchaseOrderSummary::where([
                ['po_id', $purchase_order->id],
                ['status', PurchaseOrderSummary::STATUS['UNDONE']]
            ])->delete();

            DB::commit();

            return ["status" => "ok", "message" => "PO berhasil dibatalkan dari status SENT"];
        } catch (\Exception $e) {
            DB::rollBack();

            return ["status" => "error", "message" => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    /**
     * Data halaman summary (outstanding produk PO terkirim).
     */
    public function summaryRows()
    {
        return DB::table('purchase_order_summary')
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
            ->where('purchase_order.type', 1)
            ->groupBy(
                'master_products_packaging.id',
                'master_products_packaging.name',
                'master_products_packaging.code',
                'master_packaging.pack_name'
            )
            ->orderBy('master_products_packaging.name', 'ASC')
            ->get();
    }

    /**
     * Pencarian SKU untuk select2.
     */
    public function searchSku($q)
    {
        return ProductPack::where('master_products_packaging.name', 'LIKE', '%' . $q . '%')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->where('master_products_packaging.status', ProductPack::STATUS['ACTIVE'])
            ->select(
                'master_products_packaging.id',
                DB::raw("CONCAT(master_products_packaging.code, ' - ', master_products_packaging.name, ' / ', master_packaging.pack_name) as text")
            )
            ->get();
    }

    /**
     * Pencarian kemasan untuk select2.
     */
    public function searchKemasan($q)
    {
        return Packaging::where('pack_name', 'LIKE', '%' . $q . '%')
            ->where('status', Packaging::STATUS['ACTIVE'])
            ->get(['id', 'pack_name as text']);
    }

    /**
     * Data cetak PO: detail diurut kemasan lalu nama produk + grand total qty.
     *
     * @return array [po, rows, total]
     */
    public function printData($id)
    {
        $po = PurchaseOrder::with([
                'purchase_order_detail.product_pack.packaging',
                'warehouse',
                'brandLokal',
            ])->findOrFail($id);

        $rows = $po->purchase_order_detail->sortBy(function ($d) {
            $pack = ($d->product_pack && $d->product_pack->packaging)
                ? $d->product_pack->packaging->pack_name
                : '';
            $name = $d->product_pack ? $d->product_pack->name : '';

            return $pack . '|' . $name;
        })->values();

        return [
            'po' => $po,
            'rows' => $rows,
            'total' => (float) $po->purchase_order_detail->sum('quantity'),
        ];
    }

    /**
     * Auto generate SPK INDUSTRY dari PO yang di-ACC (internal, tanpa response).
     */
    private function generateSpkFromPo(PurchaseOrder $po, $user_id)
    {
        // Validasi internal (tanpa response)
        if ($po->type != PurchaseOrder::TYPE['PO']) return null;
        if ($po->sub_type != PurchaseOrder::SUB_TYPE['INDUSTRI']) return null;
        if ($po->purchase_order_detail->isEmpty()) return null;
        if (PurchaseOrder::where('ref_po_id', $po->id)->exists()) return null;

        $spk = PurchaseOrder::create([
            'code'         => CodeRepo::generatePurchaseOrderSPK(),
            'warehouse_id' => $po->warehouse_id,
            'type'         => PurchaseOrder::TYPE['SPK'],
            'etd'          => $po->etd,
            'note'         => 'Generate from PO ' . $po->code,
            'ref_po_id'    => $po->id,
            'created_by'   => $user_id,
            'status'       => PurchaseOrder::STATUS['ACC'],
        ]);

        foreach ($po->purchase_order_detail as $detail) {
            PurchaseOrderDetail::create([
                'po_id'                => $spk->id,
                'brand_lokal_id'       => $detail->brand_lokal_id,
                'product_packaging_id' => $detail->product_packaging_id,
                'quantity'             => $detail->quantity,
                'packaging_id'         => $detail->packaging_id,
                'note_produksi'        => $detail->note_produksi,
                'note_repack'          => $detail->note_repack,
                'created_by'           => $user_id,
            ]);
        }

        $po->update([
            'count_send_spk' => 1,
            'updated_by'     => $user_id,
        ]);

        return $spk;
    }
}
