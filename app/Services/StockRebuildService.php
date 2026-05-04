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

        // 1. Penjualan / DO
        if ($type === 'TRANSAKSI / NOTA') {
            // Tarik data PO untuk mendapatkan nama customer
            // Note: Sesuaikan relasi 'customer' atau 'so.customer' dengan nama fungsi di model Anda
            $po = \App\Entities\Penjualan\PackingOrder::with(['customer', 'so.customer'])->find($row->reference_id);
            
            $customerName = '';
            if ($po) {
                // Cek apakah relasi customer ada langsung di PO atau melalui SO
                if ($po->customer) {
                    $customerName = $po->customer->name;
                } elseif ($po->so && $po->so->customer) {
                    $customerName = $po->so->customer->name;
                }
            }
            
            return $code . ' - ' . ($customerName ?: 'Customer Unknown');
        }

        // 2. Receiving
        if ($type === 'RECEIVING') {
            return $code . ' - RECEIVING';
        }

        // 3. Mutasi Showroom
        if ($type === 'MUTASI SHOWROOM') {
            return 'Mutasi Showroom - DIAMBIL';
        }

        // 4. Mutasi Gudang
        if ($type === 'MUTASI OUT') {
            return 'Mutasi Gudang - DIAMBIL';
        }

        // Default (Misal untuk SPK yang tidak di-request formatnya)
        return $code . ' - ' . $type;
    }
}