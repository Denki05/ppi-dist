<?php

namespace App\Exports\Reports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerTypeBrandReportUvExport implements FromArray, WithHeadings, WithStyles
{
    protected $groupedData;
    protected $globalTotals;
    protected $brands;

    public function __construct($groupedData, $globalTotals, $brands)
    {
        $this->groupedData = $groupedData;
        $this->globalTotals = $globalTotals;
        $this->brands = $brands;
    }

    public function array(): array
    {
        $output = [];

        foreach ($this->groupedData as $kategori => $data) {
            $customers = $data['items']->groupBy('customer_name');

            // Tambahkan baris kategori (kosongkan cell lainnya)
            $output[] = [$kategori];

            foreach ($customers as $customer_name => $items) {
                $row = [
                    '', // kategori kolom kosong
                    $customer_name . ' (' . $items->first()->customer_kota . ')',
                ];

                foreach ($this->brands as $brand) {
                    $qty = $items->where('invoice_brand', $brand)->sum('total_qty');
                    $purchase = $items->where('invoice_brand', $brand)->sum('total_purchase');
                    $row[] = number_format($qty, 2, '.', ',');
                    $row[] = number_format($purchase, 2, '.', ',');
                }

                // Total customer
                $row[] = number_format($items->sum('total_qty'), 2, '.', ',');
                $row[] = number_format($items->sum('total_purchase'), 2, '.', ',');

                $output[] = $row;
            }

            // Total kategori
            $totalRow = ['', 'Total ' . $kategori];
            foreach ($this->brands as $brand) {
                $totalRow[] = number_format($data['totals']["total_{$brand}_qty"], 2, '.', ',');
                $totalRow[] = number_format($data['totals']["total_{$brand}_purchase"], 2, '.', ',');
            }
            $totalRow[] = number_format($data['totals']['total_customer_qty'], 2, '.', ',');
            $totalRow[] = number_format($data['totals']['total_customer_purchase'], 2, '.', ',');
            $output[] = $totalRow;
        }

        // Total global
        $grandTotalRow = ['', 'Total Keseluruhan'];
        foreach ($this->brands as $brand) {
            $grandTotalRow[] = number_format($this->globalTotals["total_{$brand}_qty"], 2, '.', ',');
            $grandTotalRow[] = number_format($this->globalTotals["total_{$brand}_purchase"], 2, '.', ',');
        }
        $grandTotalRow[] = number_format($this->globalTotals['total_customer_qty'], 2, '.', ',');
        $grandTotalRow[] = number_format($this->globalTotals['total_customer_purchase'], 2, '.', ',');
        $output[] = $grandTotalRow;

        return $output;
    }

    public function headings(): array
    {
        $headings = ['Kategori', 'Customer'];

        foreach ($this->brands as $brand) {
            $headings[] = $brand . ' Qty';
            $headings[] = $brand . ' Omset';
        }

        $headings[] = 'Total Qty';
        $headings[] = 'Total Omset';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
            $cellValue = $sheet->getCell('B' . $rowIndex)->getValue();

            if (stripos($cellValue, 'Total ') !== false) {
                $sheet->getStyle("A{$rowIndex}:Z{$rowIndex}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['argb' => 'FFD9E1F2'],
                    ],
                ]);
            }
        }

        return [];
    }
}