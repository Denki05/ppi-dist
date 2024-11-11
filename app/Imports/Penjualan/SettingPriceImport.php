<?php

namespace App\Imports\Penjualan;

use App\Entities\Master\ProductPack;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Packaging;
use App\Entities\Master\Product;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\Warehouse;
use App\Entities\Penjualan\SettingPriceLog;
use App\Entities\Penjualan\DraftNewPrice;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Validators\Failure;
use DB;

class SettingPriceImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try{

            $collect_error = [];
            $collect_success = [];

            foreach ($rows as $row) {
                // $get_packaging = Packaging::where('pack_name', $row['kemasan'])->first();
                // if($get_packaging == null) {
                //     $collect_error[] = $row['kemasan'] . '  "PACKAGING" not found';
                //     break;
                // }

                $kategori = ProductCategory::where('name', $row['kategori'])->first();
                if($kategori == null) {
                    $collect_error[] = $row['kategori'] . '  "CATEGORY" not found';
                    break;
                }

                // $searah = SubBrandReference::where('name', $row['searah'])->first();
                // if($searah == null) {
                //     $collect_error[] = $row['searah'] . '  "SEARAH" not found';
                //     continue;
                // }

                $kemasan = Packaging::where('pack_name', $row['kemasan'])->first();
                if($kemasan == null) {
                    $collect_error[] = $row['kemasan'] . '  "KEMASAN" not found';
                    break;
                }

                if($row['brand_name'] == 'Senses' || $row['brand_name'] == 'SENSES'){
                    $id_product = explode(' ', $row['kode_produk']);

                    $draft_price = new DraftNewPrice;
                    $draft_price->id = $id_product.'-'.$kemasan->id;
                    // $draft_price->kode_bahan = $row['kode_bahan'] ?? null;
                    // $draft_price->nama_bahan = $row['nama_bahan'] ?? null;
                    $draft_price->packaging_id = $kemasan->id;
                    $draft_price->kode_produk = $row['kode_produk'];
                    $draft_price->nama_produk = $row['nama_produk'];
                    $draft_price->brand_name = $row['brand_name'];
                    $draft_price->category_id = $kategori->id;
                    // $draft_price->brand_reference_id = $searah->brand_reference->id ?? null;
                    // $draft_price->sub_brand_reference_id = $searah->id ?? null;
                    $draft_price->harga_lama = $row['harga_lama'];
                    $draft_price->harga_baru = $row['harga_baru'];
                    $draft_price->status = DraftNewPrice::STATUS['ACTIVE'];
                    $draft_price->save();
                } else {
                    $draft_price = new DraftNewPrice;
                    $draft_price->id = $row['kode_produk'].'-'.$kemasan->id;
                    // $draft_price->kode_bahan = $row['kode_bahan'] ?? null;
                    // $draft_price->nama_bahan = $row['nama_bahan'] ?? null;
                    $draft_price->packaging_id = $kemasan->id;
                    $draft_price->kode_produk = $row['kode_produk'];
                    $draft_price->nama_produk = $row['nama_produk'];
                    $draft_price->brand_name = $row['brand_name'];
                    $draft_price->category_id = $kategori->id;
                    // $draft_price->brand_reference_id = $searah->brand_reference->id ?? null;
                    // $draft_price->sub_brand_reference_id = $searah->id ?? null;
                    $draft_price->harga_lama = $row['harga_lama'];
                    $draft_price->harga_baru = $row['harga_baru'];
                    $draft_price->status = DraftNewPrice::STATUS['ACTIVE'];
                    $draft_price->save();
                }

                $collect_success[] = $row['kode_produk'].' - '.$row['nama_produk'];
            }

            if (!$collect_success) {
                $collect_success[] = 'No successful import.';
            }

            if (!$collect_error) {
                $collect_error[] = 'No failed import.';
            }

            $this->error = $collect_error;
            $this->success = $collect_success;

            DB::commit();
        }catch (\Exception $e) {
            dd($e);
            $this->error = $e->getMessage();
            DB::rollBack();
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}