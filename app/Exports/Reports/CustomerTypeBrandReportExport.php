<?php

namespace App\Exports\Reports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerTypeBrandReportExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;
    protected $brands;

    public function __construct($data, $brands)
    {
        $this->data = $data;
        $this->brands = $brands; // Daftar brand
    }

    public function array(): array
    {
        $groupedData = []; // Pastikan array ini terinisialisasi
        $overallTotalPerBrand = []; // Menyimpan total keseluruhan per brand

        // Inisialisasi total keseluruhan per brand
        foreach ($this->brands as $brand) {
            $overallTotalPerBrand[$brand] = ['qty' => 0, 'purchase' => 0];
        }

        foreach ($this->data as $category => $customers) {
            // Tambahkan baris kategori hanya jika ada data customer
            if (!empty($customers)) {
                $groupedData[] = [$category, '', '']; // Baris kategori baru
            }

            // Inisialisasi total qty dan omset per kategori untuk setiap brand
            $categoryTotalPerBrand = [];
            foreach ($this->brands as $brand) {
                $categoryTotalPerBrand[$brand] = ['qty' => 0, 'purchase' => 0];
            }

            foreach ($customers as $customer) {
                // Gabungkan nama customer dengan kota
                $customerNameWithCity = $customer['name'] . ' ' . $customer['kota'];

                // Buat row untuk customer
                $row = [
                    '', // Kosongkan kolom kategori
                    $customerNameWithCity,
                ];

                $totalQty = 0; // Inisialisasi total qty untuk customer
                $totalPurchase = 0; // Inisialisasi total omset untuk customer

                // Tambahkan qty dan omset untuk setiap brand
                foreach ($this->brands as $brand) {
                    $brandData = $customer['brand_data'][$brand] ?? ['qty' => 0, 'purchase' => 0];

                    // Tambahkan nilai qty dan purchase ke total per kategori, customer, dan keseluruhan
                    $totalQty += $brandData['qty'];
                    $totalPurchase += $brandData['purchase'];
                    $categoryTotalPerBrand[$brand]['qty'] += $brandData['qty'];
                    $categoryTotalPerBrand[$brand]['purchase'] += $brandData['purchase'];
                    $overallTotalPerBrand[$brand]['qty'] += $brandData['qty'];
                    $overallTotalPerBrand[$brand]['purchase'] += $brandData['purchase'];

                    // Format purchase (omset) dengan number_format
                    $formattedPurchase = number_format($brandData['purchase'], 2, '.', ',');
                    $formattedQty = number_format($brandData['qty'], 2, '.', ',');

                    // Masukkan qty dan omset (format angka) untuk setiap brand
                    $row[] = $formattedQty; // Tambahkan qty untuk brand
                    $row[] = $formattedPurchase; // Tambahkan omset dengan format angka
                }

                // Tambahkan total qty dan total omset di akhir baris
                $row[] = number_format($totalQty, 2, '.', ','); // Total Qty untuk customer
                $row[] = number_format($totalPurchase, 2, '.', ','); // Total Omset untuk customer

                $groupedData[] = $row; // Masukkan baris data customer
            }

            // Tambahkan baris total kategori di bawah semua customer dalam kategori
            $categoryTotalRow = [
                '',
                'Total Category ' . $category,
            ];

            foreach ($this->brands as $brand) {
                // Tambahkan total qty dan omset untuk setiap brand
                $categoryTotalRow[] = number_format($categoryTotalPerBrand[$brand]['qty'], 2, '.', ',');
                $categoryTotalRow[] = number_format($categoryTotalPerBrand[$brand]['purchase'], 2, '.', ',');
            }

            // Tambahkan total qty dan omset untuk kategori di akhir
            $categoryGrandTotalQty = array_sum(array_column($categoryTotalPerBrand, 'qty'));
            $categoryGrandTotalPurchase = array_sum(array_column($categoryTotalPerBrand, 'purchase'));
            $categoryTotalRow[] = number_format($categoryGrandTotalQty, 2, '.', ','); // Total Qty untuk kategori
            $categoryTotalRow[] = number_format($categoryGrandTotalPurchase, 2, '.', ','); // Total Omset untuk kategori

            $groupedData[] = $categoryTotalRow; // Tambahkan baris total kategori
        }

        // Tambahkan baris total keseluruhan di bawah semua data
        $overallTotalRow = [
            '',
            'Total All',
        ];

        foreach ($this->brands as $brand) {
            // Tambahkan total qty dan omset untuk setiap brand
            $overallTotalRow[] = number_format($overallTotalPerBrand[$brand]['qty'], 2, '.', ',');
            $overallTotalRow[] = number_format($overallTotalPerBrand[$brand]['purchase'], 2, '.', ',');
        }

        // Tambahkan total qty dan omset untuk keseluruhan di akhir
        $overallGrandTotalQty = array_sum(array_column($overallTotalPerBrand, 'qty'));
        $overallGrandTotalPurchase = array_sum(array_column($overallTotalPerBrand, 'purchase'));
        $overallTotalRow[] = number_format($overallGrandTotalQty, 2, '.', ','); // Total Qty keseluruhan
        $overallTotalRow[] = number_format($overallGrandTotalPurchase, 2, '.', ','); // Total Omset keseluruhan

        $groupedData[] = $overallTotalRow; // Tambahkan baris total keseluruhan

        return $groupedData;
    }

    public function headings(): array
    {
        // Header dasar yang mencakup Category dan Customer
        $headings = ['Category', 'Customer'];

        // Menambahkan header dinamis untuk setiap brand dengan format 'Brand (qty)' dan 'Omset Brand'
        foreach ($this->brands as $brand) {
            $headings[] = $brand;  // Header untuk qty setiap brand
            $headings[] = 'Omset ' . $brand;  // Header untuk omset setiap brand
        }

        // Menambahkan header untuk total qty dan total omset
        $headings[] = 'Total Qty Customer';
        $headings[] = 'Total Omset Customer';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        // Gaya untuk heading (baris pertama)
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        // Menebalkan baris dengan teks "Total Category" dan "Total All"
        foreach ($sheet->getRowIterator() as $row) {
            $cellValue = $sheet->getCell('B' . $row->getRowIndex())->getValue();
            if (stripos($cellValue, 'Total Category') !== false) {
                $sheet->getStyle('A' . $row->getRowIndex() . ':Z' . $row->getRowIndex())->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF0000FF']], // Warna merah
                ]);
            }

            if (stripos($cellValue, 'Total All') !== false) {
                $sheet->getStyle('A' . $row->getRowIndex() . ':Z' . $row->getRowIndex())->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFF0000']], // Warna merah
                ]);
            }
        }

        return [];
    }
}