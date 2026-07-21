<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportVariantYearBuilder
{
    public function getYears()
    {
        return DB::table('report_variant_year')
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun')
            ->toArray();
    }

    /**
     * Bangun struktur tree: Brand -> Bahan Baku -> Produk -> Kemasan -> [tahun => qty]
     */
    public function buildTree()
    {
        $rows = DB::table('report_variant_year')
            ->select('brand_name', 'material_name', 'product_name', 'packaging', 'tahun', 'qty')
            ->orderBy('brand_name')
            ->orderBy('material_name')
            ->orderBy('product_name')
            ->orderBy('packaging')
            ->get();

        $tree = [];

        foreach ($rows as $r) {
            $brand    = $r->brand_name ?: '(Tanpa Brand)';
            $material = $r->material_name ?: '(Tanpa Bahan Baku)';
            $product  = $r->product_name ?: '(Tanpa Produk)';
            $pack     = $r->packaging ?: '-';

            $tree[$brand][$material][$product][$pack][(int) $r->tahun] = (float) $r->qty;
        }

        return $tree;
    }

    /**
     * Entry point: flatten tree jadi list baris siap render (rekursif).
     * Level: 0 = Brand, 1 = Bahan Baku, 2 = Produk, 3 = Kemasan (leaf/data asli)
     */
    public function flatten(array $tree, array $years)
    {
        $rows = [];
        $grandTotal = $this->walk($tree, 0, $years, $rows);

        return [
            'rows'        => $rows,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Rekursif: untuk tiap child di level ini —
     */
    private function walk(array $children, int $level, array $years, array &$rows)
    {
        $levelTotal = array_fill_keys($years, 0);

        foreach ($children as $label => $value) {

            if ($level === 3) {
                // Leaf: Level Kemasan
                $qtyPerYear = array_fill_keys($years, 0);
                foreach ($value as $tahun => $qty) {
                    $qtyPerYear[$tahun] = $qty;
                }

                $rows[] = [
                    'level'        => 3,
                    'label'        => 'Kemasan : ' . $label, // Penamaan Kemasan
                    'qty_per_year' => $qtyPerYear,
                    'is_total'     => false,
                ];

                foreach ($years as $y) {
                    $levelTotal[$y] += $qtyPerYear[$y];
                }

            } else {
                // 1. BARIS INDUK (Dicetak di atas)
                $prefix = '';
                if ($level === 0) $prefix = 'BRAND - ';
                elseif ($level === 1) $prefix = 'Bahan Baku : ';
                elseif ($level === 2) $prefix = 'Variant : ';

                $rows[] = [
                    'level'        => $level,
                    'label'        => $prefix . $label,
                    'qty_per_year' => null, // Dikosongkan karena nilainya ada di baris Total bawah
                    'is_total'     => false,
                ];

                // Proses anak-anaknya...
                $childTotal = $this->walk($value, $level + 1, $years, $rows);

                // 2. BARIS TOTAL (Dicetak di bawah setelah rincian anak selesai)
                $totalPrefix = '';
                if ($level === 0) $totalPrefix = 'Total Brand ' . $label;
                elseif ($level === 1) $totalPrefix = 'Total Bahan Baku';
                elseif ($level === 2) $totalPrefix = 'Total Variant';

                $rows[] = [
                    'level'        => $level,
                    'label'        => $totalPrefix,
                    'qty_per_year' => $childTotal,
                    'is_total'     => true,
                ];

                foreach ($years as $y) {
                    $levelTotal[$y] += $childTotal[$y];
                }
            }
        }

        return $levelTotal;
    }
}