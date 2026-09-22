<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Push progress SO ke Modul AO (best-effort, tidak boleh menggagalkan admin).
 * Dipanggil setelah commit: lanjutkan / kembali(revisi) / tidak_lanjut / tutup_so.
 *
 * Konfigurasi (.env transaksi):
 *   AO_CALLBACK_URL=https://sys-af.lsfragrance.id/api/ao/order-progress
 *   AO_CALLBACK_KEY=warungkopi@123
 *
 * Event: LANJUTAN | REVISI | TUTUP | INVOICE
 */
class AoProgressPushService
{
    public static function push($salesOrder, $event, array $extra = [])
    {
        $url = config('services.ao_callback.url');
        if (!$url) {
            return; // belum dikonfigurasi -> skip diam-diam (polling AO tetap jalan)
        }

        $payload = array_merge([
            'so_code' => $salesOrder->so_code,
            'transaksi_so_id' => $salesOrder->id,
            'event' => $event,
            'status' => $salesOrder->status,
            'status_text' => \App\Entities\Penjualan\SalesOrder::STEP[$salesOrder->status] ?? (string) $salesOrder->status,
            'nota_code' => $salesOrder->code,
        ], $extra);

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 5,
                'connect_timeout' => 3,
                'verify' => false,
                'http_errors' => false,
            ]);
            $res = $client->post($url, [
                'json' => $payload,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-AO-KEY' => config('services.ao_callback.key'),
                ],
            ]);
            Log::info('AO progress pushed', ['so' => $salesOrder->so_code, 'event' => $event, 'http' => $res->getStatusCode()]);
        } catch (\Exception $e) {
            // Best-effort: admin tidak boleh gagal hanya karena AO timeout.
            Log::warning('AO progress push gagal (diabaikan)', ['so' => $salesOrder->so_code ?? null, 'event' => $event, 'error' => $e->getMessage()]);
        }
    }
}
