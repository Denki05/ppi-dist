<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductProjectImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'kode_produk',
                'nama_produk',
                'kategori',
                'brand',
                'searah',
                'sex',
                'harga',
            ]
        ];
    }
}
