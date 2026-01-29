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
        $price = $plUsd - $discAwal;
        $price -= ($price * ($discPercent / 100));
        $price -= $discAkhir;

        return max($price, 0);
    }
}
