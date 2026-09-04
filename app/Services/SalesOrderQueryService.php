<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Entities\Master\Product;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrderKontrak;
use App\Entities\Penjualan\SalesOrderKontrakItem;
use Illuminate\Http\Request;

class SalesOrderQueryService
{
    public function getProductPack(Request $request)
    {
        $brand = $request->id;
        if (!$brand) {
            return ['success' => false, 'code' => 400, 'message' => 'Brand tidak valid'];
        }

        $packagingId = $request->packaging_id;

        $products = Product::query()
            ->where('master_products.brand_name', $brand)
            ->where('master_products.on_order', 1)
            ->join('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->join('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('master_product_types', 'master_products_packaging.type_id', '=', 'master_product_types.id')
            ->leftJoin('master_warehouses', 'master_products_packaging.warehouse_id', '=', 'master_warehouses.id')
            ->when(!empty($packagingId), function ($query) use ($packagingId) {
                $query->where('master_packaging.id', $packagingId);
            })
            ->select([
                'master_products_packaging.id as id',
                'master_products_packaging.code as ProductCode',
                'master_products_packaging.name as productName',
                'master_products_packaging.price as productPrice',
                'master_packaging.id as productPackagingID',
                'master_packaging.pack_name as productPackaging',
                'master_warehouses.name as warehouseName',
                'master_product_types.name as typeName',
            ])
            ->orderBy('master_products_packaging.code')
            ->limit(500)
            ->get();

        if ($products->isEmpty()) {
            return ['success' => false, 'code' => 204, 'message' => 'Produk tidak ditemukan'];
        }

        $data = $products->map(function ($p) {
            if (!$p->ProductCode || !$p->productName || !$p->productPackaging) {
                return null;
            }
            return [
                'id' => $p->id,
                'code' => $p->ProductCode,
                'name' => $p->productName,
                'price' => $p->productPrice,
                'packName' => $p->productPackaging,
                'packID' => $p->productPackagingID,
                'warehouseName' => $p->warehouseName,
                'typeName' => $p->typeName,
            ];
        })->filter()->values();

        return [
            'success' => true,
            'code' => 200,
            'data' => $data,
            'count' => $data->count(),
        ];
    }

    public function searchKontrak(Request $request, $id, $merek)
    {
        $validatedData = $request->validate([
            'q' => 'nullable|string|max:255',
        ]);

        if (!is_numeric($id) || empty($merek)) {
            return [
                'success' => false,
                'code' => 422,
                'message' => 'Invalid request data.',
                'errors' => [
                    'id' => 'The ID must be a number.',
                    'merek' => 'The brand name is required.'
                ]
            ];
        }

        $sales_kontrak = SalesOrderKontrak::where('penjualan_so_kontrak.status', 2)
            ->where('penjualan_so_kontrak.customer_other_address_id', $id)
            ->where('master_products.brand_name', $merek)
            ->when($request->has('q'), function ($query) use ($validatedData) {
                $query->where('master_products_packaging.name', 'LIKE', '%' . $validatedData['q'] . '%');
            })
            ->leftJoin('penjualan_so_kontrak_item', 'penjualan_so_kontrak.id', '=', 'penjualan_so_kontrak_item.so_kontrak_id')
            ->leftJoin('master_products_packaging', 'penjualan_so_kontrak_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_products', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->leftJoin('penjualan_so_kontrak_log', 'penjualan_so_kontrak.id', '=', 'penjualan_so_kontrak_log.so_kontrak_id')
            ->select(
                'penjualan_so_kontrak.id',
                'penjualan_so_kontrak.code AS kontrak_code',
                'master_products_packaging.code AS product_code',
                'master_products_packaging.name AS product_name',
                'penjualan_so_kontrak_item.qty AS product_qty',
                'penjualan_so_kontrak_item.qty_sent AS product_qty_sent',
                DB::raw('SUM(penjualan_so_kontrak_log.qty_worked) AS total_qty_worked')
            )
            ->groupBy(
                'penjualan_so_kontrak.id',
                'penjualan_so_kontrak.code',
                'master_products_packaging.code',
                'master_products_packaging.name',
                'penjualan_so_kontrak_item.qty',
                'penjualan_so_kontrak_item.qty_sent'
            )
            ->havingRaw('SUM(penjualan_so_kontrak_log.qty_worked) < penjualan_so_kontrak_item.qty')
            ->get();

        $results = $sales_kontrak->map(function ($row) {
            return [
                'id' => $row->id,
                'text' => "{$row->product_code} - {$row->product_name} / ({$row->kontrak_code})",
                'product_qty' => $row->product_qty,
                'product_qty_sent' => $row->product_qty_sent,
                'total_qty_worked' => $row->total_qty_worked
            ];
        });

        return ['success' => true, 'code' => 200, 'data' => $results];
    }

    public function getProductKontrak($soKontrakId)
    {
        $data = [];
        $sales_kontrak_item = SalesOrderKontrakItem::where('penjualan_so_kontrak_item.so_kontrak_id', $soKontrakId)
            ->leftJoin('master_products_packaging', 'penjualan_so_kontrak_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('penjualan_so_kontrak', 'penjualan_so_kontrak_item.so_kontrak_id', '=', 'penjualan_so_kontrak.id')
            ->select(
                'master_products_packaging.name AS product_name',
                'master_products_packaging.code AS product_code',
                'penjualan_so_kontrak.id AS kontrak_id',
                'penjualan_so_kontrak_item.price AS product_price',
                'penjualan_so_kontrak_item.disc_usd AS product_disc',
                'penjualan_so_kontrak_item.product_packaging_id AS product_id',
                'master_packaging.id AS packaging_id',
                'master_packaging.pack_name AS packaging_name'
            )
            ->get();

        foreach ($sales_kontrak_item AS $row) {
            $data[] = [
                'product_id' => $row->product_id,
                'product_code' => $row->product_code,
                'product_name' => $row->product_name,
                'product_price' => $row->product_price,
                'product_disc' => $row->product_disc,
                'packaging_id' => $row->packaging_id,
                'packaging_name' => $row->packaging_name,
                'kontrak_id' => $row->kontrak_id,
            ];
        }

        return ['success' => true, 'data' => $data];
    }

    public function viewSalesOrderDetail($id)
    {
        $so_header = SalesOrder::join('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->select(
                'penjualan_so.so_date',
                'penjualan_so.so_code',
                'penjualan_so.idr_rate',
                'penjualan_so.catatan as disc_percent',
                'penjualan_so.note',
                'penjualan_so.type_transaction',
                'master_customer_other_addresses.name as customer_name',
                'master_customer_other_addresses.address as customer_address',
                'master_customer_other_addresses.text_kota as customer_city',
                'master_customer_other_addresses.text_provinsi as customer_province'
            )
            ->where('penjualan_so.id', $id)
            ->where('penjualan_so.approval_mou', 1)
            ->first();

        if (!$so_header) {
            return ['success' => false, 'code' => 404, 'message' => 'SO tidak ditemukan'];
        }

        $so_items = DB::table('penjualan_so_item')
            ->join('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->join('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->where('penjualan_so_item.so_id', $id)
            ->select(
                'master_products.code as product_code',
                'master_products.name as product_name',
                'master_packaging.pack_name as packaging_name',
                'penjualan_so_item.qty',
                'penjualan_so_item.price',
                'penjualan_so_item.free_product'
            )
            ->get();

        $data = [
            'so_code' => $so_header->so_code,
            'so_date' => $so_header->so_date,
            'idr_rate' => $so_header->idr_rate,
            'disc_percent' => $so_header->disc_percent,
            'note' => $so_header->note,
            'type_transaction' => $so_header->type_transaction,
            'customer_name' => $so_header->customer_name,
            'customer_address' => $so_header->customer_address,
            'customer_city' => $so_header->customer_city,
            'customer_province' => $so_header->customer_province,
            'so_items' => $so_items
        ];

        return ['success' => true, 'code' => 200, 'data' => $data];
    }

    public function getDataSo($id)
    {
        $result = DB::table('penjualan_so')
            ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
            ->select(
                'penjualan_so.*',
                'master_customer_other_addresses.name as customer_name',
                'master_customer_other_addresses.address AS customer_address',
                'master_customer_other_addresses.text_kota AS customer_kota',
                'master_customer_other_addresses.text_provinsi AS customer_provinsi',
            )
            ->where('penjualan_so.id', $id)
            ->first();

        $products = DB::table('penjualan_so_item')
            ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->select(
                'penjualan_so_item.*',
                'master_products_packaging.code AS code',
                'master_products_packaging.name AS name',
                'master_packaging.pack_name AS kemasan'
            )
            ->where('penjualan_so_item.so_id', $id)
            ->get();

        $result->products = $products;

        return ['success' => true, 'data' => $result];
    }

    public function getEditFormData($id, $step)
    {
        $result = SalesOrder::where('id', $id)->first();
        if (empty($result)) {
            return ['success' => false, 'message' => 'SO tidak ditemukan'];
        }

        $customers = \App\Entities\Master\Customer::get();
        $warehouse = \App\Entities\Master\Warehouse::all();
        $sales = \App\Entities\Master\Sales::all();
        $product_category = \App\Entities\Master\ProductCategory::all();
        $brand = \App\Entities\Master\BrandLokal::get();
        $ekspedisi = \App\Entities\Master\Vendor::where('type', 1)->get();
        $packaging = \App\Entities\Master\Packaging::get();
        $rekening = DB::table('rekening')->get();

        $data = [
            'customers' => $customers,
            'warehouse' => $warehouse,
            'sales' => $sales,
            'product_category' => $product_category,
            'brand' => $brand,
            'ekspedisi' => $ekspedisi,
            'result' => $result,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
            'packaging' => $packaging,
            'rekening' => $rekening,
        ];

        if ($step == 2) {
            $data['customer_history'] = $this->getUnpaidInvoices($result);
        }

        return ['success' => true, 'data' => $data];
    }

    public function getUnpaidInvoices($salesOrder)
    {
        $doList = $salesOrder->member->do;
        $invoiceList = [];

        for ($i = 0; $i < sizeof($doList); $i++) {
            $do = $doList[$i];
            if (isset($do->invoicing)) {
                $total_payable = 0;
                for ($j = 0; $j < sizeof($do->invoicing->payable_detail); $j++) {
                    $payable_d = $do->invoicing->payable_detail[$j];
                    $total_payable += $payable_d->total;
                }
                if ($total_payable < $do->invoicing->grand_total_idr) {
                    array_push($invoiceList, $do->invoicing);
                }
            }
        }

        return $invoiceList;
    }

    public function getCreateFormData($step, $member, $brand, $type, $indent, $approval, $note, $kurs, $disc_percent, $disc_idr, $disc_usd, $disc_kemasan, $packaging = null)
    {
        $merek = \App\Entities\Master\BrandLokal::where('brand_name', $brand)->first();
        $products = \App\Entities\Master\Product::all();
        $other_address = \App\Entities\Master\CustomerOtherAddress::find($member);
        $warehouse = \App\Entities\Master\Warehouse::all();
        $ekspedisi = \App\Entities\Master\Ekspedisi::all();
        $sales = \App\Entities\Master\Sales::where('is_active', 1)->get();
        $product_category = \App\Entities\Master\ProductCategory::get();
        $type_transaction = $type;
        $type_indent = $indent;
        $rekenings = SalesOrder::REKENING;
        $approval_mou = $approval;
        $note_so = $note;
        $idr_rate = is_numeric($kurs) ? (float) $kurs : 0;

        $disc = is_numeric($disc_percent) ? (float) $disc_percent : 0;
        $disc_idr_val = is_numeric($disc_idr) ? (float) $disc_idr : 0;
        $disc_usd_val = is_numeric($disc_usd) ? (float) $disc_usd : 0;
        $disc_kemasan_val = is_numeric($disc_kemasan) ? (float) $disc_kemasan : 0;

        $selected_packaging = (!empty($packaging) && is_numeric($packaging))
            ? \App\Entities\Master\Packaging::find($packaging)
            : null;

        $data = [
            'other_address' => $other_address,
            'merek' => $merek,
            'products' => $products,
            'sales' => $sales,
            'warehouse' => $warehouse,
            'ekspedisi' => $ekspedisi,
            'product_category' => $product_category,
            'step' => $step,
            'step_txt' => SalesOrder::STEP[$step],
            'type_transaction' => $type_transaction,
            'type_indent' => $type_indent,
            'rekenings' => $rekenings,
            'approval_mou' => $approval_mou,
            'note_so' => $note_so,
            'idr_rate' => $idr_rate,
            'disc' => $disc,
            'disc_idr' => $disc_idr_val,
            'disc_usd' => $disc_usd_val,
            'disc_kemasan' => $disc_kemasan_val,
            'selected_packaging' => $selected_packaging,
        ];

        return ['success' => true, 'data' => $data];
    }

    public function updateBrandName()
    {
        $sales_order = SalesOrder::leftJoin('penjualan_so_item', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
                                ->select(
                                    'penjualan_so.id as invoice_id',
                                    'penjualan_so.code as invoice',
                                    'penjualan_so.brand_name as brand_invoice',
                                    'penjualan_so.status as status_so',
                                    'penjualan_so_item.product_packaging_id as product_pack',
                                )
                                ->where('penjualan_so.status', 4)
                                ->orWhere('penjualan_so.brand_name', NULL)
                                ->get();

        foreach($sales_order as $row){
            $find = false;

            $product = DB::table('penjualan_so_item')
                            ->select(
                                'master_products_packaging.id as child_id',
                                'master_products.id as parent_id',
                                'master_products.brand_name as brand_name',
                            )
                            ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
                            ->leftJoin('master_products', 'master_products.id', '=', 'master_products_packaging.product_id')
                            ->where('penjualan_so_item.so_id', $row->invoice_id)
                            ->get();

            foreach($product as $item){
                if(!$find){
                    $data = SalesOrder::find($row->invoice_id);
                    $data->brand_name = $item->brand_name;
                    $data->save();
                    $find = true;
                }
            }
        }

        return ['success' => true];
    }

    public function getProductsByBrand($brandName)
    {
        $table = Product::where(function ($query) use ($brandName) {
                    if (!empty($brandName)) {
                        $query->where('brand_name', $brandName);
                    }
                })
                ->selectRaw(
                    'master_products.id as id, 
                    master_products.name as productName, 
                    master_products.code as productCode, 
                    master_products.selling_price as productPrice'
                )
                ->get();

        return ['success' => true, 'data' => $table];
    }

    public function getPackagingByProduct($productId)
    {
        $table = \App\Entities\Master\ProductPack::where(function ($query) use ($productId) {
                    if (!empty($productId)) {
                        $query->where('product_id', $productId);
                    }
                })
                ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                ->leftJoin('master_product_types', 'master_products_packaging.type_id', '=', 'master_product_types.id')
                ->selectRaw(
                    'master_packaging.id, master_packaging.pack_name, master_product_types.name as type'
                )
                ->get();

        return ['success' => true, 'data' => $table];
    }

    public function getCustomerDetail($customerId)
    {
        try {
            $result = \App\Entities\Master\Customer::where('id', $customerId)->first();
            return ['success' => true, 'data' => $result];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getWarehouseDetail($warehouseId)
    {
        try {
            $result = \App\Entities\Master\Warehouse::where('id', $warehouseId)->first();
            return ['success' => true, 'data' => $result];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
