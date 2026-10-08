<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Entities\Master\ProductPack;
use DB;

class ProductHighSaleTable extends Table
{
    private function query(Request $request)
    {
        $startDate = $request->start_date . " 00:00:00";
        $endDate = $request->end_date . " 23:59:59";

        $model = ProductPack::leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('penjualan_so_item', 'master_products_packaging.id', '=', 'penjualan_so_item.product_packaging_id')
            ->leftJoin('penjualan_so', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
            ->selectRaw('
                master_products.brand_name AS brand,
                master_products_packaging.id AS product_pack_id,
                master_products_packaging.code AS product_code,
                master_products_packaging.name AS product_name,
                master_packaging.pack_name AS packaging_name,
                master_packaging.id AS packaging_id,
                SUM(penjualan_so_item.qty_worked) AS total_qty
            ')
            ->where('penjualan_so.status', 4)
            ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
            ->groupBy('master_products_packaging.id', 'master_packaging.pack_name');

        if ($request->filled('brand')) {
            $brands = is_array($request->brand) ? $request->brand : explode(',', $request->brand);
            $brands = array_filter($brands, function ($v) { return $v !== '' && $v !== null; });
            if (!empty($brands) && !in_array('all', $brands)) {
                $model->whereIn('master_products.brand_name', $brands);
            }
        }

        if ($request->filled('kemasan')) {
            $kemasan = is_array($request->kemasan) ? $request->kemasan : explode(',', $request->kemasan);
            $kemasan = array_filter($kemasan, function ($v) { return $v !== '' && $v !== null; });
            if (!empty($kemasan) && !in_array('all', $kemasan)) {
                // Mendukung filter by pack_name maupun packaging id
                $model->where(function ($q) use ($kemasan) {
                    $q->whereIn('master_packaging.pack_name', $kemasan)
                      ->orWhereIn('master_packaging.id', $kemasan);
                });
            }
        }

        if ($request->filled('product')) {
            $products = is_array($request->product) ? $request->product : explode(',', $request->product);
            $products = array_filter($products, function ($v) { return $v !== '' && $v !== null; });
            if (!empty($products) && !in_array('all', $products)) {
                $model->whereIn('master_products_packaging.id', $products);
            }
        }

        $rows = $model->get();

        // Hitung total per produk (1 produk bisa banyak kemasan) agar bisa
        // diurutkan per produk tertinggi dan ditampilkan sebagai grup + total.
        $totals = [];
        foreach ($rows as $row) {
            $key = ($row->brand ?? '') . '|' . ($row->product_code ?? '') . '|' . ($row->product_name ?? '');
            $totals[$key] = ($totals[$key] ?? 0) + (float) $row->total_qty;
        }
        foreach ($rows as $row) {
            $key = ($row->brand ?? '') . '|' . ($row->product_code ?? '') . '|' . ($row->product_name ?? '');
            $row->product_total = $totals[$key] ?? (float) $row->total_qty;
        }

        // Urut: total produk tertinggi dulu, lalu qty kemasan terbesar.
        return $rows->sort(function ($a, $b) {
            if ($b->product_total == $a->product_total) {
                return $b->total_qty <=> $a->total_qty;
            }
            return $b->product_total <=> $a->product_total;
        })->values();
    }

    public function build(Request $request)
    {
        // Mode detail ala Crystal Report bila type dipilih:
        // type=1 Semester (Merek > Semester > Variant > Customer),
        // type=2 Zona (Merek > Zona > Variant > Customer).
        if ($request->type == 1) {
            return $this->buildDetail($request, 'semester');
        }
        if ($request->type == 2) {
            return $this->buildDetail($request, 'zona');
        }

        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('variant', function (ProductPack $model) {
            return $model->product_code . ' - ' . $model->product_name;
        });

        $table->addColumn('kemasan', function (ProductPack $model) {
            return $model->packaging_name ?? '-';
        });

        $table->addColumn('product_total', function (ProductPack $model) {
            return (float) ($model->product_total ?? $model->total_qty);
        });

        return $table->make(true);
    }

    /**
     * Query detail per customer meniru Crystal Report
     * (report_high_sell_semester.rpt / report_high_sell_zona.rpt).
     */
    private function queryDetail(Request $request, $mode)
    {
        $startDate = $request->start_date . " 00:00:00";
        $endDate = $request->end_date . " 23:59:59";

        $model = ProductPack::leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('penjualan_so_item', 'master_products_packaging.id', '=', 'penjualan_so_item.product_packaging_id')
            ->leftJoin('penjualan_so', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
            ->leftJoin('master_customer_other_addresses as cust', 'penjualan_so.customer_other_address_id', '=', 'cust.id')
            ->selectRaw("
                master_products.brand_name AS brand,
                master_products_packaging.code AS product_code,
                master_products_packaging.name AS product_name,
                master_packaging.pack_name AS packaging_name,
                cust.name AS customer_name,
                cust.text_kota AS customer_kota,
                cust.zone AS customer_zone,
                YEAR(penjualan_so.so_date) AS year,
                CASE WHEN MONTH(penjualan_so.so_date) <= 6 THEN 1 ELSE 2 END AS semester,
                SUM(penjualan_so_item.qty_worked) AS total_qty
            ")
            ->where('penjualan_so.status', 4)
            ->whereBetween('penjualan_so.so_date', [$startDate, $endDate]);

        // Mode zona: gabung lintas semester (seperti .rpt zona).
        // Mode semester: pisah per semester (seperti .rpt semester).
        if ($mode === 'semester') {
            $model->groupBy('master_products_packaging.id', 'cust.id', 'year', 'semester');
        } else {
            $model->groupBy('master_products_packaging.id', 'cust.id');
        }

        if ($request->filled('brand')) {
            $brands = is_array($request->brand) ? $request->brand : explode(',', $request->brand);
            $brands = array_filter($brands, function ($v) { return $v !== '' && $v !== null; });
            if (!empty($brands) && !in_array('all', $brands)) {
                $model->whereIn('master_products.brand_name', $brands);
            }
        }

        if ($request->filled('kemasan')) {
            $kemasan = is_array($request->kemasan) ? $request->kemasan : explode(',', $request->kemasan);
            $kemasan = array_filter($kemasan, function ($v) { return $v !== '' && $v !== null; });
            if (!empty($kemasan) && !in_array('all', $kemasan)) {
                $model->where(function ($q) use ($kemasan) {
                    $q->whereIn('master_packaging.pack_name', $kemasan)
                      ->orWhereIn('master_packaging.id', $kemasan);
                });
            }
        }

        if ($request->filled('product')) {
            $products = is_array($request->product) ? $request->product : explode(',', $request->product);
            $products = array_filter($products, function ($v) { return $v !== '' && $v !== null; });
            if (!empty($products) && !in_array('all', $products)) {
                $model->whereIn('master_products_packaging.id', $products);
            }
        }

        $rows = $model->get();

        $zoneOrder = [
            'JABODETABEK' => 1,
            'JABAR' => 2,
            'JATENG - JATIM' => 3,
            'SUMATRA' => 4, 'SUMATERA' => 4,
            'BALI - KALIMANTAN - SULAWESI' => 5,
        ];

        // Total per variant dalam grupnya (untuk urutan tertinggi + TOTAL grup)
        $totals = [];
        foreach ($rows as $row) {
            $grupKey = $mode === 'semester'
                ? ($row->year . '-S' . $row->semester)
                : strtoupper(trim((string) $row->customer_zone));
            $key = ($row->brand ?? '') . '|' . $grupKey . '|' . ($row->product_code ?? '') . '|' . ($row->product_name ?? '') . '|' . ($row->packaging_name ?? '');
            $totals[$key] = ($totals[$key] ?? 0) + (float) $row->total_qty;
        }

        foreach ($rows as $row) {
            if ($mode === 'semester') {
                $label = ((int) $row->semester === 1 ? 'Semester 1 (Jan - Jun)' : 'Semester 2 (Jul - Dec)') . ' ' . $row->year;
                $row->grup = $label;
                $row->grup_order = ((int) $row->year) * 10 + (int) $row->semester;
            } else {
                $zone = trim((string) ($row->customer_zone ?? ''));
                $no = $zoneOrder[strtoupper($zone)] ?? 99;
                $row->grup = 'ZONA ' . $no . ' : ' . ($zone !== '' ? $zone : '-');
                $row->grup_order = $no;
            }
            $grupKey = $mode === 'semester'
                ? ($row->year . '-S' . $row->semester)
                : strtoupper(trim((string) $row->customer_zone));
            $key = ($row->brand ?? '') . '|' . $grupKey . '|' . ($row->product_code ?? '') . '|' . ($row->product_name ?? '') . '|' . ($row->packaging_name ?? '');
            $row->variant_total = $totals[$key] ?? (float) $row->total_qty;
            $row->variant_full = ($row->product_code ?? '') . ' - ' . ($row->product_name ?? '') . ' / ' . ($row->packaging_name ?? '-');
            $row->customer = trim(($row->customer_name ?? '') . ' ' . ($row->customer_kota ?? ''));
        }

        // Urut ala Crystal: brand > grup > total variant tertinggi > qty customer tertinggi.
        return $rows->sort(function ($a, $b) {
            if ($a->brand !== $b->brand) {
                return strcmp((string) $a->brand, (string) $b->brand);
            }
            if ($a->grup_order != $b->grup_order) {
                return $a->grup_order <=> $b->grup_order;
            }
            if ($b->variant_total != $a->variant_total) {
                return $b->variant_total <=> $a->variant_total;
            }
            return $b->total_qty <=> $a->total_qty;
        })->values();
    }

    private function buildDetail(Request $request, $mode)
    {
        $table = Table::of($this->queryDetail($request, $mode));

        $table->addIndexColumn();

        return $table->make(true);
    }
}