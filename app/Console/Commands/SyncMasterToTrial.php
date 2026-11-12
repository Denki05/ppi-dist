<?php

namespace App\Console\Commands;

use App\Entities\Master\Customer;
use App\Jobs\SyncCustomerToTrial;
use App\Services\TrialMasterSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Backfill / sinkron ulang master store + member ke aplikasi trial.
 *
 * Contoh:
 *   php artisan trial:sync-master --dry-run
 *   php artisan trial:sync-master --sync --chunk=50
 *   php artisan trial:sync-master --id=27 --sync
 *   php artisan trial:sync-master --since=2026-11-01 --sync --no-media
 */
class SyncMasterToTrial extends Command
{
    protected $signature = 'trial:sync-master
        {--id= : Sinkron satu customer id saja}
        {--since= : Hanya data yang berubah sejak tanggal ini (Y-m-d)}
        {--chunk=100 : Jumlah customer per batch}
        {--sync : Kirim langsung (tanpa antrean)}
        {--no-media : Jangan ikut mengunduh file gambar}
        {--dry-run : Tampilkan rencana tanpa mengirim}';

    protected $description = 'Sinkronkan master store + member dari trans ke aplikasi trial (lssoft88.xyz)';

    public function handle()
    {
        if (empty(config('services.trial_master.base_url')) || empty(config('services.trial_master.key'))) {
            $this->error('TRIAL_MASTER_BASE_URL / TRIAL_MASTER_API_KEY belum dikonfigurasi. Sync dibatalkan.');
            return 1;
        }

        $query = Customer::query()->orderBy('id');

        if ($this->option('id')) {
            $query->where('id', (int) $this->option('id'));
        }

        if ($since = $this->option('since')) {
            try {
                $sinceDate = Carbon::parse($since)->startOfDay();
            } catch (\Throwable $e) {
                $this->error('Format --since tidak valid. Gunakan Y-m-d, contoh 2026-11-01.');
                return 1;
            }

            $query->where(function ($q) use ($sinceDate) {
                $q->where('updated_at', '>=', $sinceDate)
                    ->orWhereHas('member', function ($m) use ($sinceDate) {
                        $m->where('updated_at', '>=', $sinceDate);
                    });
            });
        }

        $chunk    = max(1, (int) $this->option('chunk'));
        $dryRun   = (bool) $this->option('dry-run');
        $inline   = (bool) $this->option('sync');
        $withMedia = !$this->option('no-media');

        $total = 0;
        $sent  = 0;
        $failed = 0;

        $service = app(TrialMasterSyncService::class);

        $query->chunkById($chunk, function ($customers) use (
            &$total, &$sent, &$failed, $dryRun, $inline, $withMedia, $service
        ) {
            foreach ($customers as $customer) {
                $total++;

                if ($dryRun) {
                    $this->line(sprintf('  [dry-run] #%s %s', $customer->id, $customer->name));
                    continue;
                }

                if ($inline) {
                    if ($service->push($customer->id, $withMedia)) {
                        $sent++;
                        $this->line(sprintf('  OK   #%s %s', $customer->id, $customer->name));
                    } else {
                        $failed++;
                        $this->warn(sprintf('  GAGAL #%s %s', $customer->id, $customer->name));
                    }
                    continue;
                }

                SyncCustomerToTrial::dispatch($customer->id, $withMedia);
                $sent++;
            }
        });

        $this->info(sprintf(
            'Selesai: %d customer diproses, %d dikirim%s.%s',
            $total,
            $sent,
            $inline ? '' : ' ke antrean (jalankan queue:work --queue=trial)',
            $failed > 0 ? ' Gagal: ' . $failed . ' (lihat log).' : ''
        ));

        return $failed > 0 ? 1 : 0;
    }
}