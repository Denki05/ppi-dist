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
        DB::transaction(function () {

            $rows = DB::table('stock_transform')
                ->where('is_posted', 0)
                ->orderBy('doc_date')
                ->orderBy('reference_id') // lebih stabil dari id transform
                ->lockForUpdate()
                ->get();

            foreach ($rows as $row) {

                $docDateTime = date('Y-m-d H:i:s', strtotime($row->doc_date));

                $master = DB::table('master_product_min_stocks')
                    ->where('warehouse_id', $row->warehouse_id)
                    ->where('product_packaging_id', $row->product_packaging_id)
                    ->lockForUpdate()
                    ->first();

                if (!$master) {
                    $masterId = DB::table('master_product_min_stocks')
                        ->insertGetId([
                            'warehouse_id'         => $row->warehouse_id,
                            'product_packaging_id' => $row->product_packaging_id,
                            'quantity'             => 0,
                            'created_at'           => $docDateTime,
                            'updated_at'           => $docDateTime,
                        ]);

                    $master = DB::table('master_product_min_stocks')
                        ->where('id', $masterId)
                        ->first();
                }

                $saldoAwal  = (float) $master->quantity;
                $saldoAkhir = $saldoAwal + $row->qty_in - $row->qty_out;

                if ($saldoAkhir < 0) {
                    Log::warning(
                        "Stock minus detected | Product: {$row->product_packaging_id} | Warehouse: {$row->warehouse_id} | Date: {$row->doc_date} | Saldo: {$saldoAkhir}"
                    );
                }

                DB::table('master_product_min_stocks')
                    ->where('id', $master->id)
                    ->update([
                        'quantity'   => $saldoAkhir,
                        'updated_at' => $docDateTime
                    ]);

                DB::table('gudang_move_stock')->insert([
                    'warehouse_id'         => $row->warehouse_id,
                    'product_packaging_id' => $row->product_packaging_id,
                    'code_transaction'     => $row->doc_code,
                    'stock_in'             => $row->qty_in,
                    'stock_out'            => $row->qty_out,
                    'stock_balance'        => $saldoAkhir,
                    'note'                 => $row->doc_type,
                    'created_at'           => $docDateTime,
                    'updated_at'           => $docDateTime,
                ]);

                DB::table('stock_transform')
                    ->where('id', $row->id)
                    ->update([
                        'is_posted'  => 1,
                        'updated_at' => $docDateTime
                    ]);
            }
        });
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
    }
}