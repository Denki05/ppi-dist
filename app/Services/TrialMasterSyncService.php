<?php

namespace App\Services;

use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use Illuminate\Support\Facades\Log;

/**
 * Push master store (parent) + member (child) ke aplikasi trial (lssoft88.xyz).
 * Payload berisi semua kolom tabel + daftar file gambar; receiver di trial
 * melakukan upsert idempotent berdasarkan id.
 *
 * Konfigurasi di server trans:
 *   TRIAL_MASTER_BASE_URL=https://drive.lssoft88.xyz/api
 *   TRIAL_MASTER_API_KEY=<secret>   (opsional; fallback CRM_API_KEY / USER_API_KEY)
 */
class TrialMasterSyncService
{
    public function push($customerId, $withMedia = true)
    {
        $baseUrl = config('services.trial_master.base_url');
        $apiKey  = config('services.trial_master.key');

        if (empty($customerId) || empty($baseUrl) || empty($apiKey)) {
            return false;
        }

        $customer = Customer::with('category')->find($customerId);
        if (!$customer) {
            return false;
        }

        $members = CustomerOtherAddress::where('customer_id', $customerId)->get();

        $payload = [
            'customer' => $this->attributesOf($customer),
            'members'  => [],
            'media'    => [],
        ];

        if ($customer->category) {
            $payload['customer']['category'] = [
                'id'    => $customer->category->id,
                'code'  => $customer->category->code,
                'name'  => $customer->category->name,
                'score' => $customer->category->score,
            ];
        }

        foreach ($members as $member) {
            $payload['members'][] = $this->attributesOf($member);
        }

        if ($withMedia) {
            $payload['media'] = $this->collectMedia($customer, $members);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout'         => 30,
                'connect_timeout' => 10,
                'http_errors'     => false,
                'verify'          => true,
            ]);

            $response = $client->post(rtrim($baseUrl, '/') . '/customers/sync', [
                'headers' => [
                    'X-API-KEY' => $apiKey,
                    'Accept'    => 'application/json',
                ],
                'json' => $payload,
            ]);

            $status = $response->getStatusCode();
            if ($status >= 400) {
                Log::warning('Trial master sync gagal', [
                    'customer_id' => $customerId,
                    'http'        => $status,
                    'body'        => substr((string) $response->getBody(), 0, 300),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Trial master sync error', [
                'customer_id' => $customerId,
                'error'       => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Ambil raw attributes (semua kolom) + normalisasi Carbon ke string.
     */
    private function attributesOf($model)
    {
        $attributes = [];
        foreach ($model->getAttributes() as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    private function collectMedia(Customer $customer, $members)
    {
        $media = [];

        foreach (['image_ktp', 'image_npwp', 'image_store'] as $column) {
            $filename = (string) $customer->getAttribute($column);
            if ($filename === '') {
                continue;
            }

            if (!file_exists(public_path(Customer::$directory_image . $filename))) {
                continue;
            }

            $media[] = [
                'table'    => 'master_customers',
                'filename' => $filename,
                'url'      => asset(Customer::$directory_image . $filename),
            ];
        }

        foreach ($members as $member) {
            foreach (['image_ktp', 'image_npwp'] as $column) {
                $filename = (string) $member->getAttribute($column);
                if ($filename === '') {
                    continue;
                }

                if (!file_exists(public_path(CustomerOtherAddress::$directory_image . $filename))) {
                    continue;
                }

                $media[] = [
                    'table'    => 'master_customer_other_addresses',
                    'filename' => $filename,
                    'url'      => asset(CustomerOtherAddress::$directory_image . $filename),
                ];
            }
        }

        return $media;
    }
}