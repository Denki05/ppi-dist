<?php

namespace App\Services\SalesOrder;

use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Finance\Invoicing;
use App\Entities\Penjualan\MutasiShowroom;
use App\Entities\Penjualan\MutasiShowroomDetail;
use App\Entities\Master\Company;
use App\Repositories\CodeRepo;
use App\Helper\CustomHelper;
use Auth;
use DB;
use Log;

class SalesOrderClosingService
{
    protected $stockService;

    public function __construct()
    {
        $this->stockService = new \App\Services\StockService();
    }

    /**
     * Clean currency value: remove dots, replace comma with dot
     */
    public function cleanCurrency($value)
    {
        if (empty($value)) return 0;
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return $value;
    }

    /**
     * Validate closing request fields
     */
    public function validateClosingRequest($request, &$errors)
    {
        if ($request->origin_warehouse_id == null) {
            $errors[] = 'Warehouse tidak boleh kosong!';
        }
        if ($request->rekening == null) {
            $errors[] = 'Rekening tidak boleh kosong!';
        }
        if (empty($request->grand_total_idr)) {
            $errors[] = 'Grand Total tidak boleh kosong!';
        }
        return empty($errors);
    }

    /**
     * Validasi silang total tutup SO dari request yang SAMA dengan yang dipakai
     * kalkulasi frontend (create_lanjutan): subtotal dari repeater x kurs,
     * nominal diskon dari persen, dan grand total akhir.
     *
     * Mencegah kasus diskon tersimpan tapi tidak dikurangkan dari grand total
     * (kasus Depo Aroma: kalkulasi JS tidak jalan penuh saat submit, backend
     * menyimpan grand kotor apa adanya). Return true jika konsisten.
     */
    public function validateClosingTotals($request, &$errors)
    {
        $repeater = $request->repeater;
        if (is_string($repeater)) {
            $repeater = json_decode($repeater, true);
        }
        if (empty($repeater) || !is_array($repeater)) {
            $errors[] = 'Item sales order kosong, kalkulasi tidak bisa divalidasi!';
            return false;
        }

        $kurs = (float) $this->cleanCurrency($request->idr_rate);
        if ($kurs <= 0) {
            $errors[] = 'Kurs tidak valid, kalkulasi tidak bisa divalidasi!';
            return false;
        }

        // Subtotal: mirror processItems & JS count_per_item (percent_disc = 0).
        $subtotal = 0;
        foreach ($repeater as $item) {
            $doQty = (float) ($item['do_qty'] ?? 0);
            if ($doQty <= 0) {
                continue;
            }
            $price = (float) ($item['price'] ?? 0);
            $usdDisc = (float) ($item['usd_disc'] ?? 0);
            $lineDiscUsd = $usdDisc * $doQty;
            $subtotal += round(($doQty * $price - $lineDiscUsd) * $kurs);
        }

        if ($subtotal <= 0) {
            $errors[] = 'Subtotal hasil kalkulasi nol, periksa qty/harga/kurs!';
            return false;
        }

        // Toleransi pembulatan antar langkah kalkulasi (kasus rusak selisih jutaan).
        $tol = max(1000, round($subtotal * 0.001));

        $d1pct = (float) ($request->disc_agen_percent ?? 0);
        $d2pct = (float) ($request->disc_kemasan_percent ?? 0);
        $discAgenReq = (float) $this->cleanCurrency($request->disc_agen_idr);
        $discKemasanReq = (float) $this->cleanCurrency($request->disc_kemasan_idr);
        $tambahan = (float) $this->cleanCurrency($request->disc_tambahan_idr);
        $voucher = (float) $this->cleanCurrency($request->voucher_idr);
        $delivery = (float) $this->cleanCurrency($request->delivery_cost_idr);
        $grandReq = (float) $this->cleanCurrency($request->grand_total_idr);

        // 1) Nominal diskon harus konsisten dengan persen x subtotal.
        $discAgenCalc = round($subtotal * ($d1pct / 100));
        $discKemasanCalc = round(($subtotal - $discAgenCalc) * ($d2pct / 100));
        if (abs($discAgenReq - $discAgenCalc) > $tol || abs($discKemasanReq - $discKemasanCalc) > $tol) {
            $errors[] = 'Nominal diskon tidak sesuai persen x subtotal (selisih Rp '
                . number_format(max(abs($discAgenReq - $discAgenCalc), abs($discKemasanReq - $discKemasanCalc)), 0, ',', '.')
                . '). Refresh halaman agar kalkulasi berjalan ulang, lalu submit kembali!';
            return false;
        }

        // 2) Grand total harus = subtotal - diskon - tambahan - voucher + ongkir.
        $grandCalc = $subtotal - $discAgenReq - $discKemasanReq - $tambahan - $voucher + $delivery;
        if (abs($grandReq - $grandCalc) > $tol) {
            $errors[] = 'Grand total tidak sesuai kalkulasi (selisih Rp '
                . number_format(abs($grandReq - $grandCalc), 0, ',', '.')
                . '). Diskon belum masuk ke total. Refresh halaman agar kalkulasi berjalan ulang, lalu submit kembali!';
            return false;
        }

        return empty($errors);
    }

