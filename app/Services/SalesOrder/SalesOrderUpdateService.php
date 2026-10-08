<?php

namespace App\Services\SalesOrder;

use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrderKontrakPivot;
use App\Entities\Master\ProductPack;
use Auth;
use DB;

class SalesOrderUpdateService
{
    /**
     * Update SO header and items (step 1 or 2)
     * Returns ['success' => bool, 'message' => string]
     */
    public function update($request)
    {
        $post = $request->all();
        $step = $post["step"];

        if (empty($post["id"])) {
            return ['success' => false, 'message' => 'ID Sales Order tidak boleh kosong'];
        }

        $sales_order = SalesOrder::find($post["id"]);
        if (!$sales_order) {
            return ['success' => false, 'message' => 'Sales Order tidak ditemukan'];
        }

        // Build customer/warehouse data
        $customer = [];
        $gudang = [];
        if (!empty($post["customer_id"])) {
            $customer["id"] = empty($post["customer_id"]) ? null : $post["customer_id"];
            $customer["so_for"] = 1;
        } else {
            $gudang["id"] = empty($post["destination_warehouse_id"]) ? null : $post["destination_warehouse_id"];
            $customer["so_for"] = 2;
        }

        // LOG SEMENTARA (debug kurs 261001-03) — hapus setelah investigasi selesai.
        \Log::info('SO-UPDATE-DBG input', ['id' => $post['id'] ?? null, 'step' => $step ?? null, 'idr_rate_raw' => $post['idr_rate'] ?? null]);

        if ($step == 1) {
            $stepError = $this->updateStep1($sales_order, $post);
            if ($stepError) {
                return ['success' => false, 'message' => $stepError];
            }
        } else if ($step == 2) {
            $this->updateStep2($sales_order, $post, $gudang);
        }

        if (!$sales_order->save()) {
            return ['success' => false, 'message' => 'Gagal menyimpan Sales Order'];
        }

        // Delete old items and kontrak pivots
        $this->deleteOldItems($sales_order->id);

        // Insert new items
        if (isset($post["sku"]) && sizeof($post["sku"]) > 0) {
            $itemResult = $this->insertItems($sales_order, $post);
            if (!$itemResult['success']) {
                return $itemResult;
            }
        }

        return ['success' => true, 'message' => 'Sales Order Berhasil Diubah'];
    }

    /**
     * Update step 1 fields
     * Returns null jika OK, atau string pesan error jika validasi gagal.
     */
    protected function updateStep1($salesOrder, $post)
    {
        $salesOrder->type_transaction = trim(htmlentities($post["type_transaction"]));
        $salesOrder->brand_name = trim(htmlentities($post["brand_name"]));
        $salesOrder->note = trim(htmlentities($post["note"]));

        // Update kurs/IDR rate (tahan format ID "17.975,00" maupun EN "17,975.00").
        $idr_rate = \App\Helper\CustomHelper::parseKurs($post["idr_rate"] ?? 0);
        // LOG SEMENTARA (debug kurs 261001-03) — hapus setelah investigasi selesai.
        \Log::info('SO-UPDATE-DBG parsed', ['idr_rate_raw' => $post["idr_rate"] ?? null, 'idr_rate_parsed' => $idr_rate]);
        // SO non-PPN wajib kurs realistis (mencegah 1000x kekecilan akibat salah format ribuan).
        if (($salesOrder->type_so ?? 'nonppn') !== 'ppn' && $idr_rate < 1000) {
            return 'Kurs tidak valid (' . e($post["idr_rate"] ?? '') . '). Gunakan angka penuh, cth: 18000.';
        }
        $salesOrder->idr_rate = $idr_rate;

        // Update indent
        if (isset($post["so_indent"])) {
            $salesOrder->so_indent = trim(htmlentities($post["so_indent"]));
        }

        // Update diskon global
        $disc_percent = trim(htmlentities($post["catatan"] ?? 0));
        $salesOrder->catatan = $disc_percent;
        $salesOrder->disc_percent = $disc_percent;
        $salesOrder->disc_usd = trim(htmlentities($post["global_disc_usd"] ?? 0));
        $salesOrder->disc_kemasan = trim(htmlentities($post["global_disc_kemasan"] ?? 0));
        $salesOrder->disc_idr = trim(htmlentities($post["global_disc_idr"] ?? 0));

        $salesOrder->updated_by = Auth::id();
        $salesOrder->status = 1;

        return null;
    }

