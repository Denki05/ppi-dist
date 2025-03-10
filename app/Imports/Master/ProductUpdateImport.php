<?php

namespace App\Imports\Master;

use App\Entities\Master\BrandReference;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
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

class ProductUpdateImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
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

            foreach ($rows as $row) {
                $raw_id = $row['id']; 
                $product_id = explode("-", $raw_id)[0]; // Assuming ID is the first part of the string

                // Find product using extracted ID
                $product = Product::where('id', $product_id)->first();

                if (!$product) {
                    $collect_error[] = 'Product with ID ' . $product_id . ' not found.';
                    continue; // Continue processing other rows
                }

                // Validate the ratio format (ensure it follows "X:Y" pattern)
                if (!empty($row['ratio']) && preg_match('/^\d+:\d+$/', $row['ratio'])) {
                    $product->ratio = $row['ratio']; // Store as string (e.g., "1:10")
                    $product->save();
                    $collect_success[] = "Updated Product: {$product->code} - {$product->name}, Ratio: {$row['ratio']}";
                } else {
                    $collect_error[] = "Invalid or missing ratio for product ID " . $product_id;
                }
            }

            // Provide feedback for success and errors
            if (empty($collect_success)) {
                $collect_success[] = 'No successful import.';
            }

            if (empty($collect_error)) {
                $collect_error[] = 'No failed import.';
            }

            $this->error = $collect_error;
            $this->success = $collect_success;

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error = ['Error: ' . $e->getMessage()];
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