    /**
     * Process stock reservation & release for SO items
     * Returns [stockLogs, mutasiItems]
     */
    public function processStockReservation($request, $salesOrder, $items, &$errors)
    {
        $stockLogs = [];
        $mutasiItems = [];

        foreach ($items as $key => $value) {
            $result = SalesOrderItem::where('id', $value["so_item_id"])->first();

            if (!$result) {
                continue;
            }

            // BASE PRODUCT PACKAGING ID (Hilangkan suffix misal _1, _2)
            $base_product_packaging_id = preg_replace('/_\d+$/', '', $result->product_packaging_id);

            // PENGECEKAN & RESERVE STOK (GABUNGAN FREE & NORMAL)
            $is_free_product = !empty($result->free_product) && (float)$result->free_product > 0;
            $so_qty = (float)$value["so_qty"];
            $do_qty = (float)$value["do_qty"];
            $rej_qty = $so_qty - $do_qty;

            // Tentukan jumlah yang akan di-reserve (Sekali eksekusi saja)
            $qtyToReserve = $is_free_product ? $so_qty : $do_qty;

            try {
                if ($qtyToReserve > 0) {
                    $this->stockService->reserveStock(
                        $request->origin_warehouse_id,
                        $base_product_packaging_id,
                        $qtyToReserve
                    );
                }

                // Catat data log
                $stockLogs[] = [
                    'do_id'                => $value['do_id'] ?? null,
                    'warehouse_id'         => $request->origin_warehouse_id,
                    'product_packaging_id' => $base_product_packaging_id,
                    'qty'                  => $qtyToReserve,
                    'status'               => 1,
                    'note'                 => 'Logs Stock',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];

                // Lepas antrean reject HANYA untuk barang berbayar
                if (!$is_free_product && $rej_qty > 0) {
                    $this->stockService->releaseReservedStock(
                        $request->origin_warehouse_id,
                        $base_product_packaging_id,
                        $rej_qty
                    );
                }
            } catch (\Exception $e) {
                throw new \Exception("Gagal reserve stock (" . $this->productLabel($result, $base_product_packaging_id) . "): " . $e->getMessage());
            }

            // Simpan data untuk mutasi jika ini free product
            if ($is_free_product) {
                $mutasiItems[] = [
                    'product_packaging_id' => $result->product_packaging_id,
                    'qty'  => $so_qty,
                    'note' => 'Free product otomatis dari SO ' . $salesOrder->code,
                ];
            }
        }

        return [$stockLogs, $mutasiItems];
    }

    /**
     * Label produk untuk notifikasi: "kode - nama".
     * Diambil dari relasi product_pack bila ada, fallback ke master via base ID,
     * terakhir fallback ke ID mentah.
     */
    protected function productLabel($soItem, $baseProductPackagingId = null)
    {
        try {
            if ($soItem && $soItem->relationLoaded('product_pack') === false) {
                $soItem->loadMissing('product_pack');
            }
            $pack = $soItem ? $soItem->product_pack : null;
            if ($pack && ($pack->code || $pack->name)) {
                return trim(($pack->code ?? '') . ($pack->name ? ' - ' . $pack->name : ''));
            }
        } catch (\Exception $e) {
        }

        foreach (array_filter([$baseProductPackagingId, $soItem->product_packaging_id ?? null]) as $candidateId) {
            try {
                $master = \App\Entities\Master\ProductPack::where('id', $candidateId)->first();
                if ($master && ($master->code || $master->name)) {
                    return trim(($master->code ?? $candidateId) . ($master->name ? ' - ' . $master->name : ''));
                }
            } catch (\Exception $e) {
            }
        }

        return $soItem->product_packaging_id ?? $baseProductPackagingId ?? '-';
    }

