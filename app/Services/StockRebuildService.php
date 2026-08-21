<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockRebuildService
{
    /*
    |--------------------------------------------------------------------------
    | Helper: Resolve Quantity Column
    |--------------------------------------------------------------------------
    */
    private function resolveQuantity($row)
    {
        if (isset($row->quantity)) {
            return (float) $row->quantity;
        }

        if (isset($row->qty)) {
            return (float) $row->qty;
        }

        if (isset($row->total_qty)) {
            return (float) $row->total_qty;
        }

        if (isset($row->amount)) {
            return (float) $row->amount;
        }

        Log::warning("Quantity column not found for reference id: " . $row->id);

        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 1 - TRANSFORM
    |--------------------------------------------------------------------------
    */
    public function transform()
    {
        DB::transaction(function () {

            $allTemp = [];

            // TEMP_IN
            $tempIns = DB::table('temp_in')
                ->whereNull('deleted_at')
                ->get();

            foreach ($tempIns as $row) {
                $allTemp[] = [
                    'type' => 'in',
                    'reference_table' => 'temp_in',
                    'data' => $row
                ];
            }

            // TEMP_OUT
            $tempOuts = DB::table('temp_out')
                ->whereNull('deleted_at')
                ->get();

            foreach ($tempOuts as $row) {
                $allTemp[] = [
                    'type' => 'out',
                    'reference_table' => 'temp_out',
                    'data' => $row
                ];
            }

            // TEMP_TRANS
            $tempTrans = DB::table('temp_trans')
                ->whereNull('deleted_at')
                ->get();

            foreach ($tempTrans as $row) {
                $allTemp[] = [
                    'type' => 'out',
                    'reference_table' => 'temp_trans',
                    'data' => $row
                ];
            }

            // SORT BY doc_date → id
            usort($allTemp, function ($a, $b) {

                $dateA = strtotime($a['data']->doc_date);
                $dateB = strtotime($b['data']->doc_date);

                if ($dateA == $dateB) {
                    return $a['data']->id - $b['data']->id;
                }

                return $dateA - $dateB;
            });

            // INSERT TO stock_transform
            foreach ($allTemp as $item) {

                $row      = $item['data'];
                $type     = $item['type'];
                $refTable = $item['reference_table'];

                $exists = DB::table('stock_transform')
                    ->where('reference_table', $refTable)
                    ->where('reference_id', $row->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $qty = $this->resolveQuantity($row);

                DB::table('stock_transform')->insert([
                    'doc_code'             => $row->doc_code,
                    'doc_type'             => $row->doc_type,
                    'doc_date'             => $row->doc_date,
                    'warehouse_id'         => $row->warehouse_id,
                    'product_packaging_id' => $row->product_packaging_id,
                    'qty_in'               => ($type === 'in') ? $qty : 0,
                    'qty_out'              => ($type === 'out') ? $qty : 0,
                    'reference_table'      => $refTable,
                    'reference_id'         => $row->id,
                    'is_posted'            => 0,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 2 - POSTING (Minus Allowed)
    |--------------------------------------------------------------------------
    */
    public function posting()
    {
        // Panggil Petugas 1 Pintu kita
        $stockService = new \App\Services\StockService();

        DB::transaction(function () use ($stockService) {
            $rows = DB::table('stock_transform')
                ->where('is_posted', 0)
                ->orderBy('doc_date')
                ->orderBy('reference_id') // lebih stabil dari id transform
                ->lockForUpdate()
                ->get();

            foreach ($rows as $row) {
                // 1. Tentukan ini barang Masuk atau Keluar
                $type = ($row->qty_in > 0) ? 'IN' : 'OUT';
                
                // 2. Ambil angkanya (pilih yang bukan 0)
                $qty = ($type === 'IN') ? $row->qty_in : $row->qty_out;

                // 3. Format tanggal kejadian masa lalu
                $docDateTime = date('Y-m-d H:i:s', strtotime($row->doc_date));

                // 4. KETOK 1 PINTU: Masukkan ke Mesin Waktu!
                $stockService->replayHistoricalLog(
                    $row->warehouse_id,
                    $row->product_packaging_id,
                    $qty,
                    $type,
                    $row->doc_code,
                    $docDateTime,
                    $this->generateCustomNote($row) // <--- UBAH BAGIAN INI
                );

                // 5. Tandai bahwa dokumen ini sudah beres diposting
                DB::table('stock_transform')
                    ->where('id', $row->id)
                    ->update([
                        'is_posted'  => 1,
                        'updated_at' => now()
                    ]);
            }
        });
    }

    public function recalculateReservedQuantity()
    {
        // 1. Sapu bersih (Nol-kan) semua reserved_quantity peninggalan masa lalu
        DB::table('master_product_min_stocks')->update(['reserved_quantity' => 0]);

        // 2. Tarik HANYA data yang sesuai aturan: SO Status 4 DAN DO Status 3
        $activePackingOrders = \App\Entities\Penjualan\PackingOrder::with('do_detail')
            ->whereHas('so', function ($query) {
                // Pastikan relasi ke SalesOrder (penjualan_so) bernama 'so'
                $query->where('status', 4); 
            })
            ->where('status', 3) // DO Status 3
            ->get();

        // 3. Hitung ulang dan kembalikan kuota booking-nya
        foreach ($activePackingOrders as $po) {
            // Pastikan gudang asal ada
            if (!$po->warehouse_id) continue;

            foreach ($po->do_detail as $item) {
                // Bersihkan kode produk (jika ada suffix seperti _1, _2)
                $baseId = preg_replace('/_\d+$/', '', $item->product_packaging_id);

                $stock = \App\Entities\Master\ProductMinStock::where('warehouse_id', $po->warehouse_id)
                    ->where('product_packaging_id', $baseId)
                    ->first();

                if ($stock) {
                    $stock->reserved_quantity += $item->qty;
                    $stock->save();
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 3 - FULL PROCESS
    |--------------------------------------------------------------------------
    */
    public function process()
    {
        $this->transform();
        $this->posting();

        // Panggil perapihan kuota booking di paling akhir!
        $this->recalculateReservedQuantity();
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Generate Custom Note
    |--------------------------------------------------------------------------
    */
    private function generateCustomNote($row)
    {
        $type = $row->doc_type;
        $code = $row->doc_code;
        
        // dd($type, $code);
    
        if ($type === 'TRANSAKSI / NOTA') {

            $tempRow = DB::table('temp_trans')->find($row->reference_id);
        
            $po = null;
        
            if ($tempRow) {
                $po = \App\Entities\Penjualan\PackingOrder::find($tempRow->reference_id);
            }
        
            if (!$po) {
                $po = \App\Entities\Penjualan\PackingOrder::where('do_code', $code)
                    ->orWhere('code', $code)
                    ->first();
            }
        
            $customerDesc = '';
        
            if ($po) {
                // ✅ Query manual karena customer_other_address_id varchar "37.1"
                // Eloquent gagal match karena type mismatch dengan id integer
                $member = \App\Entities\Master\CustomerOtherAddress::whereRaw(
                    'CAST(id AS CHAR) = ?', 
                    [$po->customer_other_address_id]
                )->first();
        
                if ($member) {
                    $customerDesc = $member->name . ' ' . $member->text_kota;
                }
            }
            
            // dd($customerDesc);
        
            return $code . ' - ' . ($customerDesc ?: 'Customer Unknown');
        }
    
        if ($type === 'RECEIVING') {
            return $code . ' - RECEIVING';
        }
    
        if ($type === 'MUTASI SHOWROOM') {
            return 'Mutasi Showroom - DIAMBIL';
        }
    
        if ($type === 'MUTASI OUT') {
            return 'Mutasi Gudang - DIAMBIL';
        }
    
        return $code . ' - ' . $type;
    }

    /*
    |--------------------------------------------------------------------------
    | REBUILD PER VARIANT (Selective, full-history, delete + rewrite)
    |--------------------------------------------------------------------------
    | Beda dengan process() global: scoped ke product_packaging_id tertentu,
    | baca LANGSUNG dari tabel sumber (bukan lewat temp_in/out/trans),
    | hapus stock_move lama (KECUALI baris OPENING-%), lalu tulis ulang
    | secara kronologis dari SELURUH histori (tanpa batas tanggal).
    |--------------------------------------------------------------------------
    */
    public function rebuildForVariants(array $productPackagingIds, $warehouseId)
    {
        $productPackagingIds = array_values(array_unique($productPackagingIds));

        if (empty($productPackagingIds) || !$warehouseId) {
            throw new \Exception('Variant dan Warehouse wajib dipilih.');
        }

        $summary = [];

        DB::transaction(function () use ($productPackagingIds, $warehouseId, &$summary) {

            $stockService = new \App\Services\StockService();

            foreach ($productPackagingIds as $productId) {

                // 1. Hapus stock_move lama, KECUALI baris OPENING (saldo awal manual, tidak punya sumber)
                DB::table('stock_move')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_packaging_id', $productId)
                    ->where('code_transaction', 'not like', 'OPENING-%')
                    ->delete();

                // 2. Kumpulkan SELURUH histori transaksi utk variant ini dari semua sumber
                $collected = $this->collectAllHistoryForVariant($productId, $warehouseId);

                // 3. Urutkan kronologis (tanggal, lalu reference_id sbg tie-breaker stabil)
                usort($collected, function ($a, $b) {
                    $dateA = strtotime($a['doc_date']);
                    $dateB = strtotime($b['doc_date']);
                    if ($dateA === $dateB) {
                        return $a['reference_id'] <=> $b['reference_id'];
                    }
                    return $dateA <=> $dateB;
                });

                // 4. Tulis ulang satu per satu lewat "1 pintu" StockService
                //    (otomatis nyambung ke saldo OPENING yang dipertahankan di stock_move)
                foreach ($collected as $row) {
                    $stockService->replayHistoricalLog(
                        $warehouseId,
                        $productId,
                        $row['qty'],
                        $row['type'], // 'IN' / 'OUT'
                        $row['doc_code'],
                        date('Y-m-d H:i:s', strtotime($row['doc_date'])),
                        $row['note']
                    );
                }

                // 5. Set quantity fisik = saldo TERAKHIR stock_move (sumber kebenaran tunggal)
                $lastBalance = DB::table('stock_move')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_packaging_id', $productId)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->value('stock_balance');

                \App\Entities\Master\ProductMinStock::updateOrCreate(
                    ['warehouse_id' => $warehouseId, 'product_packaging_id' => $productId],
                    ['quantity' => $lastBalance ?? 0]
                );

                $summary[$productId] = count($collected);
            }

            // 6. Reset & hitung ulang reserved_quantity KHUSUS variant-variant ini saja
            $this->recalculateReservedQuantityForVariants($productPackagingIds, $warehouseId);
        });

        return $summary;
    }

    /*
    |--------------------------------------------------------------------------
    | Kumpulkan seluruh histori (all-time) untuk 1 variant dari semua sumber
    |--------------------------------------------------------------------------
    */
    private function collectAllHistoryForVariant($productId, $warehouseId)
    {
        $rows = [];

        // === A. RECEIVING (INBOUND & RETUR) — status ACC ===
        \App\Entities\Gudang\Receiving::with('details')
            ->where('warehouse_id', $warehouseId)
            ->where('status', \App\Entities\Gudang\Receiving::STATUS['ACC'])
            ->whereHas('details', function ($q) use ($productId) {
                $q->where('product_packaging_id', $productId)->where('quantity_ri', '>', 0);
            })
            ->chunk(100, function ($receivings) use ($productId, &$rows) {
                foreach ($receivings as $receiving) {
                    foreach ($receiving->details as $detail) {
                        if ($detail->product_packaging_id != $productId || $detail->quantity_ri <= 0) continue;

                        $rows[] = [
                            'type'         => 'IN',
                            'qty'          => (float) $detail->quantity_ri,
                            'doc_code'     => 'RI-' . $receiving->code,
                            'doc_date'     => $receiving->acc_at ?? $receiving->created_at,
                            'note'         => 'Receiving ACC - ' . ($detail->product_pack->name ?? $receiving->code),
                            'reference_id' => $receiving->id,
                        ];
                    }
                }
            });

        // === B. STOCK ADJUSTMENT (gudang_stock_adjustment) — IN (plus) & OUT (min) ===
        \App\Entities\Gudang\StockAdjustment::where('warehouse_id', $warehouseId)
            ->where('product_packaging_id', $productId)
            ->chunk(100, function ($adjustments) use (&$rows) {
                foreach ($adjustments as $adj) {
                    if ((float) $adj->plus > 0) {
                        $rows[] = [
                            'type'         => 'IN',
                            'qty'          => (float) $adj->plus,
                            'doc_code'     => 'ADJ-' . $adj->code,
                            'doc_date'     => $adj->created_at,
                            'note'         => 'Stock Adjustment - ' . ($adj->note ?? $adj->code),
                            'reference_id' => $adj->id,
                        ];
                    }
                    if ((float) $adj->min > 0) {
                        $rows[] = [
                            'type'         => 'OUT',
                            'qty'          => (float) $adj->min,
                            'doc_code'     => 'ADJ-' . $adj->code,
                            'doc_date'     => $adj->created_at,
                            'note'         => 'Stock Adjustment - ' . ($adj->note ?? $adj->code),
                            'reference_id' => $adj->id,
                        ];
                    }
                }
            });

        // === C. SPK STOCK OUT — SEMUA (tanpa whitelist), tetap hardcode warehouse Araya (id 2)
        //     mengikuti aturan bisnis yang sama seperti collectSPKStockOut() di StockController.
        if ((int) $warehouseId === 2) {
            \App\Entities\Gudang\PurchaseOrder::with('purchase_order_detail')
                ->where('type', 0)   // SPK
                ->where('status', 4) // ACC / SENT
                ->whereHas('purchase_order_detail', function ($q) use ($productId) {
                    $q->where('product_packaging_id', $productId)->where('quantity', '>', 0);
                })
                ->chunk(100, function ($orders) use ($productId, &$rows) {
                    foreach ($orders as $order) {
                        foreach ($order->purchase_order_detail as $item) {
                            if ($item->product_packaging_id != $productId || $item->quantity <= 0) continue;

                            $rows[] = [
                                'type'         => 'OUT',
                                'qty'          => (float) $item->quantity,
                                'doc_code'     => $order->code,
                                'doc_date'     => $order->created_at,
                                'note'         => $order->code . ' - SPK',
                                'reference_id' => $order->id,
                            ];
                        }
                    }
                });
        }

        // === D. MUTASI SHOWROOM — OUT dari warehouse asal ===
        \App\Entities\Gudang\MutasiShowroom::with('details')
            ->where('warehouse_from_id', $warehouseId)
            ->where('status', 2)
            ->where('status_checked', 1)
            ->where('status_barang', 2)
            ->where('type', '!=', 5)
            ->whereHas('details', function ($q) use ($productId) {
                $q->where('product_packaging_id', $productId)->where('qty', '>', 0);
            })
            ->chunk(100, function ($mutasis) use ($productId, &$rows) {
                foreach ($mutasis as $mutasi) {
                    foreach ($mutasi->details as $item) {
                        if ($item->product_packaging_id != $productId || $item->qty <= 0) continue;

                        $rows[] = [
                            'type'         => 'OUT',
                            'qty'          => (float) $item->qty,
                            'doc_code'     => $mutasi->kode,
                            'doc_date'     => $mutasi->tanggal ?? $mutasi->created_at,
                            'note'         => 'Mutasi Showroom - DIAMBIL',
                            'reference_id' => $mutasi->id,
                        ];
                    }
                }
            });

        // === E. MUTASI OUT — OUT dari warehouse asal ===
        \App\Entities\Gudang\MutasiOut::with('mutasiOutDetails')
            ->where('warehouse_from', $warehouseId)
            ->where('status', 3)
            ->whereHas('mutasiOutDetails', function ($q) use ($productId) {
                $q->where('product_packaging_id', $productId)->where('quantity', '>', 0);
            })
            ->chunk(100, function ($mutasis) use ($productId, &$rows) {
                foreach ($mutasis as $mutasi) {
                    foreach ($mutasi->mutasiOutDetails as $item) {
                        if ($item->product_packaging_id != $productId || $item->quantity <= 0) continue;

                        $rows[] = [
                            'type'         => 'OUT',
                            'qty'          => (float) $item->quantity,
                            'doc_code'     => $mutasi->code,
                            'doc_date'     => $mutasi->date ?? $mutasi->created_at,
                            'note'         => 'Mutasi Gudang - DIAMBIL',
                            'reference_id' => $mutasi->id,
                        ];
                    }
                }
            });

        // === F. PACKING ORDER (DO / Nota Jual) — OUT ===
        \App\Entities\Penjualan\PackingOrder::with('do_detail')
            ->where('warehouse_id', $warehouseId)
            ->where('status', 6)
            ->whereHas('do_detail', function ($q) use ($productId) {
                $q->where('product_packaging_id', $productId)->where('qty', '>', 0);
            })
            ->chunk(100, function ($orders) use ($productId, &$rows) {
                foreach ($orders as $order) {
                    foreach ($order->do_detail as $item) {
                        if ($item->product_packaging_id != $productId || $item->qty <= 0) continue;

                        $rows[] = [
                            'type'         => 'OUT',
                            'qty'          => (float) $item->qty,
                            'doc_code'     => $order->do_code,
                            'doc_date'     => $order->created_at,
                            'note'         => $order->do_code . ' - TRANSAKSI / NOTA',
                            'reference_id' => $order->id,
                        ];
                    }
                }
            });

        return $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | Reset & hitung ulang reserved_quantity KHUSUS variant-variant terpilih
    |--------------------------------------------------------------------------
    */
    private function recalculateReservedQuantityForVariants(array $productPackagingIds, $warehouseId)
    {
        \App\Entities\Master\ProductMinStock::where('warehouse_id', $warehouseId)
            ->whereIn('product_packaging_id', $productPackagingIds)
            ->update(['reserved_quantity' => 0]);

        $activePackingOrders = \App\Entities\Penjualan\PackingOrder::with('do_detail')
            ->where('warehouse_id', $warehouseId)
            ->whereHas('so', function ($query) {
                $query->where('status', 4);
            })
            ->where('status', 3)
            ->get();

        foreach ($activePackingOrders as $po) {
            if (!$po->warehouse_id) continue;

            foreach ($po->do_detail as $item) {
                $baseId = preg_replace('/_\d+$/', '', $item->product_packaging_id);

                if (!in_array($baseId, $productPackagingIds)) continue;

                $stock = \App\Entities\Master\ProductMinStock::where('warehouse_id', $po->warehouse_id)
                    ->where('product_packaging_id', $baseId)
                    ->first();

                if ($stock) {
                    $stock->reserved_quantity += $item->qty;
                    $stock->save();
                }
            }
        }
    }
}