<?php

namespace App\Services;

use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrderKontrakPivot;
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

        if ($step == 1) {
            $this->updateStep1($sales_order, $post);
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
     */
    protected function updateStep1($salesOrder, $post)
    {
        $salesOrder->type_transaction = trim(htmlentities($post["type_transaction"]));
        $salesOrder->brand_name = trim(htmlentities($post["brand_name"]));
        $salesOrder->note = trim(htmlentities($post["note"]));

        // Update kurs/IDR rate
        $idr_rate_clean = str_replace('.', '', $post["idr_rate"] ?? '0');
        $idr_rate_clean = str_replace(',', '.', $idr_rate_clean);
        $salesOrder->idr_rate = (float) $idr_rate_clean;

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
        $salesOrder->ekspedisi_id = (empty($post["ekspedisi_id"])) ? null : $post["ekspedisi_id"];
    }

    /**
     * Delete old SO items and kontrak pivots
     */
    protected function deleteOldItems($soId)
    {
        $search_so_items = SalesOrderItem::where('so_id', $soId)->get();
        if ($search_so_items->isNotEmpty()) {
            foreach ($search_so_items as $search_so_item) {
                $get_pivot_kontrak = SalesOrderKontrakPivot::where('so_item_id', $search_so_item->id)->get();
                foreach ($get_pivot_kontrak as $row) {
                    SalesOrderKontrakPivot::where('so_item_id', $row->so_item_id)->delete();
                }
            }
        }

        SalesOrderItem::where('so_id', $soId)->update(['status' => 0]);
        SalesOrderItem::where('so_id', $soId)->delete();
    }

    /**
     * Insert new SO items
     */
    protected function insertItems($salesOrder, $post)
    {
        $listItem = [];
        for ($i = 0; $i < sizeof($post["sku"]); $i++) {
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
