<?php

namespace App\Diagnostics;

use Throwable;
use App\Diagnostics\Checks\DoQtyVsSoCheck;
use App\Diagnostics\Checks\DoNominalCheck;
use App\Diagnostics\Checks\InvoiceConsistencyCheck;
use App\Diagnostics\Checks\StockLedgerCheck;
use App\Diagnostics\Checks\DoStatusTraceCheck;
use App\Diagnostics\Checks\InternalRevisionCheck;
use App\Diagnostics\Checks\PayableConsistencyCheck;
use App\Diagnostics\Checks\FatNotaTtCheck;
use App\Diagnostics\Checks\FatVoidCheck;
use App\Diagnostics\Checks\SystemHealthCheck;

class HealthCheckRunner
{
    public function checks()
    {
        return [
            new DoQtyVsSoCheck(),
            new DoNominalCheck(),
            new InvoiceConsistencyCheck(),
            new StockLedgerCheck(),
            new DoStatusTraceCheck(),
            new InternalRevisionCheck(),
            new PayableConsistencyCheck(),
            new FatNotaTtCheck(),
            new FatVoidCheck(),
            new SystemHealthCheck(),
        ];
    }

    public function run(array $only = [], $sinceDays = 180)
    {
        $results = [];
        $summary = ['critical' => 0, 'warning' => 0, 'info' => 0];

        foreach ($this->checks() as $check) {
            if ($only && !in_array($check->key(), $only, true)) {
                continue;
            }

            $check->setSinceDays($sinceDays);
            $check->reset();
            $started = microtime(true);
            $error = null;

            try {
                $check->run();
            } catch (Throwable $e) {
                $error = $e->getMessage();
                $check->reset();
                $check->addFailure($e->getMessage());
            }

            $counts = ['critical' => 0, 'warning' => 0, 'info' => 0];
            foreach ($check->findings() as $finding) {
                if (isset($counts[$finding['level']])) {
                    $counts[$finding['level']]++;
                }
            }
            foreach ($counts as $level => $count) {
                $summary[$level] += $count;
            }

            $results[] = [
                'key' => $check->key(),
                'label' => $check->label(),
                'counts' => $counts,
                'findings' => $check->findings(),
                'truncated' => $check->isTruncated(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'error' => $error,
            ];
        }

        return [
            'run_at' => now()->toDateTimeString(),
            'since_days' => (int) $sinceDays,
            'summary' => $summary,
            'checks' => $results,
        ];
    }
}
