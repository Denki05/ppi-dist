<?php

namespace App\Exports\Gudang;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockValuationExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithStyles
{
    const HEADER_ROW = 3;
    const DATA_START_ROW = 4;

    private $warehouseId;
    private $warehouseName;
    private $rate;
    private $rows = [];
    private $totalUsd = 0;
    private $totalIdr = 0;

    private $brandOrder = ['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X', 'Nginden'];

    public function __construct($warehouseId, $warehouseName, $rate = 18500)
    {
        $this->warehouseId = (int) $warehouseId;
        $this->warehouseName = $warehouseName;
        $this->rate = (float) $rate;
        $this->build();
    }

    private function brandRank($brand)
    {
        $index = array_search($brand, $this->brandOrder, true);

        return $index === false ? count($this->brandOrder) : $index;
    }

    private function cleanText($value)
    {
        $value = (string) $value;
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);
    }

    private function brandExpression()
    {
        if (Schema::hasColumn('master_products', 'brand_name')) {
            return 'm.brand_name';
        }

        if (Schema::hasColumn('master_products', 'brand_lokal_id')) {
            return 'bl.brand_name';
        }

        return DB::raw("'-'");
    }

    private function build()
    {
        $query = DB::table('master_product_min_stocks as s')
            ->join('master_products_packaging as p', 'p.id', '=', 's.product_packaging_id')
            ->join('master_products as m', 'm.id', '=', 'p.product_id')
            ->leftJoin('master_packaging as k', 'k.id', '=', 'p.packaging_id');

        if (!Schema::hasColumn('master_products', 'brand_name') && Schema::hasColumn('master_products', 'brand_lokal_id')) {
            $query->leftJoin('master_brand_lokal as bl', 'bl.id', '=', 'm.brand_lokal_id');
        }

        $stocks = $query
            ->where('s.warehouse_id', $this->warehouseId)
            ->where('s.quantity', '!=', 0)
            ->get([
                'p.id as pack_code',
                'm.name as product_name',
                'brand_name' => $this->brandExpression(),
                'k.pack_name',
                's.quantity',
                'p.price',
            ])
            ->sort(function ($a, $b) {
                $rankA = $this->brandRank($a->brand_name);
                $rankB = $this->brandRank($b->brand_name);

                if ($rankA !== $rankB) {
                    return $rankA <=> $rankB;
                }

                $brandCmp = strcasecmp((string) $a->brand_name, (string) $b->brand_name);
                if ($brandCmp !== 0) {
                    return $brandCmp;
                }

                $nameCmp = strcasecmp((string) $a->product_name, (string) $b->product_name);
                if ($nameCmp !== 0) {
                    return $nameCmp;
                }

                return strnatcmp((string) $a->pack_code, (string) $b->pack_code);
            });

        foreach ($stocks as $stock) {
            $qty = (float) $stock->quantity;
            $price = (float) $stock->price;
            $usd = round($qty * $price, 2);
            $idr = round($qty * $price * $this->rate, 2);

            $this->totalUsd += $usd;
            $this->totalIdr += $idr;

            $this->rows[] = [
                $this->cleanText($stock->brand_name ?: '-'),
                $this->cleanText($stock->pack_code) . ' - ' . $this->cleanText($stock->product_name),
                $this->cleanText($stock->pack_name ?? '-'),
                $price,
                $qty,
                $this->rate,
                $idr,
            ];
        }
    }

    public function array(): array
    {
        $data = [
            ['LAPORAN STOCK & NILAI STOK'],
            [
                'Gudang: ' . $this->warehouseName,
                '',
                'Tanggal: ' . Carbon::now()->format('d-m-Y'),
                '',
                'Kurs: ' . number_format($this->rate, 0, ',', '.'),
            ],
            ['Brand', 'Kode - Nama Produk', 'Kemasan', 'Harga Produk (USD)', 'Stock', 'Kurs', 'Total (IDR)'],
        ];

        foreach ($this->rows as $row) {
            $data[] = $row;
        }

        $data[] = ['TOTAL', '', '', '', '', $this->rate, round($this->totalIdr, 2)];

        return $data;
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0.00',
            'E' => '0.00',
            'F' => '#,##0',
            'G' => '#,##0.00',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalRow = self::DATA_START_ROW + count($this->rows);

        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('C2')->getFont()->setItalic(true);
        $sheet->getStyle('E2')->getFont()->setItalic(true);

        $sheet->getStyle('A' . self::HEADER_ROW . ':G' . self::HEADER_ROW)->getFont()->setBold(true);
        $sheet->getStyle('A' . self::HEADER_ROW . ':G' . self::HEADER_ROW)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
        $sheet->getStyle('A' . self::HEADER_ROW . ':G' . self::HEADER_ROW)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->getFont()->setBold(true);

        foreach ($this->rows as $index => $row) {
            if ($row[4] < 0) {
                $excelRow = self::DATA_START_ROW + $index;
                $sheet->getStyle('A' . $excelRow . ':G' . $excelRow)->getFont()->getColor()->setRGB('C00000');
            }
        }

        $sheet->freezePane('A' . self::DATA_START_ROW);

        return [];
    }
}