    /**
     * Process SO items & build PackingOrderItem data
     * Returns [packingOrderItemsData, errors]
     */
    public function processItems($salesOrder, $items, $doId, &$errors)
    {
        $data = [];
        $updateQtyZero = [];
        $updateQtyWorked = [];

        foreach ($items as $key => $value) {
            $result = SalesOrderItem::where('id', $value["so_item_id"])->first();

            if (!$result) {
                continue;
            }

            $so_item_id = $value["so_item_id"];
            $price = $value["price"];
            $so_qty = (float)$value["so_qty"];
            $do_qty = (float)$value["do_qty"];
            $rej_qty = $so_qty - $do_qty;
            $usd_disc = $value["usd_disc"];
            $percent_disc = 0;
            $total_discount = 0;

            if (empty($value["so_item_id"])) {
                $errors[] = 'SO Item ID tidak boleh kosong';
            }
            if (empty($value["product_packaging_id"])) {
                $errors[] = 'Product ID tidak boleh kosong';
            }

            $qty_total = $do_qty + $rej_qty;

            if ($so_qty < $qty_total) {
                $errors[] = 'Jumlah DO,REJ melebihi SO Qty';
            }

            if ($do_qty == 0 && $rej_qty == 0) {
                $updateQtyZero[] = $value["so_item_id"];
            }

            if ($do_qty > 0) {
                $total_disc = floatval(($usd_disc + (($price - $usd_disc) * ($percent_disc / 100))) * $do_qty);
                $data[] = [
                    'do_id' => $doId,
                    'product_packaging_id' => $value["product_packaging_id"],
                    'so_item_id' => $value["so_item_id"],
                    'packaging_id' => $value["packaging"] ?? $result->packaging_id,
                    'qty' => $do_qty,
                    'price' => $price,
                    'usd_disc' => $usd_disc,
                    'percent_disc' => $percent_disc,
                    'total_disc' => $total_disc,
                    'total' => floatval($do_qty * $price) - $total_disc,
                    'created_by' => Auth::id(),
                ];

                $updateQtyWorked[] = ['id' => $value["so_item_id"], 'qty_worked' => $do_qty];
            }

            if (empty($do_qty) && $rej_qty > 0) {
                $updateQtyWorked[] = ['id' => $value["so_item_id"], 'qty_worked' => $do_qty];
            }
        }

        // Batch update SO items
        foreach ($updateQtyZero as $soItemId) {
            SalesOrderItem::where('id', $soItemId)->update(['qty' => 0]);
        }
        foreach ($updateQtyWorked as $item) {
            SalesOrderItem::where('id', $item['id'])->update(['qty_worked' => $item['qty_worked']]);
        }

        return $data;
    }

    /**
     * Create MutasiShowroom for free products
     */
    public function createMutasiShowroom($salesOrder, $request, $mutasiItems, $suffix = '')
    {
        if (empty($mutasiItems)) {
            return null;
        }

        $mutasiItems = array_filter($mutasiItems, function ($item) {
            return isset($item['qty']) && $item['qty'] > 0;
        });

        if (empty($mutasiItems)) {
            return null;
        }

        $note = 'Mutasi Free Product dari SO ' . $salesOrder->code;
        if ($suffix) {
            $note .= ' (' . $suffix . ')';
        }

        $mutasi = MutasiShowroom::create([
            'kode' => CodeRepo::generateMutasiShowroom(MutasiShowroom::TYPE_SYSTEM_FREE_SO),
            'brand_name'        => $salesOrder->brand_name ?? '-',
            'type'              => MutasiShowroom::TYPE_SYSTEM_FREE_SO,
            'warehouse_from_id' => $request->origin_warehouse_id,
            'warehouse_to_id'   => $salesOrder->customer_id == 51 ? 53 : $salesOrder->customer_id,
            'customer_other_address_id' => $salesOrder->customer_other_address_id ?? null,
            'so_id'                     => $salesOrder->id,
            'tanggal'           => $salesOrder->so_date,
            'status'            => MutasiShowroom::STATUS['SETTLE'],
            'status_checked'    => MutasiShowroom::STATUS_CHECKED['CHECKED'],
            'status_barang'     => MutasiShowroom::STATUS_BARANG['DIAMBIL'],
            'note'              => $note,
            'created_by'        => Auth::id(),
        ]);

        foreach ($mutasiItems as $item) {
            MutasiShowroomDetail::create([
                'penjualan_showroom_id' => $mutasi->id,
                'product_packaging_id'  => $item['product_packaging_id'],
                'qty'                   => $item['qty'],
                'price'                 => 0,
                'total_price'           => 0,
                'note'                  => $item['note'] ?? null,
            ]);
        }

        return $mutasi;
    }

