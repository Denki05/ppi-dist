<?php
namespace App\Services;

class BrandPriceCalculator
{
    public static function calculateUsd($brand, $plUsd): float
    {
        switch (strtoupper($brand)) {

            case 'SENSES':
                // (( PL - $2 ) - 20%) - $1.5
                $step1 = $plUsd - 2;                     // kurangi disc awal $2
                $step2 = $step1 - ($step1 * 20 / 100);   // kurangi 20%
                $step3 = $step2 - 1.5;                   // kurangi disc akhir $1.5
                return max($step3, 0);

            case 'GCF':
                // (( PL - $2 ) - 10%) - 6%
                $step1 = $plUsd - 2;                     // kurangi disc awal $2
                $step2 = $step1 - ($step1 * 10 / 100);   // kurangi 10%
                $step3 = $step2 - ($step2 * 6 / 100);    // kurangi 6% dari sisa
                return max($step3, 0);

            default:
                return max($plUsd, 0);
        }
    }
}