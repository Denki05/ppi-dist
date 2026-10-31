<?php

namespace App\Enrichment\Services;

class NameNormalizer
{
    public static function normalize($name)
    {
        $name = str_replace('&', ' and', (string) $name);
        $name = mb_strtolower($name);
        $name = preg_replace('/\s+/u', ' ', $name);
        $name = trim($name);
        $name = preg_replace('/[.,;:]+$/u', '', $name);

        return trim($name);
    }

    public static function slug($name)
    {
        $slug = preg_replace('/[^a-z0-9]+/u', '-', self::normalize($name));

        return trim($slug, '-');
    }

    public static function refKeyFor($brand, $searah)
    {
        return self::slug($brand) . '--' . self::slug($searah);
    }
}
