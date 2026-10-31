<?php

namespace App\Enrichment\Services;

class ReferenceGrouper
{
    /**
     * Kelompokkan baris produk menjadi parfum acuan unik (brand + searah).
     *
     * @param array $products daftar array dengan key: id, code, name, brand, searah, description
     * @return array {references: array, special: {dash: array, none: array, middle_east: array}}
     */
    public static function group(array $products)
    {
        $references = [];
        $special = ['dash' => [], 'none' => [], 'middle_east' => []];

        foreach ($products as $product) {
            $row = [
                'id' => $product['id'],
                'code' => isset($product['code']) ? $product['code'] : null,
                'name' => isset($product['name']) ? $product['name'] : null,
                'description' => isset($product['description']) ? $product['description'] : null,
            ];

            $normalized = NameNormalizer::normalize(isset($product['searah']) ? $product['searah'] : '');

            if ($normalized === '' || $normalized === '-') {
                $special['dash'][] = $row;
            } elseif ($normalized === 'none') {
                $special['none'][] = $row;
            } elseif ($normalized === 'middle east') {
                $special['middle_east'][] = $row;
            } else {
                $refKey = NameNormalizer::refKeyFor($product['brand'], $product['searah']);
                if (!isset($references[$refKey])) {
                    $references[$refKey] = [
                        'ref_key' => $refKey,
                        'brand' => $product['brand'],
                        'searah' => $product['searah'],
                        'products' => [],
                    ];
                }
                $references[$refKey]['products'][] = $row;
            }
        }

        return ['references' => array_values($references), 'special' => $special];
    }

    /**
     * Kumpulkan notes internal (description) yang unik untuk satu acuan.
     */
    public static function internalNotesFor(array $reference)
    {
        $descriptions = [];
        foreach ($reference['products'] as $product) {
            $description = trim((string) (isset($product['description']) ? $product['description'] : ''));
            if ($description !== '' && !NotesParser::isPlaceholder($description) && !in_array($description, $descriptions, true)) {
                $descriptions[] = $description;
            }
        }

        return implode("\n", $descriptions);
    }
}
