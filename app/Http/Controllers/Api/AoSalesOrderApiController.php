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
     * Create SO Awal dari AO — full mapping dari modal AO ke penjualan_so + penjualan_so_item.
     * POST /api/ao/so-awal/store
     *
     * Menerima field fleksibel dari AO (string/int) lalu dinormalisasi:
     * - customer_id: "123" (id CustomerOtherAddress langsung) ATAU "123.1" (customer_id.index)
     * - brand_name: string, wajib
     * - type_transaction: 1/2/3/4 ATAU "CASH"/"TEMPO"/"MARKETPLACE"/"COD"
     * - kurs / currency_rate / idr_rate: angka (boleh format "15.500")
     * - disc: disc_percent/discount_value, disc_idr, disc_usd/global_disc_usd, disc_kemasan
     * - approval: 0/1, "YES"/"NO", true/false
     * - so_indent: "YES"/"NO", 1/0, true/false
     * - note / notes / note_so: string opsional
     * - pic_username: opsional untuk created_by
     * - ao_order_number: nomor order dari AO (disimpan ke catatan, anti-duplikat via log)
     * - items[] tiap item menerima alias:
     *     product_packaging_id | product_id | sku,
     *     price | unit_price,
     *     qty,
     *     disc_usd | discount | disc,
     *     packaging_id | packaging,
     *     free_product | free
     *
     * Setelah create, SO langsung di-lanjutkan (status=2 = SO Lanjutan)
     * kecuali indent (tetap status=1 + so_indent=1, biar masuk jalur indent).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'    => 'required|string',
            'brand_name'     => 'required|string',
            'type_transaction' => 'required',
            'items'          => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // --- Normalisasi type_transaction (string/int -> NAMA string untuk DB) ---
        // DB penjualan_so.type_transaction = varchar: CASH/TEMPO/MARKETPLACE/COD
        $typeMap = ['CASH' => 1, 'TEMPO' => 2, 'MARKETPLACE' => 3, 'COD' => 4];
        $typeRaw = $request->input('type_transaction');
        if (is_numeric($typeRaw)) {
            $typeInt = (int) $typeRaw;
            $typeName = array_search($typeInt, $typeMap, true);
        } else {
            $typeName = strtoupper(trim((string) $typeRaw));
            $typeInt = $typeMap[$typeName] ?? 0;
        }
        if (!in_array($typeInt, [1, 2, 3, 4], true)) {
            return response()->json([
                'success' => false,
                'message' => 'type_transaction tidak valid (gunakan CASH/TEMPO/MARKETPLACE/COD atau 1-4)',
            ], 422);
        }

        // --- Normalisasi customer/member ---
        $otherAddress = $this->resolveMember($request->input('customer_id'));
        if (!$otherAddress) {
            return response()->json([
                'success' => false,
                'message' => "Customer/member tidak ditemukan: {$request->input('customer_id')}",
            ], 422);
        }

        // --- Normalisasi kurs ---
        $kursRaw = $request->input('kurs', $request->input('currency_rate', $request->input('idr_rate', 0)));
        $kurs = $this->parseNumber($kursRaw);

        // --- Normalisasi diskon global ---
        $discPercent = $this->parseNumber($request->input('disc_percent', $request->input('discount_value', 0)));
        $discIdr     = $this->parseNumber($request->input('disc_idr', $request->input('global_disc_idr', 0)));
        $discUsd     = $this->parseNumber($request->input('disc_usd', $request->input('global_disc_usd', 0)));
        $discKemasan = $this->parseNumber($request->input('disc_kemasan', $request->input('global_disc_kemasan', 0)));

        // --- Normalisasi approval & indent ---
        $approval = $this->parseBool($request->input('approval', 0));
        $soIndentRaw = $request->input('so_indent', $request->input('is_indent', 0));
        // dukung juga order_type dari AO: biasa/ppn/indent
        $orderType = strtolower(trim((string) $request->input('order_type', '')));
        if ($orderType === 'indent') {
            $soIndentRaw = 1;
        }
        $isIndent = $this->parseBool($soIndentRaw);

        $note = $request->input('note', $request->input('notes', $request->input('note_so')));

        // --- Normalisasi items + cek duplikat ---
        $normItems = [];
        foreach ((array) $request->input('items') as $idx => $it) {
            if (!is_array($it)) {
                return response()->json(['success' => false, 'message' => "Item ke-{$idx} tidak valid"], 422);
            }
            $ppId  = $it['product_packaging_id'] ?? $it['product_id'] ?? $it['sku'] ?? null;
            $price = $it['price'] ?? $it['unit_price'] ?? null;
            $qty   = $it['qty'] ?? null;
            if ($ppId === null || $price === null || $qty === null) {
                return response()->json(['success' => false, 'message' => "Item ke-{$idx} wajib ada product_packaging_id/product_id, price, qty"], 422);
            }
            $disc  = $it['disc_usd'] ?? $it['discount'] ?? $it['disc'] ?? 0;
            $pack  = $it['packaging_id'] ?? $it['packaging'] ?? null;
            $free  = $this->parseBool($it['free_product'] ?? $it['free'] ?? 0);
            if ($free) {
                $disc = 0; // samakan aturan SO awal: free => disc 0
            }
            $normItems[] = [
                'pp_id' => $ppId,
                'price' => $this->parseNumber($price),
                'qty'   => $this->parseNumber($qty),
                'disc'  => $this->parseNumber($disc),
                'pack'  => $pack,
                'free'  => $free ? 1 : 0,
            ];
        }
        if (count($normItems) < 1) {
            return response()->json(['success' => false, 'message' => 'Items kosong'], 422);
        }
        foreach ($normItems as $ni) {
            if ($ni['qty'] < 0.01 || $ni['price'] < 0) {
                return response()->json(['success' => false, 'message' => 'Qty minimal 0.01 dan price minimal 0'], 422);
            }
        }
        $ppIds = collect($normItems)->pluck('pp_id')->map(function ($v) { return (string) $v; })->all();
        if (count($ppIds) !== count(array_unique($ppIds))) {
            return response()->json([
                'success' => false,
                'message' => 'Item produk duplikat, tidak boleh ada produk yang sama 2x',
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

            $isPpn = (strtolower(trim((string) $request->input('order_type', ''))) === 'ppn');
            if ($isPpn && !$request->filled('no_document')) {
                return response()->json(['success' => false, 'message' => 'No. Dokumen PPN wajib diisi untuk order PPN'], 422);
            }

            $so = new SalesOrder;
            $so->so_code                     = $isPpn ? CodeRepo::generateSoAwalPpn() : CodeRepo::generateSoAwal();
            $so->brand_name                  = $request->input('brand_name');
            $so->customer_id                 = $otherAddress->customer_id;
            $so->customer_other_address_id   = $otherAddress->id;
            // Simpan STRING (CASH/TEMPO/...) agar sinkron dengan modul transaksi
            // (WorkflowService, report, DO semua bandingkan string, kolom DB varchar)
            $so->type_transaction            = $typeName;
            $so->so_for                      = 1;
            $so->so_date                     = null;
            $so->type_so                     = $isPpn ? 'ppn' : 'nonppn';
            $so->approval_mou                = $approval ? 1 : 0;
            $so->idr_rate                    = $kurs;
            $so->catatan                     = (string) $discPercent;
            $so->disc_percent                = $discPercent;
            $so->disc_idr                    = $discIdr;
            $so->disc_usd                    = $discUsd;
            $so->disc_kemasan                = $discKemasan;
            $so->note                        = $note;
            $so->is_proforma                 = 0;
            $so->code                        = null;
            // Indent tetap status AWAL (1) + flag indent, non-indent langsung LANJUTAN (2).
            // Sesuai kesepakatan: yang ter-up ke transaksi = sudah SO Lanjutan.
            if ($isIndent) {
                $so->status        = 1;
                $so->so_indent     = SalesOrder::INDENT['YES'];
                $so->indent_status = 1;
            } else {
                $so->status        = 1;
                $so->so_indent     = SalesOrder::INDENT['NO'];
            }
            $so->condition                   = 1;
            $so->payment_status              = 0;
            $so->count_rev                   = 0;
            $so->created_by                  = $createdBy;
            if ($isPpn) {
                $so->no_ducument_ppn = trim((string) $request->input('no_document'));
                $so->sales_senior_id = $request->input('sales_senior_id') ?: null;
                $so->sales_id = $request->input('sales_id') ?: null;
                $so->rekening = $request->input('rekening', $request->input('rekening_id'));
                if ($kurs <= 0) {
                    $so->idr_rate = 1; // samakan store PPN (idr_rate=1)
                }
            }
            // Auto estimate untuk CASH/TEMPO (samakan SalesOrderStoreService)
            if (in_array($typeName, ['CASH', 'TEMPO'], true)) {
                $so->is_estimate = 1;
                $so->estimate_code = $this->generateEstimateCode();
            } else {
                $so->is_estimate = 0;
            }
            // Jejak order AO asal (untuk idempotensi & tracing)
            if ($request->filled('ao_order_number')) {
                $aoNo = trim((string) $request->input('ao_order_number'));
                $so->note = trim(($so->note ? $so->note . "\n" : '') . "[AO:{$aoNo}]");
            }
            $so->save();

            Log::info('AO SO Awal created', [
                'so_id' => $so->id, 'so_code' => $so->so_code,
                'ao_order' => $request->input('ao_order_number'),
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

            foreach ($normItems as $item) {
                $baseProductPackagingId = $item['pp_id'];
                $product = ProductPack::where('id', $item['pp_id'])->first();

                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Produk dengan id {$item['pp_id']} tidak ditemukan.",
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
                $detail->disc_usd               = $item['disc'];
                $detail->packaging_id           = $item['pack'] ?? $product->packaging_id;
                $detail->free_product           = $item['free'];
                $detail->kontrak                = 0;
                $detail->created_by             = $createdBy;
                $detail->status                 = 1;
                $detail->save();
            }

            // Lanjutkan otomatis ke SO Lanjutan (non-indent) agar sesuai kesepakatan.
            if (!$isIndent) {
                try {
                    $wf = new \App\Services\SalesOrder\SalesOrderWorkflowService();
                    $wf->lanjutkan($so);
                    $so->refresh();
                } catch (\Exception $wfEx) {
                    Log::warning('AO SO auto-lanjutkan gagal, tetap status AWAL: ' . $wfEx->getMessage(), ['so_id' => $so->id]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'SO Awal berhasil dibuat' . ($isIndent ? ' (Indent)' : ' dan dilanjutkan ke SO Lanjutan'),
                'data'    => [
                    'so_id'   => $so->id,
                    'so_code' => $so->so_code,
                    'status'  => $so->status,
                    'status_text' => SalesOrder::STEP[$so->status] ?? (string) $so->status,
                    'so_indent' => $so->so_indent,
                    'is_estimate' => $so->is_estimate ?? 0,
                    'estimate_code' => $so->estimate_code ?? null,
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

    /**
     * List kemasan aktif untuk dropdown AO.
     * GET /api/ao/so-awal/kemasan -> [{id, pack_name}]
     */
    public function kemasan()
    {
        $rows = \App\Entities\Master\Packaging::where('status', \App\Entities\Master\Packaging::STATUS['ACTIVE'])
            ->orderBy('pack_name')
            ->select('id', 'pack_name')
            ->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * List SO baru untuk import transaksi -> AO (pull).
     * GET /api/ao/so-awal/list?since_id=0&limit=100
     * Mengembalikan SO non-deleted dengan id > since_id (asc), max 200.
     * Dipakai scheduler AO agar input via web transaksi ikut masuk AO.
     */
    public function list(Request $request)
    {
        $sinceId = (int) $request->query('since_id', 0);
        $limit = min(max((int) $request->query('limit', 100), 1), 200);

        $rows = SalesOrder::where('id', '>', $sinceId)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get(['id', 'so_code', 'status', 'brand_name', 'type_transaction', 'customer_id', 'customer_other_address_id', 'note', 'idr_rate', 'disc_percent', 'disc_idr', 'disc_usd', 'disc_kemasan', 'approval_mou', 'so_indent', 'is_estimate', 'estimate_code', 'code', 'created_by', 'created_at']);

        $usernames = [];
        try {
            $uids = $rows->pluck('created_by')->filter()->unique()->values()->all();
            if ($uids) {
                $usernames = Superuser::whereIn('id', $uids)->pluck('username', 'id')->toArray();
            }
        } catch (\Exception $e) {
        }

        $data = $rows->map(function ($so) use ($usernames) {
            return [
                'so_id' => $so->id,
                'so_code' => $so->so_code,
                'status' => $so->status,
                'brand_name' => $so->brand_name,
                'type_transaction' => $so->type_transaction,
                'customer_id' => $so->customer_id,
                'customer_other_address_id' => $so->customer_other_address_id,
                'created_by' => $so->created_by,
                'created_username' => $usernames[$so->created_by] ?? null,
                'created_at' => $so->created_at,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Detail SO + items untuk import (GET /api/ao/so-awal/detail/{so_code}).
     */
    public function detail($so_code)
    {
        $so = SalesOrder::where('so_code', $so_code)->first();
        if (!$so) {
            $trashed = SalesOrder::withTrashed()->where('so_code', $so_code)->first();
            if ($trashed) {
                return response()->json(['success' => false, 'deleted' => true, 'message' => "SO {$so_code} sudah dihapus"], 410);
            }
            return response()->json(['success' => false, 'message' => "SO {$so_code} tidak ditemukan"], 404);
        }

        $items = SalesOrderItem::where('so_id', $so->id)->get();
        $ppIds = $items->pluck('product_packaging_id')->filter()->unique()->values()->all();
        $names = [];
        if ($ppIds) {
            try {
                $names = ProductPack::whereIn('id', $ppIds)->pluck('name', 'id')->toArray();
            } catch (\Exception $e) {
            }
        }

        return response()->json(['success' => true, 'data' => [
            'so_id' => $so->id,
            'so_code' => $so->so_code,
            'status' => $so->status,
            'brand_name' => $so->brand_name,
            'type_transaction' => $so->type_transaction,
            'customer_id' => $so->customer_id,
            'customer_other_address_id' => $so->customer_other_address_id,
            'note' => $so->note,
            'idr_rate' => $so->idr_rate,
            'disc_percent' => $so->disc_percent,
            'disc_idr' => $so->disc_idr,
            'disc_usd' => $so->disc_usd,
            'disc_kemasan' => $so->disc_kemasan,
            'approval_mou' => $so->approval_mou,
            'so_indent' => $so->so_indent,
            'created_by' => $so->created_by,
            'items' => $items->map(function ($it) use ($names) {
                return [
                    'product_packaging_id' => $it->product_packaging_id,
                    'product_name' => $names[$it->product_packaging_id] ?? '',
                    'price' => $it->price,
                    'qty' => $it->qty,
                    'disc_usd' => $it->disc_usd,
                    'packaging_id' => $it->packaging_id,
                    'free_product' => $it->free_product,
                ];
            })->values(),
        ]]);
    }

    /**
     * Hapus SO dari AO (delete sync AO -> transaksi).
     * DELETE /api/ao/so-awal/{so_code}
     * Guard: hanya status AWAL(1)/REVISI(3) dan belum ada DO. Selain itu 422.
     */
    public function destroyApi($so_code)
    {
        $so = SalesOrder::where('so_code', $so_code)->first();
        if (!$so) {
            return response()->json(['success' => false, 'message' => "SO {$so_code} tidak ditemukan"], 404);
        }
        if (!in_array((int) $so->status, [1, 3], true)) {
            return response()->json(['success' => false, 'message' => 'SO sudah diproses (lanjutkan/tutup), hapus via flow Void di transaksi'], 422);
        }
        try {
            $hasDo = \App\Entities\Penjualan\PackingOrder::where('so_id', $so->id)->exists();
            if ($hasDo) {
                return response()->json(['success' => false, 'message' => 'SO sudah punya DO, tidak bisa dihapus via AO'], 422);
            }
        } catch (\Exception $e) {
        }

        try {
            DB::beginTransaction();
            SalesOrderItem::where('so_id', $so->id)->delete();
            $so->delete();
            DB::commit();
            Log::info('AO delete-sync: SO dihapus via AO', ['so_code' => $so_code]);
            return response()->json(['success' => true, 'message' => 'SO dihapus di transaksi']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal hapus: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Prediksi nomor SO berikutnya (acuan counting AO).
     * GET /api/ao/so-awal/next-code -> {last_code, next_code}
     * next_code dihitung via CodeRepo::generateSoAwal() TANPA menyimpan.
     * Sifatnya prediksi (bisa geser jika ada SO masuk bersamaan) —
     * nomor resmi tetap so_code yang dikembalikan POST /store.
     */
    public function nextCode()
    {
        $next = CodeRepo::generateSoAwal();
        $last = SalesOrder::withTrashed()
            ->where('status', '>', 0)
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->orderBy('id', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'last_code' => $last->so_code ?? null,
                'next_code' => $next,
            ],
        ]);
    }

    /**
     * GET status SO untuk tombol "Cek Status" di AO + scheduler poll.
     * GET /api/ao/so-awal/status/{so_code}
     * Response: status int + status_text (STEP) + do_code/nota/invoice bila sudah tutup.
     */
    public function status($so_code)
    {
        $so = SalesOrder::where('so_code', $so_code)->first();
        if (!$so) {
            // Bedakan "tidak pernah ada" vs "sudah dihapus" (soft delete) agar AO tidak menebak
            $trashed = SalesOrder::withTrashed()->where('so_code', $so_code)->first();
            if ($trashed) {
                return response()->json(['success' => false, 'deleted' => true, 'message' => "SO {$so_code} sudah dihapus di transaksi"], 410);
            }
            return response()->json(['success' => false, 'message' => "SO {$so_code} tidak ditemukan"], 404);
        }

        $doCode = null;
        $notaCode = $so->code;
        $invoiceCode = null;
        try {
            $do = \App\Entities\Penjualan\PackingOrder::where('so_id', $so->id)->orderBy('id', 'desc')->first();
            if ($do) {
                $doCode = $do->do_code ?? $do->code;
                $inv = \App\Entities\Finance\Invoicing::where('do_id', $do->id)->orderBy('id', 'desc')->first();
                if ($inv) {
                    $invoiceCode = $inv->code;
                }
            }
        } catch (\Exception $e) {
            // abaikan, status tetap kembali
        }

        return response()->json([
            'success' => true,
            'data' => [
                'so_id'     => $so->id,
                'so_code'   => $so->so_code,
                'status'    => $so->status,
                'status_text' => SalesOrder::STEP[$so->status] ?? (string) $so->status,
                'so_indent' => $so->so_indent,
                'indent_status' => $so->indent_status,
                'condition' => $so->condition,
                'do_code' => $doCode,
                'nota_code' => $notaCode,
                'invoice_code' => $invoiceCode,
                'updated_at' => $so->updated_at,
            ],
        ]);
    }

    /**
     * POST revisi dari AO (hanya jika SO status=3 di transaksi).
     * POST /api/ao/so-awal/update
     * Payload sama seperti store + wajib transaksi_so_id / so_code.
     * Aturan edit ikut SalesOrderUpdateService@updateStep1
     * (type_transaction, brand, note, kurs, so_indent, disc global, items baru).
     */
    public function updateFromAo(Request $request)
    {
        $so = null;
        if ($request->filled('transaksi_so_id')) {
            $so = SalesOrder::find($request->input('transaksi_so_id'));
        } elseif ($request->filled('so_code')) {
            $so = SalesOrder::where('so_code', $request->input('so_code'))->first();
        }
        if (!$so) {
            return response()->json(['success' => false, 'message' => 'SO transaksi tidak ditemukan'], 404);
        }
        if ((int) $so->status !== 3) {
            return response()->json([
                'success' => false,
                'message' => 'SO belum berstatus perlu revisi (status=3), revisi ditolak',
                'status' => $so->status,
                'status_text' => SalesOrder::STEP[$so->status] ?? (string) $so->status,
            ], 422);
        }

        // Reuse validasi ringan store (tanpa customer ulang — customer dikunci)
        $validator = Validator::make($request->all(), [
            'brand_name' => 'required|string',
            'type_transaction' => 'required',
            'items' => 'required|array|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $typeMap = ['CASH' => 1, 'TEMPO' => 2, 'MARKETPLACE' => 3, 'COD' => 4];
        $typeRaw = $request->input('type_transaction');
        if (is_numeric($typeRaw)) {
            $typeInt = (int) $typeRaw;
            $typeNameUp = array_search($typeInt, $typeMap, true);
        } else {
            $typeNameUp = strtoupper(trim((string) $typeRaw));
            $typeInt = $typeMap[$typeNameUp] ?? 0;
        }
        if (!in_array($typeInt, [1, 2, 3, 4], true)) {
            return response()->json(['success' => false, 'message' => 'type_transaction tidak valid'], 422);
        }

        try {
            DB::beginTransaction();
            $so->type_transaction = $typeNameUp;
            $so->brand_name = $request->input('brand_name');
            $so->note = $request->input('note', $request->input('notes', $so->note));
            $so->idr_rate = $this->parseNumber($request->input('kurs', $request->input('currency_rate', $so->idr_rate)));
            $so->disc_percent = $this->parseNumber($request->input('disc_percent', $request->input('discount_value', $so->disc_percent)));
            $so->disc_idr = $this->parseNumber($request->input('disc_idr', $so->disc_idr));
            $so->disc_usd = $this->parseNumber($request->input('disc_usd', $so->disc_usd));
            $so->disc_kemasan = $this->parseNumber($request->input('disc_kemasan', $so->disc_kemasan));
            if ($request->has('so_indent') || $request->has('is_indent')) {
                $so->so_indent = $this->parseBool($request->input('so_indent', $request->input('is_indent'))) ? 1 : 0;
            }
            if ($request->filled('no_document')) {
                $so->no_ducument_ppn = trim((string) $request->input('no_document'));
            }
            if ($request->filled('sales_senior_id')) {
                $so->sales_senior_id = $request->input('sales_senior_id');
            }
            if ($request->filled('sales_id')) {
                $so->sales_id = $request->input('sales_id');
            }
            if ($request->has('rekening')) {
                $so->rekening = $request->input('rekening');
            }
            if ($request->filled('pic_username')) {
                $su = Superuser::where('username', $request->input('pic_username'))->first();
                if ($su) {
                    $so->updated_by = $su->id;
                }
            }
            $so->status = 1; // reset AWAL dulu (konsisten updateStep1), lalu lanjutkan otomatis di bawah
            $so->save();

            // Hapus item lama + insert baru (samakan updateStep1)
            SalesOrderItem::where('so_id', $so->id)->delete();
            foreach ((array) $request->input('items') as $it) {
                $ppId = $it['product_packaging_id'] ?? $it['product_id'] ?? $it['sku'] ?? null;
                $product = ProductPack::where('id', $ppId)->first();
                if (!$product) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Produk {$ppId} tidak ditemukan"], 422);
                }
                $free = $this->parseBool($it['free_product'] ?? $it['free'] ?? 0);
                $d = new SalesOrderItem;
                $d->so_id = $so->id;
                $d->product_packaging_id = $ppId;
                $d->price = $this->parseNumber($it['price'] ?? $it['unit_price'] ?? 0);
                $d->qty = $this->parseNumber($it['qty'] ?? 0);
                $d->disc_usd = $free ? 0 : $this->parseNumber($it['disc_usd'] ?? $it['discount'] ?? $it['disc'] ?? 0);
                $d->packaging_id = $it['packaging_id'] ?? $it['packaging'] ?? $product->packaging_id;
                $d->free_product = $free ? 1 : 0;
                $d->kontrak = 0;
                $d->created_by = $so->updated_by ?? $so->created_by;
                $d->status = 1;
                $d->save();
            }

            // Revisi dari AO langsung lanjutkan lagi (non-indent) — tidak berhenti di AWAL.
            $isIndentRev = ((int) $so->so_indent === 1);
            if (!$isIndentRev) {
                try {
                    $wf = new \App\Services\SalesOrder\SalesOrderWorkflowService();
                    $wf->lanjutkan($so);
                    $so->refresh();
                } catch (\Exception $wfEx) {
                    Log::warning('AO revisi auto-lanjutkan gagal, tetap AWAL: ' . $wfEx->getMessage(), ['so_id' => $so->id]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Revisi diterima dan dilanjutkan ke SO Lanjutan', 'data' => ['so_id' => $so->id, 'so_code' => $so->so_code, 'status' => $so->status, 'status_text' => SalesOrder::STEP[$so->status] ?? (string) $so->status]], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AO SO revisi gagal: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Revisi gagal', 'error' => $e->getMessage()], 500);
        }
    }

    // ---------- helpers ----------

    protected function resolveMember($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }
        // 1) langsung id CustomerOtherAddress
        if (strpos($raw, '.') === false && ctype_digit($raw)) {
            $m = CustomerOtherAddress::where('id', $raw)->first();
            if ($m) {
                return $m;
            }
        }
        // 2) format customer_id.index
        if (strpos($raw, '.') !== false) {
            [$cid, $idx] = explode('.', $raw, 2);
            if (ctype_digit($cid) && ctype_digit($idx) && (int) $idx > 0) {
                $m = CustomerOtherAddress::where('customer_id', (int) $cid)
                    ->orderBy('id', 'asc')->skip((int) $idx - 1)->first();
                if ($m) {
                    return $m;
                }
            }
        }
        return null;
    }

    protected function parseNumber($v)
    {
        if ($v === null || $v === '') {
            return 0;
        }
        $s = trim((string) $v);
        // dukung "15.500" (ribuan ID) -> 15500 ; "1.800.000,50" -> 1800000.50
        if (strpos($s, ',') !== false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // hilangkan koma ribuan US, titik ribuan ID tanpa desimal dianggap ribuan
            if (preg_match('/^\d{1,3}(\.\d{3})+$/', $s)) {
                $s = str_replace('.', '', $s);
            } else {
                $s = str_replace(',', '', $s);
            }
        }
        return is_numeric($s) ? (float) $s : 0;
    }

    protected function parseBool($v)
    {
        if (is_bool($v)) {
            return $v;
        }
        $s = strtolower(trim((string) $v));
        return in_array($s, ['1', 'yes', 'y', 'true', 'on'], true);
    }

    protected function generateEstimateCode()
    {
        $today = now();
        $prefix = $today->format('ymd');
        $last = SalesOrder::where('estimate_code', 'LIKE', $prefix . '-%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(estimate_code, '-', -1) AS UNSIGNED) DESC")
            ->first();
        $newNumber = $last ? ((int) substr($last->estimate_code, -2) + 1) : 1;
        return $prefix . '-' . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
    }
}