<?php

namespace App\Services\PurchaseOrder;

use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Packaging;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use DB;
use Validator;

/**
 * Logika bisnis Purchase Order Detail.
 *
 * Controller hanya mengurus HTTP (request/response + hak akses),
 * semua query dan transaksi database terpusat di sini.
 */
class PurchaseOrderDetailService
{
    /**
     * Definisi tab multipage: 1 PO = 1 brand, beda tab beda kemasan (tampilan saja).
     * ID kemasan di-resolve by pack_name saat runtime agar aman beda database.
     *
     * @return array [pack_tabs, fixed_pack_ids, other_packs, other_pack_ids]
     */
    public function packTabs()
    {
        $tab_defs = [
            ['key' => '100gr', 'label' => '100gr', 'pack_names' => ['0.1 kg Alu']],
            ['key' => '500gr', 'label' => '500gr', 'pack_names' => ['0.5 kg Alu']],
            ['key' => '5kg',   'label' => '5kg',   'pack_names' => ['5 kg Alu', '5 Kg Jirigen']],
            ['key' => '25kg',  'label' => '25kg',  'pack_names' => ['25 Kg Drum', '25 Kg Jirigen']],
        ];

        $pack_tabs = [];
        $fixed_pack_ids = [];
        foreach ($tab_defs as $tab) {
            $packs = Packaging::whereIn('pack_name', $tab['pack_names'])->get(['id', 'pack_name']);
            $ids = $packs->pluck('id')->all();
            $fixed_pack_ids = array_merge($fixed_pack_ids, $ids);
            $pack_tabs[] = [
                'key' => $tab['key'],
                'label' => $tab['label'],
                'pack_ids' => $ids,
                'pack_names' => $packs->pluck('pack_name')->all(),
            ];
        }

        // Kemasan di luar 4 tab fix (mis. 0.25 kg, 1 kg) — dicadangkan agar tidak ada data hilang.
        $other_packs = Packaging::where('status', Packaging::STATUS['ACTIVE'])
            ->whereNotIn('id', $fixed_pack_ids ?: [0])
            ->orderBy('pack_name')->get(['id', 'pack_name']);

        return [
            'pack_tabs' => $pack_tabs,
            'fixed_pack_ids' => $fixed_pack_ids,
            'other_packs' => $other_packs,
            'other_pack_ids' => $other_packs->pluck('id')->all(),
        ];
    }

    /**
     * Daftar detail 1 PO untuk hot-reload tabel per tab (detail_json).
     */
    public function listDetails($purchase_id, $route_prefix = 'superuser.gudang.purchase_order.detail')
    {
        $details = PurchaseOrderDetail::with(['product_pack.packaging'])
            ->where('po_id', $purchase_id)
            ->orderBy('id')
            ->get();

        return $details->map(function ($d) use ($route_prefix) {
            $pack = $d->product_pack;
            return [
                'id' => $d->id,
                'code' => $pack->code ?? '-',
                'name' => $pack->name ?? '-',
                'quantity' => $d->quantity,
                'product_packaging_id' => $d->product_packaging_id,
                'packaging_id' => $d->packaging_id,
                'pack_name' => ($pack && $pack->packaging) ? $pack->packaging->pack_name : '-',
                'note_produksi' => $d->note_produksi,
                'note_repack' => $d->note_repack,
                'edit_url' => route($route_prefix . '.edit', [$d->po_id, $d->id]),
                'update_url' => route($route_prefix . '.update', [$d->po_id, $d->id]),
                'destroy_url' => route($route_prefix . '.destroy', [$d->po_id, $d->id]),
            ];
        });
    }

    /**
     * Daftar produk by brand + filter kemasan, lengkap dengan kemasan
     * tiap produk langsung dari relasi master_products_packaging.
     */
    public function getProducts($brand_name, $packaging_ids)
    {
        $pack_filter = $this->normalizeIds($packaging_ids);

        $table = Product::where(function ($query2) use ($brand_name) {
                if (!empty($brand_name)) {
                    $query2->where('brand_name', $brand_name);
                }
            })
            ->leftJoin('master_warehouses', 'master_products.default_warehouse_id', '=', 'master_warehouses.id')
            ->leftJoin('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->when(!empty($pack_filter), function ($query) use ($pack_filter) {
                $query->whereIn('master_products_packaging.packaging_id', $pack_filter);
            })
            ->selectRaw(
                'master_products.id as id,
                master_products.name as productName,
                master_products.code as productCode,
                master_warehouses.name as warehouseName'
            )
            ->groupBy('master_products.id', 'master_products.name', 'master_products.code', 'master_warehouses.name')
            ->get();

        $pack_map = [];
        if ($table->count() > 0) {
            $pack_rows = ProductPack::whereIn('product_id', $table->pluck('id')->all())
                ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                ->when(!empty($pack_filter), function ($query) use ($pack_filter) {
                    $query->whereIn('master_products_packaging.packaging_id', $pack_filter);
                })
                ->select('master_products_packaging.product_id', 'master_packaging.id as packaging_id', 'master_packaging.pack_name')
                ->orderBy('master_packaging.pack_name')
                ->get();
            foreach ($pack_rows as $pr) {
                $pack_map[$pr->product_id][] = ['id' => $pr->packaging_id, 'name' => $pr->pack_name];
            }
        }
        $table->each(function ($row) use ($pack_map) {
            $row->packagings = $pack_map[$row->id] ?? [];
        });