    /**
     * Insert stock deduction logs
     */
    public function insertStockLogs($stockLogs)
    {
        if (!empty($stockLogs)) {
            DB::table('do_stock_deduction_logs')->insert($stockLogs);
        }
    }

    /**
     * Clean currency fields from request
     */
    public function cleanCurrencyFields($request)
    {
        return [
            'discount_agen_idr' => $this->cleanCurrency($request->disc_agen_idr),
            'discount_kemasan_idr' => $this->cleanCurrency($request->disc_kemasan_idr),
            'sub_total' => $this->cleanCurrency($request->subtotal_2),
            'grand_total_idr' => $this->cleanCurrency($request->grand_total_idr),
            'disc_tambahan_idr' => $this->cleanCurrency($request->disc_tambahan_idr),
            'voucher_idr' => $this->cleanCurrency($request->voucher_idr),
            'delivery_cost_idr' => $this->cleanCurrency($request->delivery_cost_idr),
        ];
    }

    /**
     * Prepare SO fields for closing
     */
    public function prepareClosing($salesOrder, $request)
    {
        if (empty($salesOrder->code)) {
            $salesOrder->code = CodeRepo::generateSO();
        }

        $salesOrder->origin_warehouse_id = $request->origin_warehouse_id;
        $salesOrder->sales_senior_id = $request->sales_senior_id;
        $salesOrder->sales_id = $request->sales_id;
        $salesOrder->ekspedisi_id = $request->ekspedisi ?? null;
        $salesOrder->so_date = $request->so_date;
        $salesOrder->rekening = $request->rekening;
        $salesOrder->shipping_cost_buyer = $request->shipping_cost_buyer ?? 0;
        $salesOrder->status = 4;
        $salesOrder->updated_by = Auth::id();

        if ($salesOrder->count_rev == 1 && $request->keep_old_code == 1) {
            $salesOrder->code = $salesOrder->keep_code;
        } else {
            $salesOrder->count_rev = 0;
        }

        if (!$salesOrder->save()) {
            throw new \Exception('Gagal menyimpan Sales Order');
        }

        return $salesOrder;
    }

    /**
     * Get or create PackingOrder for closing
     */
    public function getOrCreatePackingOrder($salesOrder, $request)
    {
        $isRevision = ($salesOrder->count_rev == 0 && $request->has('keep_old_code'));

        if ($isRevision || $salesOrder->count_rev == 1) {
            $packing_order = PackingOrder::where('so_id', $salesOrder->id)->first();
        } else {
            $packing_order = null;
        }

        if (!$packing_order) {
            $company = Company::first();
            $packing_order = new PackingOrder;
            $packing_order->code = CodeRepo::generatePO();
            $packing_order->do_code = $salesOrder->code;
            $packing_order->so_id = $salesOrder->id;
            $packing_order->customer_id = $salesOrder->customer_id;
            $packing_order->customer_other_address_id = $salesOrder->customer_other_address_id;
            $packing_order->warehouse_id = $salesOrder->origin_warehouse_id;
            $packing_order->type_transaction = $salesOrder->type_transaction;
            $packing_order->idr_rate = $request->idr_rate;
            $packing_order->is_kurs_hold = (empty($request->idr_rate) || (float) $request->idr_rate <= 1);
            $packing_order->other_address = 0 ?? Null;
            $packing_order->note = $company->note ?? null;
            $packing_order->pic = $salesOrder->customer->pic;
            $packing_order->officer = $salesOrder->member->officer;
            $packing_order->account_representative = $salesOrder->created_by;
            $packing_order->vendor_id = $salesOrder->ekspedisi_id ?? null;
            $packing_order->status = 2;
            $packing_order->count_cancel = 0;
            $packing_order->created_by = Auth::id();
            $packing_order->save();
        } else {
            $packing_order->update([
                'status' => 2,
                'do_code' => $salesOrder->code,
                'idr_rate' => $request->idr_rate,
                'is_kurs_hold' => (empty($request->idr_rate) || (float) $request->idr_rate <= 1),
            ]);
        }

        return $packing_order;
    }

