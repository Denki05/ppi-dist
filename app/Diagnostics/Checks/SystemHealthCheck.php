<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Diagnostics\Check;

class SystemHealthCheck extends Check
{
    public function key()
    {
        return 'system_health';
    }

    public function label()
    {
        return 'Error live, job gagal, heartbeat scheduler';
    }

    public function run()
    {
        if (Schema::hasTable('dev_error_events')) {
            $errors = DB::table('dev_error_events')
                ->where('is_resolved', 0)
                ->where('last_seen_at', '>=', now()->subDay())
                ->orderBy('last_seen_at', 'desc')
                ->limit(20)
                ->get();

            foreach ($errors as $error) {
                $this->add(
                    'warning',
                    $error->exception_class . ' di ' . $error->file . ':' . $error->line
                    . ' (' . $error->occurrences . 'x, terakhir ' . $error->last_seen_at . '): '
                    . mb_substr(trim(explode("\n", (string) $error->message)[0]), 0, 180) . '.',
                    $error->url
                );
            }
        } else {
            $this->add('info', 'Tabel dev_error_events belum ada (migrasi belum dijalankan).', 'system');
        }

        if (Schema::hasTable('failed_jobs')) {
            $failed = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(5)->get();
            foreach ($failed as $job) {
                $exception = trim(explode("\n", (string) $job->exception)[0]);
                $this->add(
                    'warning',
                    'Job gagal pada ' . $job->failed_at . ': ' . mb_substr($exception, 0, 200),
                    'failed_jobs #' . $job->id
                );
            }
        }

        if (!Schema::hasTable('dev_heartbeats')) {
            $this->add('info', 'Tabel dev_heartbeats belum ada (migrasi belum dijalankan).', 'system');
            return;
        }

        if (!config('health.expect_scheduler', true)) {
            $this->add(
                'info',
                'Pemantauan scheduler dimatikan (HEALTH_EXPECT_SCHEDULER=false) - sesuai untuk lingkungan tanpa cron.',
                'scheduler'
            );
            return;
        }

        $staleMinutes = (int) config('health.scheduler_stale_minutes', 15);
        $heartbeat = DB::table('dev_heartbeats')->where('name', 'scheduler')->first();

        if (!$heartbeat || !$heartbeat->last_run_at) {
            $this->add(
                'warning',
                'Heartbeat scheduler belum pernah tercatat. Pastikan cron menjalankan schedule:run.',
                'scheduler'
            );
        } elseif (now()->diffInMinutes($heartbeat->last_run_at) > $staleMinutes) {
            $this->add(
                'warning',
                'Heartbeat scheduler terakhir ' . $heartbeat->last_run_at
                . ' (lebih dari ' . $staleMinutes . ' menit).',
                'scheduler'
            );
        }
    }
}