    /**
     * Update step 2 fields
     */
    protected function updateStep2($salesOrder, $post, $gudang)
    {
        $salesOrder->origin_warehouse_id = trim(htmlentities($post["origin_warehouse_id"]));
        $salesOrder->destination_warehouse_id = $gudang["id"] ?? null;
        $salesOrder->type_transaction = trim(htmlentities($post["type_transaction"]));
        $salesOrder->updated_by = Auth::id();
        $salesOrder->status = 2;
        // Edit & submit ulang ke admin -> perbarui waktu submit.
        $salesOrder->submitted_at = date('Y-m-d H:i:s');
        $salesOrder->ekspedisi_id = (empty($post["ekspedisi_id"])) ? null : $post["ekspedisi_id"];
    }

    /**
     * Delete old SO items and kontrak pivots
     */
    protected function deleteOldItems($soId)
    {
        $search_so_items = \App\Entities\Penjualan\SalesOrderItem::where('so_id', $soId)->get();
        if ($search_so_items->isNotEmpty()) {
            foreach ($search_so_items as $search_so_item) {
                $get_pivot_kontrak = \App\Entities\Penjualan\SalesOrderKontrakPivot::where('so_item_id', $search_so_item->id)->get();
                foreach ($get_pivot_kontrak as $row) {
                    \App\Entities\Penjualan\SalesOrderKontrakPivot::where('so_item_id', $row->so_item_id)->delete();
                }
            }
        }

        \App\Entities\Penjualan\SalesOrderItem::where('so_id', $soId)->update(['status' => 0]);
        \App\Entities\Penjualan\SalesOrderItem::where('so_id', $soId)->delete();
    }

