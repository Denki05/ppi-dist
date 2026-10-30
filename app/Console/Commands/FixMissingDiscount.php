<?php

namespace App\Console\Commands;

use App\Services\Finance\InvoiceDiscountAuditService;
use Illuminate\Console\Command;

class FixMissingDiscount extends Command
{
    protected $signature = 'payable:fix-missing-discount
                            {--fix : Terapkan perbaikan (tanpa flag ini hanya laporan dry-run)}
                            {--codes= : Batasi ke kode nota, dipisah koma (cth: 6H077,6H078)}
                            {--customer= : Filter nama customer/address (LIKE), kosong = semua customer}
                            {--year= : Batasi tahun invoice berdasar tanggal buat (cth: 2026)}
                            {--unpaid-only : Hanya tampilkan yang belum lunas (sembunyikan yang sudah dibayar)}
                            {--problems-only : Hanya tampilkan yang bermasalah (FIX/SKIP), sembunyikan yang OK}
                            {--include-paid : Sertakan yang sudah dibayar HANYA bila total bayar pas sama dengan seharusnya (sisa hantu jadi nol)}';

    protected $description = 'Scan semua invoice (default semua customer): betulkan yang diskon persennya tersimpan tapi tidak dikurangkan dari grand total';

    public function handle(InvoiceDiscountAuditService $audit)
    {
        $isFix = (bool) $this->option('fix');
        $codesOpt = $this->option('codes');
        $customerOpt = $this->option('customer');

        $codes = $codesOpt
            ? array_values(array_filter(array_map('trim', explode(',', $codesOpt))))
            : [];

        $yearOpt = $this->option('year');
        $includePaid = (bool) $this->option('include-paid');

        $invoices = $audit->scan(
            $codes,
            $yearOpt ? (int) $yearOpt : null,
            $customerOpt ?: null,
            $includePaid
        );

        if (empty($invoices)) {
            $this->warn('Tidak ada invoice yang cocok dengan filter.');
            return 0;
        }

        $unpaidOnly = (bool) $this->option('unpaid-only');
        $problemsOnly = (bool) $this->option('problems-only');

        $rows = [];
        $nFix = 0;
        $nOk = 0;
        $nSkip = 0;
        $totalSelisih = 0;

        foreach ($invoices as $result) {
            // Sembunyikan angka rekomendasi untuk kasus yang harus diputuskan manual.
            if ($result['action'] === 'SKIP' && $result['paid'] > 0) {
                $result['expected'] = null;
                $result['selisih'] = null;
            }

            // Mode belum-lunas: sembunyikan baris yang sudah ada pembayaran.
            if ($unpaidOnly && $result['action'] === 'SKIP' && strpos($result['note'], 'sudah ada pembayaran') === 0) {
                continue;
            }

            if ($result['action'] === 'OK') {
                $nOk++;
                if ($problemsOnly) {
                    continue;
                }
            } elseif ($result['action'] === 'SKIP') {
                $nSkip++;
            } elseif ($result['action'] === 'FIX' && $isFix) {
                $audit->applyFix($result);
                $nFix++;
                $totalSelisih += $result['selisih'];
            } elseif ($result['action'] === 'FIX') {
                $totalSelisih += $result['selisih'];
            }

            $rows[] = [
                $result['code'],
                $result['addr'],
                number_format((float) $result['inv_grand'], 2, '.', ','),
                $result['expected'] === null ? '-' : number_format((float) $result['expected'], 2, '.', ','),
                $result['selisih'] === null ? '-' : number_format((float) $result['selisih'], 2, '.', ','),
                $result['action'] . ($result['note'] ? ' (' . $result['note'] . ')' : ''),
            ];
        }

        $this->table(['Nota', 'Customer', 'Tagihan DB', 'Seharusnya', 'Selisih', 'Aksi'], $rows);
        $this->info("OK: $nOk, Perlu FIX: " . count(array_filter($rows, function ($r) { return strpos($r[5], 'FIX') === 0; })) . ", SKIP: $nSkip"
            . ($isFix ? ", Diperbaiki: $nFix" : ' (dry-run, tambah --fix untuk menerapkan)'));
        $this->info('Total selisih tagihan: ' . number_format((float) $totalSelisih, 2, '.', ','));

        return 0;
    }
}
