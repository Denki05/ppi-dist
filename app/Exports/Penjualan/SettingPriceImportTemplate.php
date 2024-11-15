<?php

namespace App\Exports\Penjualan;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SettingPriceImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                // 'kode_bahan',
                // 'nama_bahan',
                'kode_produk',
                'nama_produk',
                'kemasan', 
                'brand_name', 
                'kategori', 
                // 'brand_refrence', 
                // 'searah', 
                'harga_lama', 
                'harga_baru'
            ]
        ];
    }
}
