<?php

namespace App\Jobs;

use App\Services\TrialMasterSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Push satu store + semua member-nya ke aplikasi trial.
 * Memakai koneksi queue 'database' & nama queue 'trial' agar tidak
 * mengganggu QUEUE_CONNECTION global (notifikasi tetap sync).
 */
class SyncCustomerToTrial implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 8;
    public $timeout = 60;

    public $customerId;
    public $withMedia;

    public function __construct($customerId, $withMedia = true)
    {
        $this->customerId = (int) $customerId;
        $this->withMedia  = (bool) $withMedia;

        $this->onConnection('database');
        $this->onQueue('trial');
    }

    public function backoff()
    {
        return [10, 30, 60, 120, 300, 600, 900];
    }

    public function handle(TrialMasterSyncService $service)
    {
        if (empty(config('services.crm.base_url')) || empty(config('services.crm.key'))) {
            Log::info('Trial master sync dilewati: konfigurasi CRM belum diisi', [
                'customer_id' => $this->customerId,
            ]);
            return;
        }

        if (!$service->push($this->customerId, $this->withMedia)) {
            throw new \RuntimeException('Sync customer ke trial gagal: #' . $this->customerId);
        }
    }

    public function failed(\Throwable $e)
    {
        Log::error('Trial master sync menyerah setelah retry', [
            'customer_id' => $this->customerId,
            'error'       => $e->getMessage(),
        ]);
    }
}