    /**
     * Insert new SO items
     */
    protected function insertItems($salesOrder, $post)
    {
        $listItem = [];
        $headerBrand = trim((string) ($salesOrder->brand_name ?? ''));
        $expectedPackagingId = isset($post['packaging_id']) && is_numeric($post['packaging_id'])
            ? (int) $post['packaging_id']
            : null;
        // Validasi ketat: kemasan wajib dipilih & harus aktif (mencegah bypass + data revisi yatim).
        if (empty($expectedPackagingId)) {
            return ['success' => false, 'message' => 'Kemasan wajib dipilih sebelum menyimpan SO.'];
        }
        $masterPackaging = \App\Entities\Master\Packaging::where('id', $expectedPackagingId)
            ->where('status', \App\Entities\Master\Packaging::STATUS['ACTIVE'])
            ->first();
        if (!$masterPackaging) {
            return ['success' => false, 'message' => 'Kemasan yang dipilih tidak valid / tidak aktif.'];
        }
        // Penanda revisi: jika SO hasil revisi DO (count_rev=1), kemasan boleh diubah
        // HANYA sebelum tutup ulang — item lama wajib sudah disesuaikan (dicek per-baris di bawah).
        $isRevision = ((int) ($salesOrder->count_rev ?? 0) === 1);
        for ($i = 0; $i < sizeof($post["sku"]); $i++) {
            // Jaring pengaman: item harus milik brand header (frontend bisa di-bypass)
            $pack = ProductPack::with('product')->where('id', $post["sku"][$i])->first();
            if (!$pack) {
                return ['success' => false, 'message' => 'Produk pada baris ' . ($i + 1) . ' tidak ditemukan.'];
            }
            $itemBrand = trim((string) ($pack->product->brand_name ?? ''));
            if ($headerBrand === '' || strcasecmp($itemBrand, $headerBrand) !== 0) {
                $code = $pack->code ?? $post["sku"][$i];
                return ['success' => false, 'message' => 'Item <b>' . e($code) . '</b> milik brand "' . e($itemBrand) . '", tidak sesuai dengan brand SO "' . e($headerBrand) . '". Hapus baris tersebut atau kembalikan Brand.'];
            }

            // Jaring pengaman: item non-kontrak harus ikut kemasan awal SO
            // (frontend sudah difilter, ini mencegah bypass via devtools).
            // Berlaku juga untuk SO revisi (count_rev=1): baris lama yang masih
            // kemasan lama wajib dihapus dulu, tidak bisa campur.
            $isKontrak = isset($post['so_kontrak_value'][$i]) && (string) $post['so_kontrak_value'][$i] === '1';
            if (!$isKontrak) {
                $submittedPackagingId = isset($post['packaging'][$i]) && is_numeric($post['packaging'][$i])
                    ? (int) $post['packaging'][$i]
                    : null;
                // Cek ganda: kiriman form + master ProductPack aktual.
                $actualPackagingId = isset($pack->packaging_id) ? (int) $pack->packaging_id : null;
                if ($submittedPackagingId !== $expectedPackagingId || $actualPackagingId !== $expectedPackagingId) {
                    $code = $pack->code ?? $post["sku"][$i];
                    $extra = $isRevision ? ' (SO revisi: hapus baris kemasan lama dulu)' : '';
                    return ['success' => false, 'message' => 'Item <b>' . e($code) . '</b> kemasannya tidak sesuai dengan kemasan SO (' . e($masterPackaging->pack_name) . ')' . $extra . '. Hapus baris tersebut dan pilih ulang produk.'];
                }
            }

            $duplicate_product = [];
            $duplicate = false;
            $listItem[] = [
                'sku' => $post["sku"][$i],
                'free_product' => $post["free_product"][$i] ?? 0,
            ];

            foreach ($listItem as $row => $value) {
                if (in_array($value, $duplicate_product)) {
                    $duplicate = true;
                    break;
                } else {
                    array_push($duplicate_product, $value);
                }
            }

            if ($duplicate) {
                return ['success' => false, 'message' => 'Item sudah ada di dalam list. Silahkan gabungkan Qty-nya.'];
            }

            // Validasi angka item (mencegah qty minus/nol dan harga/diskon negatif).
            $qtyItem = $post["qty"][$i] ?? null;
            $priceItem = $post["price"][$i] ?? null;
            $discItem = $post["disc"][$i] ?? 0;
            if (!is_numeric($qtyItem) || (float) $qtyItem <= 0) {
                return ['success' => false, 'message' => 'Qty baris ' . ($i + 1) . ' wajib angka lebih dari 0.'];
            }
            if (!is_numeric($priceItem) || (float) $priceItem < 0 || !is_numeric($discItem) || (float) $discItem < 0) {
                return ['success' => false, 'message' => 'Price/Disc baris ' . ($i + 1) . ' tidak boleh minus.'];
            }

            $insertDetail = new SalesOrderItem;
            $insertDetail->so_id = $salesOrder->id;
            $insertDetail->product_packaging_id = $post["sku"][$i];
            $insertDetail->price = $post["price"][$i];
            $insertDetail->qty = $post["qty"][$i];
            $insertDetail->disc_usd = $post["disc"][$i];
            $insertDetail->packaging_id = $post["packaging"][$i];
            $insertDetail->kontrak = $post["so_kontrak_value"][$i];
            $insertDetail->free_product = $post["free_product"][$i] ?? 0;
            $insertDetail->created_by = Auth::id();
            $insertDetail->save();
        }

        return ['success' => true, 'message' => ''];
    }

    /**
     * Update single SO item
     * Returns ['success' => bool, 'message' => string]
     */
    public function updateItem($request)
    {
        $post = $request->all();

        if (empty($post["id"])) {
            return ['success' => false, 'message' => 'ID item so tidak boleh kosong'];
        }
        if (empty($post["product_id"])) {
            return ['success' => false, 'message' => 'Product wajib dipilih'];
        }
        if (empty($post["qty"])) {
            return ['success' => false, 'message' => 'Quantity tidak boleh kosong'];
        }
        if (empty($post["packaging"])) {
            return ['success' => false, 'message' => 'Packaging tidak boleh kosong'];
        }

        $result = SalesOrderItem::where('id', $post["id"])->first();
        $get_so_item = SalesOrderItem::where('id', '!=', $post["id"])
            ->where('so_id', $result->so_id)
            ->where('product_id', $post["product_id"])
            ->where('packaging', $post["packaging"])
            ->first();

        if ($get_so_item) {
            return ['success' => false, 'message' => 'Item sudah ada'];
        }

        $data = [
            'product_id' => trim(htmlentities($post["product_id"])),
            'qty' => trim(htmlentities($post["qty"])),
            'packaging' => trim(htmlentities($post["packaging"])),
            'updated_by' => Auth::id(),
        ];

        $update = SalesOrderItem::where('id', $post["id"])->update($data);

        if ($update) {
            return ['success' => true, 'message' => 'Item Berhasil Diubah dan Ditambahkan ke SO'];
        }

        return ['success' => false, 'message' => 'Gagal mengubah item'];
    }
}