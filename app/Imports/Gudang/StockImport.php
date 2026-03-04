<?php

namespace App\Imports\Gudang;

use App\Entities\Master\ProductPack;
use App\Entities\Master\ProductMinStock;
use App\Entities\Gudang\StockMove;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StockImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected $warehouse_id = 2; // Gudang Araya
    protected $opening_date;
    protected $opening_code;

    public $error = [];
    public $success = [];

    public function __construct($opening_date)
    {
        $this->opening_date = $opening_date;
        $this->opening_code = 'OPENING-' . date('Ymd', strtotime($opening_date));
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {

            // 🔥 Hapus opening lama (jika ada)
            // StockMove::where('warehouse_id', $this->warehouse_id)
            //     ->where('code_transaction', $this->opening_code)
            //     ->delete();
            StockMove::where('warehouse_id', $this->warehouse_id)->delete();

            foreach ($rows as $row) {

                // ===== VALIDASI =====
                if (empty($row['id'])) {
                    $this->error[] = 'ID kosong ditemukan';
                    continue;
                }

                if ($row['quantity'] === null || $row['quantity'] === '') {
                    continue; // skip jika quantity tidak diisi
                }

                if (!is_numeric($row['quantity']) || $row['quantity'] < 0) {
                    $this->error[] = $row['id'] . ' quantity tidak valid';
                    continue;
                }

                $qty = floatval($row['quantity']); // quantity = 0 tetap valid

                // ===== AMBIL PRODUCT PACK =====
                $product_pack = ProductPack::with(['product', 'packaging'])
                    ->where('id', $row['id'])
                    ->whereNull('deleted_at')
                    ->first();

                if (!$product_pack) {
                    $this->error[] = $row['id'] . ' product tidak ditemukan';
                    continue;
                }

                // ===== AMBIL / BUAT ProductMinStock =====
                $min_stock = ProductMinStock::firstOrNew([
                    'product_packaging_id' => $product_pack->id,
                    'warehouse_id' => $this->warehouse_id
                ]);
                $min_stock->quantity = $qty;
                $min_stock->save();

                // ===== INSERT OPENING MOVE STOCK =====
                StockMove::unguard(); // optional, jika ada fillable
                $stockMove = new StockMove([
                    'code_transaction'     => $this->opening_code,
                    'warehouse_id'         => $this->warehouse_id,
                    'product_packaging_id' => $product_pack->id,
                    'stock_in'             => $qty,
                    'stock_out'            => 0,
                    'stock_balance'        => $qty,
                    'note'                 => 'Opening Stock ' . date('d-m-Y', strtotime($this->opening_date)),
                    'created_by'           => Auth::id(),
                    'updated_by'           => Auth::id(),
                ]);
                $stockMove->timestamps = false; // matikan auto timestamps
                $stockMove->created_at = $this->opening_date;
                $stockMove->updated_at = $this->opening_date;
                $stockMove->save();

                $this->success[] = $product_pack->code . ' - ' . $product_pack->name . ' berhasil diopening';
            }

            if (empty($this->success)) $this->success[] = 'Tidak ada data berhasil diimport';
            if (empty($this->error)) $this->error[] = 'Tidak ada data gagal';

            DB::commit();

        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            $this->error[] = $e->getMessage();
        }
    }

    public function startRow(): int
    {
        return 2; // karena row 1 adalah header
    }
}