<?php

namespace App\Services;

use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrderKontrak;
use App\Entities\Penjualan\SalesOrderKontrakItem;
use App\Entities\Penjualan\SalesOrderKontrakPivot;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\ProductPack;
use App\Helpers\CodeRepo;
use Auth;
use DB;
use Log;

class SalesOrderStoreService
{
    /**
     * Create new SO Awal (non-PPN)
     * Returns ['success' => bool, 'errors' => array, 'sales_order' => ...]
     */
    public function create($request, $memberId)
    {
        $errors = [];

        $get_store = CustomerOtherAddress::where('id', $memberId)->first();

        $insert = new SalesOrder;
        $insert->so_code = CodeRepo::generateSoAwal();
        $insert->brand_name = $request->brand_name;
        $insert->customer_id = $get_store->customer_id;
        $insert->customer_other_address_id = $memberId;
        $insert->type_transaction = $request->type_transaction;
        $insert->so_for = 1;
        $insert->so_date = null;
        $insert->type_so = 'nonppn';
        $insert->approval_mou = $request->approval;
        $insert->idr_rate = str_replace(',', '.', $request->kurs);
        $insert->note = $request->note_so;
        $insert->is_proforma = 0;

        // Auto generate estimate untuk semua SO (Cash/Tempo)
        $typeUpper = strtoupper($request->type_transaction);
        if (in_array($typeUpper, ['CASH', 'TEMPO'])) {
            $insert->is_estimate = 1;
            $insert->estimate_code = $this->generateEstimateCode();
        } else {
            $insert->is_estimate = 0;
        }

        // Diskon global
        $insert->catatan = null;
        $insert->disc_percent = $request->disc_percent ?? 0;
        $insert->disc_idr = $request->global_disc_idr ?? 0;
        $insert->disc_usd = $request->global_disc_usd ?? 0;
        $insert->disc_kemasan = $request->global_disc_kemasan ?? 0;

        $insert->created_by = Auth::id();

        if ($request->so_indent == "YES") {
            $insert->code = null;
            $insert->status = 1;
            $insert->so_indent = 1;
            $insert->indent_status = 1;
        } elseif ($request->so_indent == "NO") {
            $insert->code = null;
            $insert->status = $request->ajukankelanjutan ? 2 : 1;
            $insert->so_indent = SalesOrder::INDENT['NO'];
        }
        $insert->condition = 1;
        $insert->payment_status = 0;
        $insert->count_rev = 0;

        if (!$insert->save()) {
            return ['success' => false, 'errors' => ['Gagal menyimpan Sales Order'], 'sales_order' => null];
        }

        // Process items
        if ($request->sku) {
            $itemErrors = $this->processItems($insert, $request);
            if (!empty($itemErrors)) {
                return ['success' => false, 'errors' => $itemErrors, 'sales_order' => $insert];
            }
        }

        // Log activity
        if ($request->so_indent == "YES") {
            \LogActivity::addToLog('Created a new SO-Indent: ' . $insert->so_code);
        } elseif ($request->so_indent == "NO") {
            \LogActivity::addToLog('Created a new SO: ' . $insert->so_code);
        }

        return ['success' => true, 'errors' => [], 'sales_order' => $insert];
    }

    /**
     * Process SO items from store request
     */
    protected function processItems($salesOrder, $request)
    {
        $errors = [];
        $listItem = [];

        foreach ($request->sku as $key => $item) {
            $duplicate_product = [];
            $duplicate = false;
            $listItem[] = [
                'sku' => $request->sku[$key],
                'free_product' => $request->free_product[$key],
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
                $errors[] = 'Item sudah ada!';
                return $errors;
            }

            // Get base product packaging ID
            $baseProductPackagingId = null;
            $product = ProductPack::where('id', $request->sku[$key])->first();

            if ($product) {
                if (strpos($product->id, '_1') !== false) {
                    $baseProduct = ProductPack::where('id', str_replace('_1', '', $product->id))->first();
                    if ($baseProduct) {
                        $baseProductPackagingId = $baseProduct->id;
                    }
                } else {
                    $baseProductPackagingId = $product->id;
                }
            }

            $is_free = ($request->free_product[$key] == 1);

            $insertDetail = new SalesOrderItem;
            $insertDetail->so_id = $salesOrder->id;
            $insertDetail->kontrak = $request->value_kontrak[$key];
            $insertDetail->product_packaging_id = $baseProductPackagingId;
            $insertDetail->price = $request->price[$key];
            $insertDetail->qty = $request->qty[$key];
            $insertDetail->packaging_id = $request->packaging[$key];
            $insertDetail->free_product = $request->free_product[$key];
            $insertDetail->disc_usd = $is_free ? 0 : ($request->disc[$key] ?? 0);
            $insertDetail->created_by = Auth::id();
            $insertDetail->status = 1;

            if ($request->value_kontrak[$key] == 1) {
                $insertDetail->kontrak_id = $request->kontrak_so_id[$key];
            }

            $insertDetail->save();

            // Process kontrak pivot
            if ($request->value_kontrak[$key] == 1) {
                $kontrakErrors = $this->processKontrakPivot($insertDetail, $request->kontrak_so_id[$key], $request->qty[$key]);
                if (!empty($kontrakErrors)) {
                    return $kontrakErrors;
                }
            }
        }

        return $errors;
    }

    /**
     * Process kontrak pivot for item
     */
    protected function processKontrakPivot($insertDetail, $kontrakId, $qty)
    {
        $errors = [];

        $search_kontrak = SalesOrderKontrak::where('id', $kontrakId)->first();
        $item_kontrak = SalesOrderKontrakItem::where('so_kontrak_id', $search_kontrak->id)->first();

        if ($search_kontrak) {
            $log_kontrak = DB::table('penjualan_so_kontrak_log')
                ->where('so_kontrak_id', $search_kontrak->id)
                ->select(DB::raw('SUM(qty_worked) AS total_qty_kontrak'))
                ->first();

            $sisa_qty = $item_kontrak->qty - ($log_kontrak->total_qty_kontrak ?? 0);

            if ($sisa_qty < $qty) {
                $errors[] = 'Sisa Kontrak <b>' . $item_kontrak->product_pack->name . '</b> tidak mencukupi..!!';
                return $errors;
            }
        }

        $pivot_kontrak = new SalesOrderKontrakPivot;
        $pivot_kontrak->so_item_id = $insertDetail->id;
        $pivot_kontrak->so_kontrak_item_id = $item_kontrak->id;
        $pivot_kontrak->save();

        return $errors;
    }

    /**
     * Generate unique estimate code
     */
    protected function generateEstimateCode()
    {
        $today = now();
        $prefix = $today->format('ymd');
        $lastEstimate = SalesOrder::where('estimate_code', 'LIKE', $prefix . '-%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(estimate_code, '-', -1) AS UNSIGNED) DESC")
            ->first();

        if ($lastEstimate) {
            $lastNumber = (int) substr($lastEstimate->estimate_code, -2);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
    }
}