    /**
     * Insert or update PackingOrderDetail
     */
    public function upsertPackingOrderDetail($packingOrder, $salesOrder, $request)
    {
        $currencyFields = $this->cleanCurrencyFields($request);

        $poDetailData = [
            'discount_1' => $request->disc_agen_percent,
            'discount_2' => $request->disc_kemasan_percent,
            'discount_1_idr' => $currencyFields['discount_agen_idr'],
            'discount_2_idr' => $currencyFields['discount_kemasan_idr'],
            'discount_idr' => $currencyFields['disc_tambahan_idr'],
            'voucher_idr' => $currencyFields['voucher_idr'],
            'purchase_total_idr' => $currencyFields['sub_total'],
            'grand_total_idr' => $currencyFields['grand_total_idr'],
            // Rumus sama seperti approve revisi & reset_cost: agen + kemasan + tambahan.
            'total_discount_idr' => $currencyFields['discount_agen_idr'] + $currencyFields['discount_kemasan_idr'] + $currencyFields['disc_tambahan_idr'],
            'terbilang' => CustomHelper::terbilang($currencyFields['grand_total_idr']),
            'created_by' => Auth::id(),
        ];

        if ($salesOrder->shipping_cost_buyer == 0) {
            $poDetailData['delivery_cost_idr'] = $currencyFields['delivery_cost_idr'];
        } elseif ($salesOrder->shipping_cost_buyer == 1) {
            $poDetailData['delivery_cost_idr'] = 0;
        }

        $existingDetail = PackingOrderDetail::where('do_id', $packingOrder->id)->first();
        if ($existingDetail) {
            $poDetailData['updated_by'] = Auth::id();
            $existingDetail->update($poDetailData);
        } else {
            $poDetailData['do_id'] = $packingOrder->id;
            $poDetailData['other_cost_idr'] = 0;
            PackingOrderDetail::create($poDetailData);
        }
    }

    /**
     * Buat nota/invoicing langsung di tutup_so kalau nota valid:
     * kurs valid (>1) + grand_total_idr > 0.
     * Idempotent: kalau invoice sudah ada (termasuk soft-delete revisi),
     * sinkronkan ulang + restore — kecuali VOID (final).
     * Duplikat logika createInvoiceIfNeeded di PackingOrderController
     * (Release SPK) supaya Release tetap aman jadi fallback/sync.
     */
    public function createInvoiceIfNeeded($packingOrderId)
    {
        $packing = PackingOrder::where('id', $packingOrderId)->first();
        $detail  = PackingOrderDetail::where('do_id', $packingOrderId)->first();

        if (!$packing || !$detail) {
            return false;
        }

        // Nota valid = kurs valid + total valid.
        $idrRate = (float) ($packing->idr_rate ?? 0);
        if ($idrRate <= 1) {
            return false;
        }
        if ((float) ($detail->grand_total_idr ?? 0) <= 0) {
            return false;
        }

        $existing = Invoicing::withTrashed()->where('do_id', $packingOrderId)->first();

        if ($existing) {
            // Invoice VOID bersifat final — jangan restore/sinkron.
            if ($existing->trashed() && (int) $existing->status === Invoicing::STATUS['VOID']) {
                return false;
            }
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update([
                'code' => $packing->do_code,
                'customer_id' => $packing->customer_id,
                'customer_other_address_id' => $packing->customer_other_address_id,
                'grand_total_idr' => $detail->grand_total_idr,
                'status' => Invoicing::STATUS['ACTIVE'],
            ]);
            return true;
        }

        Invoicing::create([
            'code' => $packing->do_code,
            'do_id' => $packing->id,
            'customer_id' => $packing->customer_id,
            'customer_other_address_id' => $packing->customer_other_address_id,
            'grand_total_idr' => $detail->grand_total_idr,
            'status' => Invoicing::STATUS['ACTIVE'],
            'type' => 0,
            'created_by' => Auth::id(),
        ]);

        return true;
    }
}
