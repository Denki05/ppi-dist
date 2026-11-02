<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Push perubahan member (customer other address) ke database online (CRM).
 * Best-effort: kegagalan CRM tidak boleh menggagalkan import di trans.
 *
 * Konfigurasi di server trans:
 *   CRM_BASE_URL=https://lssoft88.xyz/api
 *   CRM_API_KEY=<secret>   (opsional; default memakai USER_API_KEY)
 */
class MemberSyncService
{
    public static function push(array $payload)
    {
        $baseUrl = config('services.crm.base_url');
        $apiKey  = config('services.crm.key');

        if (empty($payload['member_id']) || empty($baseUrl) || empty($apiKey)) {
            return false;
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout'         => 5,
                'connect_timeout' => 3,
                'http_errors'     => false,
                'verify'          => true,
            ]);

            $response = $client->post(rtrim($baseUrl, '/') . '/members/update', [
                'headers' => [
                    'X-API-KEY' => $apiKey,
                    'Accept'    => 'application/json',
                ],
                'json' => $payload,
            ]);

            $status = $response->getStatusCode();
            if ($status >= 400) {
                Log::warning('Member sync CRM gagal', [
                    'member_id' => $payload['member_id'],
                    'http'      => $status,
                    'body'      => substr((string) $response->getBody(), 0, 300),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Member sync CRM error (diabaikan)', [
                'member_id' => $payload['member_id'],
                'error'     => $e->getMessage(),
            ]);
            return false;
        }
    }
}
