<?php

namespace App\Imports\Master;

use App\Entities\Master\ProductProjectPrint;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;
use Auth;

class ProductProjectImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function __construct()
    {
        // Hapus dahulu data yang ada di tabel ProductProjectPrint
        ProductProjectPrint::truncate();
    }

    public function model(array $row)
    {
        $this->validateHeader([
            'kode_produk', 
            'nama_produk', 
            'tipe',
            'kategori', 
            'brand', 
            'searah', 
            'sex', 
            'harga'
        ], $row);

        return new ProductProjectPrint([
            'kode_produk' => $row['kode_produk'],
            'nama_produk' => $row['nama_produk'],
            'tipe' => $row['tipe'] ?? null,
            'kategori' => $row['kategori'],
            'brand' => $row['brand'] ?? null,
            'searah' => $row['searah'] ?? null,
            'sex' => $row['sex'] ?? null,
            'harga' => $row['harga'],
            'created_by' => Auth::user()->id,
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'kode_produk'   => 'required',
            'nama_produk'   => 'required',
            'tipe'          => 'required',
            'kategori'      => 'required',
            'harga'         => 'required',
        ];
    }
}