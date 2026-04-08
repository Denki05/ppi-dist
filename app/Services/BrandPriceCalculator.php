<?php

namespace App\Services;

class BrandPriceCalculator
{
    public static function calculateUsd(
        string $brand,
        float $plUsd,
        float $discAwal,
        float $discPercent,
        float $discAkhir
    ): float {
        // Pastikan nama brand seragam (huruf besar semua & tanpa spasi lebih)
        $brandName = strtoupper(trim($brand));
        $price = 0;

        if ($brandName === 'SENSES') {
            // Rumus Senses: (PL - DiscAwal) - (Persentase) - DiscAkhir(nominal)
            $step1 = $plUsd - $discAwal;
            $diskonPersen = $step1 * ($discPercent / 100);
            $price = $step1 - $diskonPersen - $discAkhir;
            
        } elseif ($brandName === 'GCF') {
            // Rumus GCF: (PL - DiscAwal) - ((PL - (PL * Persentase)) * DiscAkhir%)
            $step1 = $plUsd - $discAwal;
            
            // Perhatikan: Persentase diskon GCF diambil dari harga PL asli
            $baseDiskon = $plUsd - ($plUsd * ($discPercent / 100));
            
            // Karena parameter dari UI adalah 6, kita wajib membaginya dengan 100 agar jadi 6%
            $diskonAkhir = $baseDiskon * ($discAkhir / 100);
            
            $price = $step1 - $diskonAkhir;
            
        } else {
            // Default perhitungan untuk brand lain
            $step1 = $plUsd - $discAwal;
            $price = $step1 - ($step1 * ($discPercent / 100)) - $discAkhir;
        }

        // Pastikan harga tidak pernah minus
        return max($price, 0);
    }
}