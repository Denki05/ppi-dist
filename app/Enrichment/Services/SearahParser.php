<?php

namespace App\Enrichment\Services;

class SearahParser
{
    const CONCENTRATION_PATTERNS = [
        'extrait de parfum' => 'Extrait de Parfum',
        'eau de parfum' => 'EDP',
        'edp' => 'EDP',
        'eau de toilette' => 'EDT',
        'edt' => 'EDT',
        'extrait' => 'Extrait',
        'parfum' => 'Parfum',
        'cologne' => 'Cologne',
        'edc' => 'Cologne',
    ];

    /**
     * Ekstrak gender, tahun rilis, dan konsentrasi dari string searah secara deterministik.
     *
     * @return array {gender: string|null, year: int|null, concentration: string|null}
     */
    public static function parse($searah)
    {
        $searah = mb_strtolower((string) $searah);

        return [
            'gender' => self::parseGender($searah),
            'year' => self::parseYear($searah),
            'concentration' => self::parseConcentration($searah),
        ];
    }

    public static function parseGender($searah)
    {
        $searah = mb_strtolower((string) $searah);

        if (preg_match('/for (women and men|men and women)/u', $searah)) {
            return 'unisex';
        }
        if (preg_match('/for women/u', $searah)) {
            return 'women';
        }
        if (preg_match('/for men/u', $searah)) {
            return 'men';
        }

        return null;
    }

    public static function parseYear($searah)
    {
        $searah = (string) $searah;

        if (preg_match('/\((\d{4})\)/u', $searah, $m)) {
            return self::validYear((int) $m[1]);
        }
        if (preg_match('/\b(19\d{2}|20\d{2})\b/u', $searah, $m)) {
            return self::validYear((int) $m[1]);
        }

        return null;
    }

    public static function parseConcentration($searah)
    {
        $searah = mb_strtolower((string) $searah);

        foreach (self::CONCENTRATION_PATTERNS as $pattern => $canonical) {
            if (strpos($searah, $pattern) !== false) {
                return $canonical;
            }
        }

        return null;
    }

    protected static function validYear($year)
    {
        return ($year >= 1900 && $year <= 2100) ? $year : null;
    }
}
