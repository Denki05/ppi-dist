<?php

namespace App\Exports\Accounting;

use App\Entities\Master\Mitra;
use App\Entities\Master\ProductPack;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductFinanceImportTemplate implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [new ProductFinanceTemplateSheet(), new MitraListSheet()];
    }
}

class ProductFinanceTemplateSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    /** Validasi dropdown menjangkau N baris data + cadangan baris baru. */
    const VALIDATION_ROWS = 5000;

    public function title(): string
    {
        return 'Template';
    }

    public function query()
    {
        return ProductPack::query()
            ->join('master_products', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->join('master_packaging', 'master_packaging.id', '=', 'master_products_packaging.packaging_id')
            ->where('master_products.status', 1)
            ->orderBy('master_products_packaging.code')
            ->select(
                'master_products.brand_name',
                'master_products_packaging.code',
                'master_products_packaging.name',
                'master_packaging.pack_name'
            );
    }

    public function headings(): array
    {
        return ['brand', 'code', 'name', 'kemasan', 'mitra', 'harga_beli', 'harga_jual'];
    }

    public function map($row): array
    {
        return [$row->brand_name, $row->code, $row->name, $row->pack_name, '', '', ''];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    /**
     * Dropdown nama mitra pada kolom E agar user tidak salah ketik
     * (sumber typo terbesar pada pesan "Mitra tidak ditemukan").
     * Dipasang via AfterSheet karena versi Excel ini belum punya concern export-validation.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $names = Mitra::where('status', Mitra::STATUS['ACTIVE'])
                    ->orderBy('name')
                    ->pluck('name')
                    ->map(function ($n) {
                        return str_replace('"', '""', $n);
                    })
                    ->values();

                if ($names->isEmpty()) {
                    return;
                }

                $list = '"' . $names->implode(',') . '"';
                // Validasi hanya sepanjang data + buffer 100 baris baru (hemat ukuran file).
                $dataRows = ProductPack::query()
                    ->join('master_products', 'master_products.id', '=', 'master_products_packaging.product_id')
                    ->where('master_products.status', 1)
                    ->count();
                $lastRow = min($dataRows + 100, self::VALIDATION_ROWS) + 1;
                $worksheet = $event->sheet->getDelegate();

                for ($r = 2; $r <= $lastRow; $r++) {
                    $validation = $worksheet->getCell('E' . $r)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1($list);
                    // true = panah dropdown DITAMPILKAN (mapping OOXML showDropDown terbalik).
                    $validation->setShowDropDown(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Mitra tidak valid');
                    $validation->setError('Pilih mitra dari daftar. Lihat sheet Daftar Mitra.');
                }
            },
        ];
    }
}

class MitraListSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    public function title(): string
    {
        return 'Daftar Mitra';
    }

    public function headings(): array
    {
        return ['mitra_aktif'];
    }

    public function array(): array
    {
        return Mitra::where('status', Mitra::STATUS['ACTIVE'])
            ->orderBy('name')
            ->pluck('name')
            ->map(function ($n) {
                return [$n];
            })
            ->toArray();
    }
}
