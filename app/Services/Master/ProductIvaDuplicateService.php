<?php

namespace App\Services\Master;

use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\ProductMinStock;
use DB;

/**
 * Duplikasi 12 produk lama menjadi produk IVA baru (ID + 'IVA', kode tetap),
 * kembalikan produk lama ke nama awal, dan arahkan SO/DO terlist ke produk baru.
 *
 * Idempotent & portable: acuan kode SO (bukan ID internal), cek exists sebelum insert.
 * Dipakai oleh tombol 1-klik di menu Master -> Produk.
 */
class ProductIvaDuplicateService
{
    /** old_parent_id => ['iva' => new name, 'old' => old name] */
    const MAP = [
        '17564'     => ['iva' => 'IVA - AQUIORA',      'old' => 'Blue Aqua'],
        'SJA 11940' => ['iva' => 'IVA - ARVELLE',      'old' => 'Cherry Mod'],
        '17568'     => ['iva' => 'IVA - ATELIER NOVAE','old' => 'Citrus Splash'],
        '18395'     => ['iva' => 'IVA - ATLAS DOMINION','old' => 'Cool Stream'],
        '18396'     => ['iva' => 'IVA - BLACK MONARCH','old' => 'French Romance'],
        '17572'     => ['iva' => 'IVA - BLOOM FANTASY','old' => 'Fresh Spring'],
        '24628'     => ['iva' => 'IVA - BRONZE EMPIRE','old' => 'Mocha Coffee'],
        '24442'     => ['iva' => 'IVA - CENDANIQUE',   'old' => 'Mystery'],
        'SJA 11894' => ['iva' => 'IVA - CLARIVA',      'old' => 'New Car'],
        '17565'     => ['iva' => 'IVA - COMFORTE',     'old' => 'Pasific Ocean'],
        '17570'     => ['iva' => 'IVA - COZY AURA',    'old' => 'Secret Sahara'],
        '18397'     => ['iva' => 'IVA - ELITE AIR',    'old' => 'Summer Paradise'],
    ];

    /** [so_code, old_pack, new_pack] — hanya yang terlist di gambar */
    const REPOINT = [
        ['5C098', '17564-9',     '17564IVA-9'],
        ['6J001', 'SJA 11940-9', 'SJA 11940IVA-9'],
        ['5H025', '17568-7',     '17568IVA-7'],
        ['5I030', '17568-7',     '17568IVA-7'],
        ['5I030', '17572-7',     '17572IVA-7'],
        ['5L068', '17572-7',     '17572IVA-7'],
        ['5L069', '18396-9',     '18396IVA-9'],
        ['6D031', '18395-9',     '18395IVA-9'],
        ['6E085', '24628-9',     '24628IVA-9'],
        ['4J084', '24442-7',     '24442IVA-7'],
        ['5A045', '24442-7',     '24442IVA-7'],
        ['6H092', 'SJA 11894-9', 'SJA 11894IVA-9'],
        ['6H092', '17570-9',     '17570IVA-9'],
        ['6G001', '17565-9',     '17565IVA-9'],
        ['6G001', '18397-9',     '18397IVA-9'],
    ];

    public static function newParentId($old)
    {
        return (string) $old . 'IVA';
    }

