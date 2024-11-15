<?php

namespace App\Imports\Accounting;

use App\Entities\Master\BrandLokal;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\Entities\Master\ProductFinance;
use App\Entities\Master\Mitra;
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

class ProductFinanceImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
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

            foreach ($rows as $row) 
            {
                // dd($row['id']);

                // product pack
                $product_pack = ProductPack::where('id', $row['id'])->first();
                if($product_pack == null) {
                    $collect_error[] = $row['id'] . '  "PRODUCT" not found';
                    break;
                }

                // seacrh master product
                $product = Product::where('id', $product_pack->product_id)->first();

                //search kemasan 
                $kemasan = Packaging::where('pack_name', $row['kemasan'])->first();

                $brand = BrandLokal::where('brand_name', $row['brand'])->first();
                if($brand == null) {
                    $collect_error[] = $row['brand'] . '  "BRAND" not found';
                    break;
                }

                $mitra = Mitra::where('name', $row['mitra'])->first();
                if($mitra == null) {
                    $collect_error[] = $row['mitra'] . '  "MITRA" not found';
                    break;
                }

                if($product_pack->product_finance_tax == 0){
                    $product_finance = new ProductFinance;

                    $product_finance->id = $row['id'];
                    $product_finance->brand_name = $brand->brand_name;
                    $product_finance->code_product = $product->code;
                    $product_finance->name_product = $product->name;
                    $product_finance->mitra_id = $mitra->id;
                    $product_finance->product_id = $product->id;
                    $product_finance->packaging_id = $kemasan->id;
                    $product_finance->buying_price_usd_unit = $row['harga_beli'];
                    $product_finance->selling_price_usd_unit = $row['harga_jual'];
                    $product_finance->status = ProductFinance::STATUS['ACTIVE'];
                    if($product_finance->save()){
                        // Update data product pack
                        $get_product = ProductPack::where('id', $product_finance->id)->get();

                        foreach($get_product as $val){
                            $val->product_finance_tax = 1;
                            $val->save();
                        }
                    }

                    $collect_success[] = $product_pack->code.' - '.$product_pack->name. ' / ' .$kemasan->pack_name;

                }elseif($product_pack->product_finance_tax == 1){
                    $collect_error[] =  $product_pack->code.' - '.$product_pack->name. ' / ' .$kemasan->pack_name .' '.'found duplicate in Database!';
                }
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
