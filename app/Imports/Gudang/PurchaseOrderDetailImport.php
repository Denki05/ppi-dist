<?php

namespace App\Imports\Gudang;

use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Master\Product;
use App\Entities\Master\Packaging;
use App\Entities\Master\BrandLokal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use DB;
use Auth;

class PurchaseOrderDetailImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function  __construct($purchase_order_id)
    {
        $this->purchase_order_id = $purchase_order_id;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try{

            $collect_error = [];
            $collect_success = [];

            $purchase_order = PurchaseOrder::find($this->purchase_order_id);
            if($purchase_order == null) {
                $collect_error = array('Something went wrong, please reload page!');
            } else {
                foreach ($rows as $row) {
                    $product = Product::where('name', $row['product'])->first();
                    if($product == null) {
                        $collect_error = array('PRODUCT'.$row['product'].' NOT FOUND : all import aborted!');
                        break;
                    }
    
                    $packaging = Packaging::where('pack_name', $row['packaging'])->first();
                    if($packaging == null) {
                        $collect_error = array('PACKAGING'.$row['packaging'].' NOT FOUND : all import aborted!');
                        break;
                    }
    
                    $brand = BrandLokal::where('brand_name', $row['brand'])->first();
                    if($brand == null) {
                        $collect_error = array('BRAND'.$row['brand'].' NOT FOUND : all import aborted!');
                        break;
                    }
    
                    $quantity = $row['qty'] ?? 0;
                    $note = $row['note'] ?? null;
                    $customer = $row['customer'] ?? null;
                    
                    $purchase_order_detail = new PurchaseOrderDetail;
                    $purchase_order_detail->po_id = $purchase_order->id;
                    $purchase_order_detail->brand_lokal_id = $brand->id;
                    $purchase_order_detail->product_packaging_id = implode("-", [$product->id, $packaging->id]);
                    $purchase_order_detail->quantity = $quantity;
                    $purchase_order_detail->packaging_id = $packaging->id;
                    $purchase_order_detail->note_produksi = $note;
                    $purchase_order_detail->note_repack = $customer;
                    $purchase_order_detail->created_by = Auth::id();
                    $purchase_order_detail->save();

                    $collect_success[] = $product->code.'-'.$product->name;
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
