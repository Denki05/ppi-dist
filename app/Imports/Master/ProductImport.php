<?php

namespace App\Imports\Master;

use App\Entities\Master\BrandReference;
use App\Entities\Master\Product;
use App\Entities\Master\ProductChild;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\ProductType;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\Unit;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Packaging;
use App\Entities\Master\Vendor;
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

class ProductImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
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
                $searah = SubBrandReference::where('name', $row['searah'])->first();
                if($searah == null) {
                    $collect_error[] = $row['searah'] . '  "SEARAH" not found';
                    break;
                }

                $kategori = ProductCategory::where('name', $row['kategori'])->first();
                if($kategori == null) {
                    $collect_error[] = $row['kategori'] . '  "CATEGORY" not found';
                    break;
                }

                $type = ProductType::where('name', $row['type'])->first();

                $vendor = Vendor::where('name', $row['vendor'])->first();
                if($vendor == null) {
                    $collect_error[] = $row['vendor'] . '  "VENDOR" not found';
                    break;
                }

                // $warehouse = Warehouse::where('name', $row['warehouse'])->first();
                // if($warehouse == null) {
                //     $collect_error[] = $row['warehouse'] . '  "WAREHOUSE" not found';
                //     break;
                // }

                // $packaging = Packaging::where('pack_name', $row['packaging'])->first();
                // if($packaging == null) {
                //     $collect_error[] = $row['packaging'] . '  "PACKAGING" not found';
                //     break;
                // }

                if($row['merek'] == 'Senses' || $row['merek'] == 'SENSES'){
                    $id_product = explode(' ', $row['code']);
                    
                    $product = new Product;
                    $product->id = $id_product[1];
                    $product->sub_brand_reference_id = $searah->id;
                    $product->category_id = $kategori->id;
                    $product->type_id = $type->id ?? null;
                    $product->vendor_id = $vendor->id;
                    $product->brand_name = $row['merek'];
                    $product->code = $row['code'];
                    $product->name = $row['name'];
                    $product->material_code = $row['material_code'];
                    $product->material_name = $row['material_name'];
                    $product->gender = $row['gender'];
                    $product->description = $row['description'];
                    $product->buying_price = $row['buying_price'];
                    $product->selling_price = $row['selling_price'];
                    $product->status = Product::STATUS['ACTIVE'];
                    // $product->save();
                    if($product->save()){
                        $warehouse = Warehouse::where('name', 'Gudang Araya')->first();
                        $pecah_kemasan = explode(',', $row['packaging']);
                        $kemasan = Packaging::where('pack_name', $pecah_kemasan)->get();


                        foreach($pecah_kemasan as $value){
                                $child_product = new ProductChild;
                                $child_product->id = $product->id.'.'.Packaging::where('pack_name', $value)->pluck('id')->first();
                                $child_product->product_id = $product->id;
                                $child_product->warehouse_id = $warehouse->id;
                                $child_product->material_code = $row['material_code'];
                                $child_product->material_name = $row['material_name'];
                                $child_product->code = $row['code'];
                                $child_product->name = $row['name'];
                                $child_product->price = $row['selling_price'];
                                $child_product->stock = 1000;
                                $child_product->gender = $row['gender'];
                                $child_product->note = $row['description'];
                                $child_product->status = ProductChild::STATUS['ACTIVE'];
                                $child_product->save();

                                $min_stock = new ProductMinStock;
                                $min_stock->product_pack_id = $child_product->id;
                                $min_stock->warehouse_id = $warehouse->id;
                                $min_stock->unit_id = 1;
                                $min_stock->quantity = $child_product->stock;
                                $min_stock->selling_price = $child_product->price;
                                $min_stock->save();
                        }
                    }
                }else{
                    $product = new Product;
                    $product->id = $row['code'];
                    $product->sub_brand_reference_id = $searah->id;
                    $product->category_id = $kategori->id;
                    $product->type_id = $type->id ?? null;
                    $product->vendor_id = $vendor->id;
                    $product->brand_name = $row['merek'];
                    $product->code = $row['code'];
                    $product->name = $row['name'];
                    $product->material_code = $row['material_code'];
                    $product->material_name = $row['material_name'];
                    $product->gender = $row['gender'];
                    $product->description = $row['description'];
                    $product->buying_price = $row['buying_price'];
                    $product->selling_price = $row['selling_price'];
                    $product->status = Product::STATUS['ACTIVE'];
                    // $product->save();
                    if($product->save()){
                        $warehouse = Warehouse::where('name', 'Gudang Araya')->first();
                        $pecah_kemasan = explode(',', $row['packaging']);
                        $kemasan = Packaging::where('pack_name', $pecah_kemasan)->get();


                        foreach($pecah_kemasan as $value){
                                $child_product = new ProductChild;
                                $child_product->id = $product->id.'.'.Packaging::where('pack_name', $value)->pluck('id')->first();
                                $child_product->product_id = $product->id;
                                $child_product->warehouse_id = $warehouse->id;
                                $child_product->material_code = $row['material_code'];
                                $child_product->material_name = $row['material_name'];
                                $child_product->code = $row['code'];
                                $child_product->name = $row['name'];
                                $child_product->price = $row['selling_price'];
                                $child_product->stock = 1000;
                                $child_product->gender = $row['gender'];
                                $child_product->note = $row['description'];
                                $child_product->status = ProductChild::STATUS['ACTIVE'];
                                $child_product->save();

                                $min_stock = new ProductMinStock;
                                $min_stock->product_pack_id = $child_product->id;
                                $min_stock->warehouse_id = $warehouse->id;
                                $min_stock->unit_id = 1;
                                $min_stock->quantity = $child_product->stock;
                                $min_stock->selling_price = $child_product->price;
                                $min_stock->save();

                                // dd($min_stock);
                                
                        }
                    }
                }

                $collect_success[] = $product->code.'-'.$product->name;
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
            // dd($e);
            $this->error = $e->getMessage();
            DB::rollBack();
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
