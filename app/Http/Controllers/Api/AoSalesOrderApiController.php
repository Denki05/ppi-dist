<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Account\Superuser;
use App\Repositories\CodeRepo; // ASUMSI: sesuaikan namespace CodeRepo yang asli
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AoSalesOrderApiController extends Controller
{
    /**
     * List brand untuk dropdown di form AO.
     * GET /api/ao/so-awal/brands
     */
    public function brands()
    {
        $brands = BrandLokal::select('id', 'brand_name')
            ->orderBy('brand_name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $brands,
        ]);
    }

    /**
     * List produk (regular, non-kontrak) untuk brand tertentu.
     * GET /api/ao/so-awal/products?brand=GCF
     *
     * Query-nya sengaja disamakan persis dengan SalesOrderController::get_product_pack()
     * supaya harga & packaging yang muncul di AO konsisten dengan modul transaksi.
     */
    public function products(Request $request)
    {
        $brand = $request->query('brand');

        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Parameter brand wajib diisi'], 400);
        }

        $products = Product::query()
            ->where('master_products.brand_name', $brand)
            ->where('master_products.on_order', 1)
            ->join('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->join('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('master_product_types', 'master_products_packaging.type_id', '=', 'master_product_types.id')
            ->leftJoin('master_warehouses', 'master_products_packaging.warehouse_id', '=', 'master_warehouses.id')
            ->select([
                'master_products_packaging.id as id',
                'master_products_packaging.code as ProductCode',
                'master_products_packaging.name as productName',
                'master_products_packaging.price as productPrice',
                'master_packaging.pack_name as productPackaging',
                'master_warehouses.name as warehouseName',
                'master_product_types.name as typeName',
            ])
            ->orderBy('master_products_packaging.code')
            ->limit(500)
            ->get();

        $data = $products->map(function ($p) {
            if (!$p->ProductCode || !$p->productName || !$p->productPackaging) {
                return null;
            }
            return [
                'id'            => $p->id,
                'code'          => $p->ProductCode,
                'name'          => $p->productName,
                'price'         => $p->productPrice,
                'packName'      => $p->productPackaging,
                'warehouseName' => $p->warehouseName,
                'typeName'      => $p->typeName,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Create SO Awal dari AO (produk reguler saja, tanpa kontrak/indent).
     * POST /api/ao/so-awal/store
     *
     * Payload:
     * {
     *   "customer_id": 123,
     *   "brand_name": "GCF",
     *   "type_transaction": 1,      // 1 CASH, 2 TEMPO, 3 MARKETPLACE, 4 COD
     *   "note": "opsional",
     *   "pic_username": "budi",     // opsional, dipakai untuk created_by
     *   "items": [
     *     { "product_packaging_id": 55, "price": 100000, "qty": 2, "disc_usd": 0, "free_product": 0 }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'                   => ['required', 'regex:/^\d+\.\d+$/'],
            'brand_name'                    => 'required|string',
            'type_transaction'               => 'required|integer|in:1,2,3,4',
            'items'                          => 'required|array|min:1',
            'items.*.product_packaging_id'   => 'required',
            'items.*.qty'                    => 'required|numeric|min:0.01',
            'items.*.price'                  => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $ppIds = collect($request->items)->pluck('product_packaging_id')->all();
        if (count($ppIds) !== count(array_unique($ppIds))) {
            return response()->json([
                'success' => false,
                'message' => 'Item produk duplikat, tidak boleh ada produk yang sama 2x',
            ], 422);
        }

        [$customerIdRaw, $memberIndexRaw] = explode('.', $request->customer_id, 2);
        $customerId  = (int) $customerIdRaw;
        $memberIndex = (int) $memberIndexRaw;

        if ($customerId <= 0 || $memberIndex <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Format customer_id tidak valid: {$request->customer_id}",
            ], 422);
        }

        $otherAddress = CustomerOtherAddress::where('customer_id', $customerId)
            ->orderBy('id', 'asc')
            ->skip($memberIndex - 1)
            ->first();

        if (!$otherAddress) {
            return response()->json([
                'success' => false,
                'message' => "Member ke-{$memberIndex} untuk customer_id {$customerId} tidak ditemukan.",
            ], 422);
        }

        $createdBy = config('services.ao_api.default_superuser_id');
        if ($request->filled('pic_username')) {
            $superuser = Superuser::where('username', $request->pic_username)->first();
            if ($superuser) {
                $createdBy = $superuser->id;
            }
        }

        try {
            DB::beginTransaction();

            $so = new SalesOrder;
            $so->so_code                     = CodeRepo::generateSoAwal();
            $so->brand_name                  = $request->brand_name;
            $so->customer_id                 = $otherAddress->customer_id;
            $so->customer_other_address_id   = $otherAddress->id;
            $so->type_transaction            = $request->type_transaction;
            $so->so_for                      = 1;
            $so->so_date                     = null;
            $so->type_so                     = 'nonppn';
            $so->approval_mou                = 0;
            $so->idr_rate                    = 0;
            $so->catatan                     = '0';
            $so->note                        = $request->note;
            $so->is_proforma                 = 0;
            $so->code                        = null;
            $so->status                      = 1;
            $so->so_indent                   = SalesOrder::INDENT['NO'];
            $so->condition                   = 1;
            $so->payment_status              = 0;
            $so->count_rev                   = 0;
            $so->created_by                  = $createdBy;
            $so->save();

            // 🔎 DIAGNOSTIK SEMENTARA: log detail SO yang baru saja dibuat.
            // Ini akan kasih tau kita persis kenapa id-nya 0 (kalau memang masih 0).
            Log::info('AO SO Awal DEBUG - after save', [
                'so_id_attribute' => $so->id,
                'so_getKey'       => $so->getKey(),
                'so_exists'       => $so->exists,
                'so_wasRecentlyCreated' => $so->wasRecentlyCreated,
                'so_so_code'      => $so->so_code,
                'so_all_attrs'    => $so->getAttributes(),
            ]);

            if (!$so->id || $so->id == 0) {
                DB::rollBack();
                Log::error('AO SO Awal: id tidak valid setelah save()', ['so' => $so->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate ID SO Header, proses dibatalkan sebelum insert item.',
                    'debug_so_id' => $so->id,
                ], 500);
            }

            foreach ($request->items as $item) {
                $baseProductPackagingId = $item['product_packaging_id'];
                $product = ProductPack::where('id', $item['product_packaging_id'])->first();

                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Produk dengan id {$item['product_packaging_id']} tidak ditemukan.",
                    ], 422);
                }

                if (strpos($product->id, '_1') !== false) {
                    $baseProduct = ProductPack::where('id', str_replace('_1', '', $product->id))->first();
                    if ($baseProduct) {
                        $baseProductPackagingId = $baseProduct->id;
                    }
                }

                $detail                         = new SalesOrderItem;
                $detail->so_id                  = $so->id;
                $detail->product_packaging_id   = $baseProductPackagingId;
                $detail->price                  = $item['price'];
                $detail->qty                    = $item['qty'];
                $detail->disc_usd               = $item['disc_usd'] ?? 0;
                $detail->packaging_id           = $item['packaging_id'] ?? $product->packaging_id;
                $detail->free_product           = $item['free_product'] ?? 0;
                $detail->kontrak                = 0;
                $detail->created_by             = $createdBy;
                $detail->status                 = 1;
                $detail->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'SO Awal berhasil dibuat',
                'data'    => [
                    'so_id'   => $so->id,
                    'so_code' => $so->so_code,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AO SO Awal creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat SO Awal',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}