<?php

namespace App\Imports\Accounting;

use App\Entities\Master\BrandLokal;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\Entities\Master\ProductFinance;
use App\Entities\Master\Mitra;
use App\Entities\Accounting\PriceLogFinance;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use DB;

class ProductFinanceImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            $collect_error = [];
            $collect_success = [];

            foreach ($rows as $row) 
            {
                // Cek apakah produk finance sudah ada
                $product_pack = ProductFinance::where('id', $row['id'])->first();

                if (!$product_pack) {
                    $collect_error[] = "Product ID {$row['id']} not found!";
                    continue;
                }

                // Ambil nilai setelah "-"
                $pack = explode("-", $product_pack->id);
                $packaging_name = end($pack);

                // Cek apakah Packaging ada
                $packaging = Packaging::where('pack_name', $packaging_name)->first();
                
                // Buat finance baru jika tidak ada
                if (!$product_pack) {
                    ProductFinance::create([
                        'id' => $row['id'],
                        'brand_name' => $row['brand'],
                        'code_product' => $row['code'],
                        'name_product' => $row['name'],
                        'mitra_id' => optional(Mitra::where('name', $row['mitra'])->first())->id,
                        'product_id' => $row['id'],
                        'packaging_id' => optional($packaging)->id,
                        'buying_price_usd_unit' => $row['harga_beli'],
                        'selling_price_usd_unit' => $row['harga_jual'],
                        'status' => ProductFinance::STATUS['ACTIVE'],
                        'year' => 2025,
                    ]);

                    $collect_success[] = "{$row['code']} - {$row['name']} added successfully.";
                    continue;
                }

                // Jika produk sudah ada, cari finance record
                $product_finance = ProductFinance::where('id', $product_pack->id)->first();

                if (!$product_finance) {
                    $collect_error[] = "Finance data for {$product_pack->code} - {$product_pack->name} not found!";
                    continue;
                }

                // Cek apakah harga berubah
                if ($product_finance->buying_price_usd_unit != $row['harga_beli'] || 
                    $product_finance->selling_price_usd_unit != $row['harga_jual']) {

                    // Simpan harga lama ke log price
                    DB::table('product_price_logs')->insert([
                        'product_finance_id' => $product_finance->id,
                        'buying_price_usd_unit' => $product_finance->buying_price_usd_unit,
                        'selling_price_usd_unit' => $product_finance->selling_price_usd_unit,
                        'year' => 2024,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Update harga baru
                    $product_finance->update([
                        'buying_price_usd_unit' => $row['harga_beli'],
                        'selling_price_usd_unit' => $row['harga_jual'],
                        'updated_year' => 2025,
                    ]);

                    $collect_success[] = "{$product_pack->code} - {$product_pack->name} updated successfully.";
                } else {
                    $collect_error[] = "{$product_pack->code} - {$product_pack->name} has no price change.";
                }
            }

            $this->error = !empty($collect_error) ? $collect_error : ['No failed import.'];
            $this->success = !empty($collect_success) ? $collect_success : ['No successful import.'];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error = [$e->getMessage()];
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}