        return $table;
    }

    /**
     * Daftar kemasan yang dimiliki 1 produk.
     */
    public function getPackagings($product_id)
    {
        return ProductPack::where(function ($query2) use ($product_id) {
                if (!empty($product_id)) {
                    $query2->where('product_id', $product_id);
                }
            })
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->selectRaw('master_packaging.id, master_packaging.pack_name')
            ->get();
    }

    /**
     * Simpan banyak baris detail sekaligus (dari staging per tab).
     *
     * @return array [IsError, Message, http]
     */
    public function storeDetails($purchase_id, array $input, $user_id)
    {
        if (empty($input["merek"])) {
            return ["IsError" => true, "Message" => "Merek wajib dipilih", "http" => 200];
        }

        if (empty($input["product_packaging_id"])) {
            return ["IsError" => true, "Message" => "Product wajib dipilih", "http" => 200];
        }

        if (empty($input["qty"])) {
            return ["IsError" => true, "Message" => "Qty wajib dipilih", "http" => 200];
        }

        DB::beginTransaction();
        try {
            $brand_id = BrandLokal::where('brand_name', $input["merek"])->pluck('id')->first();

            if (sizeof($input["product_packaging_id"]) > 0) {
                for ($i = 0; $i < sizeof($input["product_packaging_id"]); $i++) {
                    if (empty($input["product_packaging_id"][$i])) continue;

                    $po_detail = new PurchaseOrderDetail;
                    $po_detail->po_id = $purchase_id;
                    $po_detail->brand_lokal_id = $brand_id;
                    $po_detail->product_packaging_id = trim(htmlentities(implode("-", [$input["product_packaging_id"][$i], $input["packaging_id"][$i]])));
                    $po_detail->quantity = trim(htmlentities($input["qty"][$i]));
                    $po_detail->packaging_id = trim(htmlentities($input["packaging_id"][$i]));
                    $po_detail->note_produksi = trim(htmlentities($input["note_produksi"][$i])) ?? null;
                    $po_detail->note_repack = trim(htmlentities($input["note_repack"][$i])) ?? null;
                    $po_detail->created_by = $user_id;
                    $po_detail->save();
                }
            }

            DB::commit();

            return ["IsError" => false, "Message" => "Product Berhasil Ditambahkan", "http" => 200];
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('PO detail store failed', ['po_id' => $purchase_id, 'error' => $e->getMessage()]);

            return ["IsError" => true, "Message" => $e->getMessage(), "http" => 400];
        }
    }

    /**
     * Update 1 baris detail (dipakai edit inline di step).
     *
     * @return array [status => ok|validation|not_found, errors => []]
     */
    public function updateDetail($id, $detail_id, array $input)
    {
        $validator = Validator::make($input, [
            'product_packaging_id' => 'required',
            'quantity' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return ["status" => "validation", "errors" => $validator->errors()->all()];
        }

        $purchase_order = PurchaseOrder::find($id);
        $purchase_order_detail = PurchaseOrderDetail::find($detail_id);

        if ($purchase_order == null || $purchase_order_detail == null) {
            return ["status" => "not_found", "errors" => []];
        }

        $purchase_order_detail->product_packaging_id = $input["product_packaging_id"] ?? $purchase_order_detail->product_packaging_id;
        $purchase_order_detail->quantity = $input["quantity"] ?? $purchase_order_detail->quantity;
        $purchase_order_detail->packaging_id = $input["packaging_id"] ?? $purchase_order_detail->packaging_id;
        $purchase_order_detail->note_produksi = $input["note_produksi"] ?? $purchase_order_detail->note_produksi;
        $purchase_order_detail->note_repack = $input["note_repack"] ?? $purchase_order_detail->note_repack;

        if ($purchase_order_detail->save()) {
            return ["status" => "ok", "errors" => []];
        }

        return ["status" => "validation", "errors" => ["Gagal menyimpan perubahan"]];
    }

    /**
     * Hapus 1 baris detail.
     *
     * @return array [status => ok|not_found]
     */
    public function deleteDetail($id, $detail_id)
    {
        $purchase_order = PurchaseOrder::find($id);
        $purchase_order_detail = PurchaseOrderDetail::find($detail_id);

        if ($purchase_order === null || $purchase_order_detail === null) {
            return ["status" => "not_found"];
        }

        if ($purchase_order_detail->delete()) {
            $purchase_order->save();

            return ["status" => "ok"];
        }

        return ["status" => "not_found"];
    }

    /**
     * Normalisasi input packaging_id (bisa tunggal, array, atau kosong) jadi array ID.
     */
    private function normalizeIds($packaging_ids)
    {
        if (empty($packaging_ids)) {
            return [];
        }

        $ids = is_array($packaging_ids) ? $packaging_ids : [$packaging_ids];

        return array_values(array_filter($ids, function ($v) {
            return $v !== '' && $v !== null;
        }));
    }
}
