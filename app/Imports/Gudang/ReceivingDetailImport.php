<?php

namespace App\Imports\Gudang;;

use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Packaging;
use App\Entities\Gudang\PurchaseOrder;
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
use Carbon\Carbon;

HeadingRowFormatter::default('none');

class ReceivingDetailImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;
    public $receiving_id;

    public function  __construct($receiving_id)
    {
        $this->receiving_id = $receiving_id;
    }

    public function collection(Collection $rows)
    {
        // dd("asssss");
        DB::beginTransaction();

        try {
            $collect_error = [];
            $collect_success = [];
            $data = [];
            // dd($rows);
            foreach ($rows as $row) {
                // foreach($rowTmp as $row){
                
                    $ppb = null;
                    $packaging = Packaging::where('pack_name', $row['packaging'])->pluck('id')->first();
                    $product = ProductPack::where('name', $row['variant'])->where('packaging_id', $packaging)->first();
                    $qty = 0;
                    $ppb_detail = null;
                    if($row['variant'] != ""){
                        if (array_key_exists($row['PO CODE'], $data)) {
                            if (array_key_exists($row['variant'], $data[$row['PO CODE']]['product'])) {
                                $qty = $data[$row['PO CODE']]['product'][$row['variant']]['quantity'];
                                // $data[$row['PPB Number']]['product'][$row['SKU']]['quantity'] = $qty + $row['quantity'];
                            } else {
                                $ppb = PurchaseOrder::where('code', $row['PO CODE'])->first();
                                // DD($ppb);
                                
                                if($ppb){
                                    if($ppb->status == PurchaseOrder::STATUS['ACTIVE']){
                                        $collect_error[] = 'PURCHASE ORDER '. $row['PO CODE'] .' Not Approve, please approve';
                                        continue;
                                    }
                                    
                                    if($ppb->status == PurchaseOrder::STATUS['DELETED']){
                                        $collect_error[] = 'PURCHASE ORDER '. $row['PO CODE'] .' has been deleted';
                                        continue;
                                    }
                                } else {
                                    $collect_error[] = 'PURCHASE ORDER '. $row['PO CODE'] .' Not Found';
                                    continue;
                                }
                                // DD($ppb);
                                foreach($ppb->purchase_order_detail as $detail){
                                    $receiving_detail = ReceivingDetail::where('po_detail_id', $detail->id)->get();
                                    
                                    $qty_receiving = $detail->quantity;
                                    foreach($receiving_detail as $item){
                                        if($item->receiving->status == 2){
                                            $qty_receiving -= $item->total_reject_ri($item->id);
                                            $qty_receiving -= $item->total_quantity_ri;
                                        }
                                    }
                                    
                                    if($product->id == $detail->product_packaging_id){
                                        $qty = $qty_receiving;
                                        $ppb_detail = $detail->id;
                                    }
                                }

                                $data[$row['PO CODE']]['product'][$row['variant']] = [
                                    'product_packaging_id' => $product->id,
                                    'quantity'      => $qty,
                                    'po_detail_id'  => $ppb_detail,
                                    'sj_po'         => $row['no sj'],
                                    'note'          => $row['note']
                                ];
                                
                                // DD($data);
                            }
                        } else {
                            $ppb = PurchaseOrder::where('code', $row['PO CODE'])->first();
                                
                            // DD($ppb);
                            if($ppb){
                                if($ppb->status == PurchaseOrder::STATUS['ACTIVE']){
                                    $collect_error[] = 'PURCHASE ORDER '. $row['PO CODE'] .' Not Approve, please approve';
                                    continue;
                                }
                                
                                if($ppb->status == PurchaseOrder::STATUS['DELETED']){
                                    $collect_error[] = 'PURCHASE ORDER '. $row['PO CODE'] .' has been deleted';
                                    continue;
                                }
                            } else {
                                $collect_error[] = 'PURCHASE ORDER '. $row['PO CODE'] .' Not Found';
                                continue;
                            }
                            foreach($ppb->purchase_order_detail as $detail){
                                if($product->id == $detail->product_packaging_id){
                                    $receiving_detail = ReceivingDetail::where('po_detail_id', $detail->id)->get();
                                    
                                    $qty_receiving = $detail->quantity;
                                    foreach($receiving_detail as $item){
                                        if($item->receiving->status == 2){
                                            $qty_receiving -= $item->total_reject_ri($item->id);
                                            $qty_receiving -= $item->total_quantity_ri;
                                        }
                                    }
                                    
                                    if($product->id == $detail->product_packaging_id){
                                        $qty = $qty_receiving;
                                        $ppb_detail = $detail->id;
                                    }
                                }
                            }
                            $data[$row['PO CODE']]['product'][$row['variant']] = [
                                'product_packaging_id' => $product->id,
                                'quantity'      => $qty,
                                'po_detail_id'  => $ppb_detail,
                                'sj_po'         => $row['no sj'],
                                'note'          => $row['note'],
                            ];

                            $data[$row['PO CODE']]['info'] = [
                                'po_id' => $ppb->id,
                                
                            ];
                        }     
                    }
            }

            // DD($data);
            if ($data) {
                // dd($data);
                foreach ($data as $key => $value) {
                    // foreach($value as )
                    
                    $receiving_detail = ReceivingDetail::where('receiving_id', $key)->first();

                    if (is_null($receiving_detail)) {
                        // VERIFIED DATA

                        if ($value['product']) {
                            $found_error_product = false;
                            foreach ($value['product'] as $key_product => $value_product) {
                                $cek_product = ProductPack::where('id', $value_product['product_packaging_id'])->first();
                                if ($cek_product == null) {
                                    $collect_error[] = $key . ' : VARIANT ' . $key_product . ' not found in database';
                                    $found_error_product = true;
                                    continue;
                                }
                            }
                            if ($found_error_product) {
                                continue;
                            }
                        } else {
                            $collect_error[] = $key . ' : No product column';
                            continue;
                        }

                        foreach ($value['product'] as $key_product => $value_product) {
                            $cek_product = ProductPack::where('id', trim($value_product['product_packaging_id']))->first();
                            if($cek_product){
                                $receiving_detail = new ReceivingDetail();
                                $receiving_detail->receiving_id = $this->receiving_id;
                                $receiving_detail->po_id = $value['info']['po_id'];
                                $receiving_detail->po_detail_id = $value_product['po_detail_id'];
                                $receiving_detail->product_packaging_id = $cek_product->id;
                                $receiving_detail->quantity = $value_product['quantity'];
                                $receiving_detail->sj_po = $value_product['sj_po'];
                                $receiving_detail->note =   $value_product['note'];
                                $receiving_detail->created_by =  Auth::id();
                                $receiving_detail->save();
                            }
                        }
                        // dd($value);
                        $collect_success[] = $key;
                    } else {
                        // dd("as");
                        $collect_error[] = $key . ' : Duplicate Code';
                    }
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
        } catch (\Exception $e) {
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