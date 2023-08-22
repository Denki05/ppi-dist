<?php

namespace App\Imports\Master;

use App\Entities\Master\BrandReference;
use App\Entities\Master\Product;
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

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            $searah = SubBrandReference::where('name', $row['searah'])->first();
            $kategori = ProductCategory::where('name', $row['kategori'])->first();
            $type = ProductType::where('name', $row['type'])->first();
            $vendor = Vendor::where('name', $row['vendor'])->first();
            $warehouse = Warehouse::where('name', $row['warehouse'])->first();
            $packaging = Packaging::where('pack_name', $row['packaging'])->first();

            Product::create([
                'sub_brand_reference_id' => $searah->id,
                'category_id' => $kategori->id,
                'packaging_id' => $kategori->id,
                'type_id' => $type->id,
                'vendor_id' => $vendor->id,
                'brand_name' => $row['brand_name'],
                'code' => $row['code'],
                'name' => $row['name'],
                'material_code' => $row['material_code'],
                'material_name' => $row['material_name'],
                'gender' => $row['gender'],
                'description' => $row['description'],
                'default_quantity' => $row['qty'],
                'default_warehouse_id' => $warehouse->id,
                'buying_price' => $row['buying_price'],
                'selling_price' => $row['selling_price'],
                'status' => Product::STATUS['ACTIVE'],

            ]);
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