    public function run()
    {
        $summary = ['parents' => 0, 'childs' => 0, 'min_stocks' => 0, 'min_dedup' => 0, 'so_items' => 0, 'do_items' => 0];

        DB::transaction(function () use (&$summary) {
            // 1. Duplikat parent + pastikan nama IVA (copy dari lama bila belum ada)
            // PENTING: cast key MAP ke string — key numerik ('17564') jadi int oleh PHP,
            // dan where('id', <int>) akan ikut match '17564IVA' di MySQL (coercion).
            foreach (self::MAP as $oldId => $names) {
                $oldId = (string) $oldId;
                $newId = self::newParentId($oldId);
                $old = Product::where('id', $oldId)->first();
                if (!$old) {
                    continue;
                }
                $new = Product::where('id', $newId)->first();
                if (!$new) {
                    $copy = $old->replicate();
                    $copy->id = $newId;
                    $copy->code = $old->code; // kode tetap sama
                    $copy->name = $names['iva'];
                    $copy->save();
                    $summary['parents']++;
                } elseif ($new->name !== $names['iva']) {
                    $new->name = $names['iva'];
                    $new->save();
                }
            }

            // 2. Duplikat semua child milik parent lama
            foreach (self::MAP as $oldId => $names) {
                $oldId = (string) $oldId;
                $newParent = self::newParentId($oldId);
                if (!Product::where('id', $newParent)->exists()) {
                    continue;
                }
                $childs = ProductPack::where('product_id', $oldId)->get();
                foreach ($childs as $child) {
                    $newChildId = $newParent . '-' . $child->packaging_id;
                    if (ProductPack::where('id', $newChildId)->exists()) {
                        // pastikan nama IVA
                        ProductPack::where('id', $newChildId)->where('name', '!=', $names['iva'])
                            ->update(['name' => $names['iva']]);
                        continue;
                    }
                    $copy = $child->replicate();
                    $copy->id = $newChildId;
                    $copy->product_id = $newParent;
                    $copy->code = $child->code; // kode tetap
                    $copy->name = $names['iva'];
                    $copy->save();
                    $summary['childs']++;
                }
            }

            // 3. Duplikat min_stock (samakan dengan lama)
            $nextId = (int) (ProductMinStock::max('id') ?? 0) + 1;
            foreach (self::MAP as $oldId => $names) {
                $oldId = (string) $oldId;
                $newParent = self::newParentId($oldId);
                $olds = ProductPack::where('product_id', $oldId)->pluck('id');
                foreach ($olds as $oldChildId) {
                    $packId = substr(strrchr($oldChildId, '-'), 1);
                    if ($packId === false) {
                        continue;
                    }
                    $newChildId = $newParent . '-' . $packId;
                    if (!ProductPack::where('id', $newChildId)->exists()) {
                        continue;
                    }
                    $rows = ProductMinStock::where('product_packaging_id', $oldChildId)->get();
                    foreach ($rows as $row) {
                        $exists = ProductMinStock::where('product_packaging_id', $newChildId)
                            ->where('warehouse_id', $row->warehouse_id)->exists();
                        if ($exists) {
                            continue;
                        }
                        $copy = $row->replicate();
                        $copy->id = $nextId++;
                        $copy->product_packaging_id = $newChildId;
                        $copy->save();
                        $summary['min_stocks']++;
                    }
                }
            }

            // 3b. Bersihkan min_stock ganda (sisa klik 2x / script lama) — sisakan ID terkecil
            $dups = ProductMinStock::select('product_packaging_id', 'warehouse_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as c'))
                ->where('product_packaging_id', 'like', '%IVA%')
                ->groupBy('product_packaging_id', 'warehouse_id')
                ->havingRaw('COUNT(*) > 1')
                ->get();
            foreach ($dups as $d) {
                $summary['min_dedup'] += ProductMinStock::where('product_packaging_id', $d->product_packaging_id)
                    ->where('warehouse_id', $d->warehouse_id)
                    ->where('id', '!=', $d->keep_id)
                    ->delete();
            }

            // 4. Kembalikan produk LAMA ke nama awal (biar duplikat yang bawa nama IVA)
            // where() harus pakai string agar tidak ikut match ID baru 'xxxIVA' (MySQL coercion)
            foreach (self::MAP as $oldId => $names) {
                $oldId = (string) $oldId;
                Product::where('id', $oldId)->where('name', '!=', $names['old'])
                    ->update(['name' => $names['old']]);
                ProductPack::where('product_id', $oldId)->where('name', '!=', $names['old'])
                    ->update(['name' => $names['old']]);
            }

            // 5. Repoint SO + DO terlist (portable via kode SO)
            foreach (self::REPOINT as [$soCode, $oldPack, $newPack]) {
                if (!ProductPack::where('id', $newPack)->exists()) {
                    continue;
                }
                $soIds = DB::table('penjualan_so')->where('code', $soCode)->pluck('id');
                if ($soIds->isEmpty()) {
                    continue;
                }
                $summary['so_items'] += DB::table('penjualan_so_item')
                    ->whereIn('so_id', $soIds)
                    ->where('product_packaging_id', $oldPack)
                    ->update(['product_packaging_id' => $newPack]);

                $doIds = DB::table('penjualan_do')->whereIn('so_id', $soIds)->pluck('id');
                if ($doIds->isEmpty()) {
                    continue;
                }
                $summary['do_items'] += DB::table('penjualan_do_item')
                    ->whereIn('do_id', $doIds)
                    ->where('product_packaging_id', $oldPack)
                    ->update(['product_packaging_id' => $newPack]);
            }
        });

        return $summary;
    }
}
