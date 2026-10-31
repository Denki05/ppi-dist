<?php

namespace App\Enrichment\Contracts;

interface Driver
{
    /**
     * Kumpulkan data pendukung untuk satu parfum acuan.
     *
     * @param string $brand nama brand (kolom master_products.brand_name)
     * @param string $searah nama parfum acuan (relasi sub_brand_reference->name)
     * @param string $internalNotes notes internal dari description produk, boleh ''
     * @return array {success: bool, data: array|null, error: string|null, http_status: int|null, raw: string|null, model: string|null}
     */
    public function enrich($brand, $searah, $internalNotes);

    /**
     * Nama driver (mis. "gemini"), dipakai untuk log dan pemilihan lewat config/settings.
     */
    public function name();
}
