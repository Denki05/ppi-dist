<?php

namespace App\Imports\Penjualan;

use App\Entities\Master\ProductPack;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Packaging;
use App\Entities\Master\Product;
use App\Entities\Master\Warehouse;
use App\Entities\Penjualan\SettingPriceLog;
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
                $get_product = ProductPack::where('name', $row['name'])->first();
                if($get_product == null) {
                    $collect_error[] = $row['name'] . '  "PRODUCT" not found';
                    break;
                }

                $get_packaging = Packaging::where('pack_name', $row['kemasan'])->first();
                if($get_packaging == null) {
                    $collect_error[] = $row['kemasan'] . '  "PACKAGING" not found';
                    break;
                }

                $update_price = ProductPack::where('id', $get_product->id)
                            ->where('packaging_id', $get_packaging->id)
                            ->update([
                                'price' => $row['price'],
                            ]);

                if($update_price){
                    $log_price = new SettingPriceLog;
                    
                    $log_price->product_packaging_id = $get_product->id;
                    $log_price->price = $get_product->price;
                    $log_price->save();
                }

                $collect_success[] = $get_product->code.'-'.$get_product->name;
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