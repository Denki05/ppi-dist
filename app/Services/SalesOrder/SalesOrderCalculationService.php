<?php

namespace App\Services;

use App\Entities\Penjualan\SalesOrder;

class SalesOrderCalculationService
{
    /**
     * Kalkulasi estimasi harga dari SO Awal.
     * Mengembalikan array berisi data hitungan lengkap.
     */
    public function calculateEstimate(SalesOrder $sales_order)
    {
        // Pastikan Kurs valid
        $idr_rate = (float) $sales_order->idr_rate;
        if ($idr_rate <= 0) $idr_rate = 1;

        $items = [];
        $subtotal_item = 0;

        // 1. KALKULASI LEVEL ITEM
        foreach ($sales_order->so_detail as $detail) {
            if ((float)$detail->qty <= 0) continue;

            $qty = (float) $detail->qty;
            $price = (float) $detail->price;
            $disc_usd = (float) $detail->disc_usd;
            $is_free = $detail->free_product == 1;

            // Jika barang free, harga dan diskon 0 sehingga tidak masuk grand total
            if ($is_free) {
                $price = 0;
                $disc_usd = 0;
            }

            // Hitungan: (Harga - Diskon USD) * QTY * KURS
            $total_item_usd = ($price - $disc_usd) * $qty;
            $total_item_idr = $total_item_usd * $idr_rate;

            $subtotal_item += $total_item_idr;

            $items[] = [
                'code'      => $detail->product_pack->code ?? '-',
                'name'      => $detail->product_pack->name ?? '-',
                'qty'       => $qty,
                'packaging' => $detail->packaging->pack_name ?? '-',
                'price_idr' => $price * $idr_rate,
                'disc_idr'  => $disc_usd * $idr_rate,
                'total_idr' => $total_item_idr,
            ];
        }

        // 2. KALKULASI LEVEL GLOBAL (Hanya Disc % saja)
        $disc_percent = (float) $sales_order->disc_percent;

        // Hitung nominal IDR dari diskon % (dari subtotal item)
        $disc_agen_idr = ($subtotal_item * $disc_percent) / 100;
        
        // Grand Total Akhir
        $grand_total = $subtotal_item - $disc_agen_idr;

        return [
            'idr_rate'      => $idr_rate,
            'subtotal'      => $subtotal_item,
            'disc_agen_idr' => $disc_agen_idr,
            'grand_total'   => $grand_total,
            'items'         => $items,
        ];
    }
}