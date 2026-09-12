<?php

namespace App\Services\Accounting;

use App\Entities\Accounting\PriceLogFinance;
use App\Entities\Master\BrandLokal;
use App\Entities\Master\Mitra;
use App\Entities\Master\Packaging;
use App\Entities\Master\ProductFinance;
use App\Entities\Master\ProductPack;
use DB;
use Illuminate\Support\Collection;

/**
 * Logika bisnis Product UV (FAT -> CV).
 *
 * Controller hanya mengurus HTTP (akses, validasi request, response).
 * Semua query, transaksi, dan bulk-cache terpusat di sini agar ringan.
 */
class ProductFinanceService
{
    /**
     * Data dropdown untuk index.
     */
    public function indexData(): array
    {
        return [
            'mitra' => Mitra::where('status', Mitra::STATUS['ACTIVE'])
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    /**
     * Data dropdown untuk form create.
     */
    public function createData(): array
    {
        return [
            'mitra' => Mitra::where('status', Mitra::STATUS['ACTIVE'])
                ->orderBy('name')
                ->get(['id', 'name']),
            'kemasan' => Packaging::orderBy('pack_name')->get(),
            'brand' => BrandLokal::orderBy('brand_name')->get(),
        ];
    }

    /**
     * Lookup produk per brand untuk select2 (dibatasi agar ringan).
     */
    public function productsByBrand(?string $brandName, int $limit = 200): Collection
    {
        if (blank($brandName)) {
            return collect();
        }

        return ProductPack::leftJoin('master_products', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->leftJoin('master_packaging', 'master_packaging.id', '=', 'master_products_packaging.packaging_id')
            ->where('master_products.brand_name', $brandName)
            ->orderBy('master_products_packaging.name')
            ->limit($limit)
            ->get([
                'master_products_packaging.id',
                'master_products_packaging.code',
                'master_products_packaging.name',
                'master_packaging.id as packaging_id',
                'master_packaging.pack_name as packaging_name',
            ]);
    }

    /**
     * Simpan Product UV manual (menu Create).
     *
     * @return array [status => ok|validation|not_found|exists, errors => [], id => string|null]
     */
    public function store(array $input): array
    {
        $productPack = ProductPack::where('id', $input['product'])->first();

        if (! $productPack) {
            return ['status' => 'not_found', 'errors' => ['Product Pack tidak ditemukan.'], 'id' => null];
        }

        // Kunci bisnis baku: (product_pack_id, mitra_id). Kolom id string
        // packId-mitraId dipertahankan sebagai dual-write untuk FK lama
        // (price_log, invoice_detail) sampai migrasi PK surrogate tahap 2.
        $financeId = $productPack->id . '-' . $input['mitra_id'];

        $exists = ProductFinance::where('product_pack_id', $productPack->id)
            ->where('mitra_id', $input['mitra_id'])
            ->exists()
            || ProductFinance::where('id', $financeId)->exists();

        if ($exists) {
            return ['status' => 'exists', 'errors' => ['Product Finance sudah ada!'], 'id' => $financeId];
        }

        try {
            DB::beginTransaction();

            $finance = new ProductFinance();
            $finance->id = $financeId;
            $finance->product_pack_id = $productPack->id;
            $finance->brand_name = $input['brand'];
            $finance->code_product = $productPack->code;
            $finance->name_product = $productPack->name;
            $finance->product_id = $productPack->product_id;
            $finance->packaging_id = $input['packaging_code'];
            $finance->mitra_id = $input['mitra_id'];
            $finance->buying_price_usd_unit = $input['harga_beli_satuan'];
            $finance->selling_price_usd_unit = $input['harga_jual_satuan'];
            $finance->year = date('Y');
            $finance->status = ProductFinance::STATUS['ACTIVE'];
            $finance->save();

            DB::commit();

            return ['status' => 'ok', 'errors' => [], 'id' => $financeId];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return ['status' => 'error', 'errors' => ['Gagal menyimpan data.'], 'id' => null];
        }
    }

    /**
     * Update harga satuan + catat harga lama ke log.
     *
     * @return array [status => ok|not_found|error, message => string]
     */
    public function updatePrice(string $id, $buying, $selling, $userId = null): array
    {
        try {
            DB::beginTransaction();

            $product = ProductFinance::find($id);

            if (! $product) {
                DB::rollBack();

                return ['status' => 'not_found', 'message' => 'Produk tidak ditemukan!'];
            }

            $oldBuy = $product->buying_price_usd_unit;
            $oldSell = $product->selling_price_usd_unit;

            // Skip update kalau tidak ada perubahan (hemat 1x save + 1x log).
            if ((float) $oldBuy === (float) $buying && (float) $oldSell === (float) $selling) {
                DB::rollBack();

                return ['status' => 'ok', 'message' => 'Tidak ada perubahan harga.'];
            }

            $product->buying_price_usd_unit = $buying;
            $product->selling_price_usd_unit = $selling;
            $product->save();

            $log = new PriceLogFinance();
            $log->product_finance_id = $product->id;
            $log->buying_price_usd_unit = $oldBuy;
            $log->selling_price_usd_unit = $oldSell;
            $log->buying_price_usd_drum = $product->buying_price_usd_drum;
            $log->selling_price_usd_drum = $product->selling_price_usd_drum;
            $log->year = $product->year;
            if ($userId) {
                $log->created_by = $userId;
            }
            $log->save();

            DB::commit();

            return ['status' => 'ok', 'message' => 'Harga berhasil diperbarui!'];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return ['status' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui harga!'];
        }
    }

    /** Batas baris terisi per file agar import raksasa tidak mengunci DB / OOM. */
    const MAX_IMPORT_ROWS = 5000;

    /**
     * Normalisasi murni (tanpa DB): ambil hanya baris yang diisi user.
     * Dipisah agar bisa di-unit-test tanpa database.
     *
     * @return array list [mitra, beli, jual, code, kemasan, brand]
     */
    public function extractTargets(Collection $rows): array
    {
        $targets = [];
        foreach ($rows as $row) {
            $mitra = trim((string) ($row['mitra'] ?? ''));
            $beli = trim((string) ($row['harga_beli'] ?? ''));
            $jual = trim((string) ($row['harga_jual'] ?? ''));
            $code = trim((string) ($row['code'] ?? ''));
            $kemasan = trim((string) ($row['kemasan'] ?? ''));
            $brand = trim((string) ($row['brand'] ?? ''));

            if ($mitra === '' && $beli === '' && $jual === '') {
                continue;
            }

            $targets[] = compact('mitra', 'beli', 'jual', 'code', 'kemasan', 'brand');
        }

        return $targets;
    }

    /**
     * Riwayat harga 1 record + harga produk yang sama di mitra lain.
     *
     * @return array [status => ok|not_found, finance, across, logs]
     */
    public function priceHistory(string $id, int $limit = 50): array
    {
        $finance = ProductFinance::leftJoin('master_mitra', 'master_mitra.id', '=', 'master_product_finance.mitra_id')
            ->where('master_product_finance.id', $id)
            ->select(
                'master_product_finance.id',
                'master_product_finance.product_pack_id',
                'master_product_finance.code_product',
                'master_product_finance.name_product',
                'master_product_finance.buying_price_usd_unit as beli',
                'master_product_finance.selling_price_usd_unit as jual',
                'master_mitra.name as mitra'
            )
            ->first();

        if (! $finance) {
            return ['status' => 'not_found', 'finance' => null, 'across' => [], 'logs' => []];
        }

        $across = ProductFinance::leftJoin('master_mitra', 'master_mitra.id', '=', 'master_product_finance.mitra_id')
            ->where('master_product_finance.product_pack_id', $finance->product_pack_id)
            ->orderBy('master_mitra.name')
            ->get([
                'master_product_finance.id as id',
                'master_mitra.name as mitra',
                'master_product_finance.buying_price_usd_unit as beli',
                'master_product_finance.selling_price_usd_unit as jual',
                'master_product_finance.status as status',
            ]);

        $logs = PriceLogFinance::where('product_finance_id', $finance->id)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get(['buying_price_usd_unit as beli', 'selling_price_usd_unit as jual', 'year', 'created_at']);

        return ['status' => 'ok', 'finance' => $finance, 'across' => $across, 'logs' => $logs];
    }

    /**
     * Aktif/nonaktif 1 record (tanpa hapus data, aman untuk audit).
     *
     * @return array [status => ok|not_found, active => bool]
     */
    public function toggleStatus(string $id): array
    {
        $finance = ProductFinance::find($id);

        if (! $finance) {
            return ['status' => 'not_found', 'active' => false];
        }

        $finance->status = ((int) $finance->status === ProductFinance::STATUS['ACTIVE'])
            ? ProductFinance::STATUS['DELETED']
            : ProductFinance::STATUS['ACTIVE'];
        $finance->save();

        return ['status' => 'ok', 'active' => (int) $finance->status === ProductFinance::STATUS['ACTIVE']];
    }

    /**
     * Proses hasil baca Excel (sudah berupa Collection keyed by heading).
     * Hanya baris yang diisi user yang diproses; sisanya di-skip.
     *
     * Bulk preload dipakai agar N baris = ~3 query baca, bukan 3xN query.
     *
     * @return array [success => string[], error => string[], processed => int]
     */
    public function processImportRows(Collection $rows): array
    {
        $collectSuccess = [];
        $collectError = [];

        // 1. Normalisasi + filter hanya baris yang diisi.
        $targets = $this->extractTargets($rows);

        if (empty($targets)) {
            return [
                'success' => ['Tidak ada data yang berhasil di-import.'],
                'error' => ['Tidak ada baris yang diisi. Silakan isi kolom mitra, harga_beli, dan harga_jual pada baris yang mau di-upload.'],
                'processed' => 0,
            ];
        }

        if (count($targets) > self::MAX_IMPORT_ROWS) {
            return [
                'success' => ['Tidak ada data yang berhasil di-import.'],
                'error' => ['File melebihi ' . self::MAX_IMPORT_ROWS . ' baris terisi. Pecah menjadi beberapa file lebih kecil.'],
                'processed' => count($targets),
            ];
        }

        // 2. Bulk preload: pack, mitra, existing finance.
        $codes = collect($targets)->pluck('code')->filter()->unique()->values();
        $kemasanNames = collect($targets)->pluck('kemasan')->filter()->unique()->values();
        $mitraNames = collect($targets)->pluck('mitra')->filter()->unique()->values();

        $packs = ProductPack::query()
            ->join('master_packaging', 'master_packaging.id', '=', 'master_products_packaging.packaging_id')
            ->whereIn('master_products_packaging.code', $codes)
            ->when($kemasanNames->isNotEmpty(), function ($q) use ($kemasanNames) {
                $q->whereIn('master_packaging.pack_name', $kemasanNames);
            })
            ->select('master_products_packaging.*', 'master_packaging.pack_name')
            ->get()
            ->keyBy(function ($item) {
                return $item->code . '|' . $item->pack_name;
            });

        $mitras = Mitra::whereIn('name', $mitraNames)
            ->get(['id', 'name'])
            ->keyBy('name');

        // Lookup baku: (product_pack_id, mitra_id). Fallback ke id legacy
        // untuk baris lama yang belum ter-backfill.
        $packIds = $packs->map(function ($p) {
            return $p->id;
        })->unique()->values();
        $mitraIds = $mitras->map(function ($m) {
            return $m->id;
        })->unique()->values();

        $existing = collect();
        if ($packIds->isNotEmpty() && $mitraIds->isNotEmpty()) {
            $existing = ProductFinance::whereIn('product_pack_id', $packIds)
                ->whereIn('mitra_id', $mitraIds)
                ->get()
                ->keyBy(function ($item) {
                    return $item->product_pack_id . '|' . $item->mitra_id;
                });
        }

        $legacyIds = [];
        foreach ($targets as $t) {
            $pack = $packs[$t['code'] . '|' . $t['kemasan']] ?? null;
            $mitraRow = $mitras[$t['mitra']] ?? null;
            if ($pack && $mitraRow && ! isset($existing[$pack->id . '|' . $mitraRow->id])) {
                $legacyIds[] = $pack->id . '-' . $mitraRow->id;
            }
        }

        $legacy = collect();
        if (! empty($legacyIds)) {
            $legacy = ProductFinance::whereIn('id', array_unique($legacyIds))
                ->get()
                ->keyBy('id');
        }

        // 3. Proses per baris tanpa query tambahan (kecuali save).
        DB::beginTransaction();
        try {
            foreach ($targets as $t) {
                if ($t['mitra'] === '' || $t['beli'] === '' || $t['jual'] === '') {
                    $collectError[] = "{$t['code']} - Data belum lengkap (mitra, harga_beli, harga_jual wajib diisi).";
                    continue;
                }

                if (! is_numeric($t['beli']) || ! is_numeric($t['jual'])) {
                    $collectError[] = "{$t['code']} - Harga beli/jual harus berupa angka.";
                    continue;
                }

                $pack = $packs[$t['code'] . '|' . $t['kemasan']] ?? null;
                if (! $pack) {
                    $collectError[] = "{$t['code']} / {$t['kemasan']} - Product Pack tidak ditemukan!";
                    continue;
                }

                $mitraRow = $mitras[$t['mitra']] ?? null;
                if (! $mitraRow) {
                    $collectError[] = "{$t['mitra']} - Mitra tidak ditemukan!";
                    continue;
                }

                $financeId = $pack->id . '-' . $mitraRow->id;
                $compositeKey = $pack->id . '|' . $mitraRow->id;
                $finance = $existing[$compositeKey] ?? ($legacy[$financeId] ?? null);

                if ($finance && empty($finance->product_pack_id)) {
                    $finance->product_pack_id = $pack->id;
                }

                if (! $finance) {
                    $new = new ProductFinance();
                    $new->id = $financeId;
                    $new->product_pack_id = $pack->id;
                    $new->brand_name = $t['brand'] !== '' ? $t['brand'] : null;
                    $new->code_product = $pack->code;
                    $new->name_product = $pack->name;
                    $new->mitra_id = $mitraRow->id;
                    $new->product_id = $pack->product_id;
                    $new->packaging_id = $pack->packaging_id;
                    $new->buying_price_usd_unit = $t['beli'];
                    $new->selling_price_usd_unit = $t['jual'];
                    $new->year = date('Y');
                    $new->status = ProductFinance::STATUS['ACTIVE'];
                    $new->save();

                    $existing[$compositeKey] = $new;
                    $collectSuccess[] = "{$pack->code} - {$pack->name} ({$mitraRow->name}) berhasil ditambahkan.";
                    continue;
                }

                if ((float) $finance->buying_price_usd_unit != (float) $t['beli']
                    || (float) $finance->selling_price_usd_unit != (float) $t['jual']) {
                    PriceLogFinance::create([
                        'product_finance_id' => $finance->id,
                        'buying_price_usd_unit' => $finance->buying_price_usd_unit,
                        'selling_price_usd_unit' => $finance->selling_price_usd_unit,
                        'buying_price_usd_drum' => $finance->buying_price_usd_drum,
                        'selling_price_usd_drum' => $finance->selling_price_usd_drum,
                        'year' => $finance->year,
                    ]);

                    $finance->buying_price_usd_unit = $t['beli'];
                    $finance->selling_price_usd_unit = $t['jual'];
                    $finance->year = date('Y');
                    $finance->save();

                    $existing[$compositeKey] = $finance;
                    $collectSuccess[] = "{$pack->code} - {$pack->name} ({$mitraRow->name}) berhasil diupdate.";
                } else {
                    $collectError[] = "{$pack->code} - {$pack->name} ({$mitraRow->name}) tidak ada perubahan harga.";
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return [
                'success' => ['No successful import.'],
                'error' => [$e->getMessage()],
                'processed' => count($targets),
            ];
        }

        return [
            'success' => ! empty($collectSuccess) ? $collectSuccess : ['No successful import.'],
            'error' => ! empty($collectError) ? $collectError : ['No failed import.'],
            'processed' => count($targets),
        ];
    }
}
