<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Diagnostics\HealthCheckRunner;

class DevHealthCheck extends Command
{
    protected $signature = 'dev:health-check
                            {--json : Keluarkan hasil JSON}
                            {--check= : Batasi ke kode check tertentu, pisahkan dengan koma}
                            {--since=180 : Batasi transaksi N hari terakhir (0 = semua)}';

    protected $description = 'Health check read-only: qty/nominal DO, invoice, stok, status DO, revisi, FAT, sistem';

    public function handle()
    {
        $only = [];
        if ($this->option('check')) {
            $only = array_filter(array_map('trim', explode(',', $this->option('check'))));
        }

        $sinceDays = (int) $this->option('since');
        $result = (new HealthCheckRunner())->run($only, $sinceDays);

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $result['summary']['critical'] > 0 ? 1 : 0;
        }

        $rows = [];
        foreach ($result['checks'] as $check) {
            $rows[] = [
                $check['key'],
                $check['label'],
                $check['counts']['critical'],
                $check['counts']['warning'],
                $check['counts']['info'],
                $check['duration_ms'] . ' ms',
                $check['truncated'] ? 'ya' : '-',
            ];
        }

        $this->info('Health check ' . $result['run_at']
            . ($result['since_days'] > 0 ? ' (transaksi ' . $result['since_days'] . ' hari terakhir)' : ' (semua transaksi)'));
        $this->table(['Check', 'Keterangan', 'Critical', 'Warning', 'Info', 'Durasi', 'Terpotong'], $rows);

        foreach ($result['checks'] as $check) {
            if (empty($check['findings'])) {
                continue;
            }
            $this->line('');
            $this->comment('[' . $check['key'] . '] ' . $check['label']);
            foreach ($check['findings'] as $finding) {
                $prefix = strtoupper($finding['level']) . ': ';
                $line = $prefix . $finding['message'];
                if (!empty($finding['ref'])) {
                    $line .= ' (' . $finding['ref'] . ')';
                }
                if ($finding['level'] === 'critical') {
                    $this->error($line);
                } elseif ($finding['level'] === 'warning') {
                    $this->warn($line);
                } else {
                    $this->line($line);
                }
            }
            if ($check['truncated']) {
                $this->comment('  ... temuan dipotong; persempit dengan --check atau periksa langsung di halaman.');
            }
        }

        $this->line('');
        $this->info('Ringkasan: critical ' . $result['summary']['critical']
            . ', warning ' . $result['summary']['warning']
            . ', info ' . $result['summary']['info']);

        return $result['summary']['critical'] > 0 ? 1 : 0;
    }
}
