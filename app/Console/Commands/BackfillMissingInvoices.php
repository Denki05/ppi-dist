<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Finance\Invoicing;
use App\Services\SalesOrder\SalesOrderClosingService;

/**
 * Buat nota (finance_invoicing) untuk DO yang belum punya nota padahal
 * kurs dan grand total sudah valid. Menutup celah lama: ACC Proforma
 * membuat DO status 2 tanpa memanggil createInvoiceIfNeeded (sebelum
 * perbaikan), sehingga nota baru ada saat Checker packed / Edit DO.
 *
 * Hanya MEMBUAT baris yang belum ada. Baris nota yang sudah ada (termasuk
 * terhapus / VOID) tidak disentuh, hanya dilaporkan.
 * Idempoten: dijalankan dua kali tidak membuat nota duplikat.
 *
 *   php artisan invoice:backfill            # dry-run
 *   php artisan invoice:backfill --apply    # terapkan
 */
class BackfillMissingInvoices extends Command
{
    protected $signature = 'invoice:backfill
                            {--id= : Hanya proses satu DO id}
                            {--apply : Buat nota (tanpa flag ini hanya dry-run)}';

    protected $description = 'Buat nota untuk DO yang kurs & grand total-nya valid tetapi belum punya baris finance_invoicing';

    public function handle()
    {
        $query = PackingOrder::whereNull('deleted_at')
            ->whereIn('status', [2, 3, 4, 5, 6]);

        if ($this->option('id')) {
            $query->where('id', (int) $this->option('id'));
        }

        $dos = $query->orderBy('id')->get();
        if ($dos->isEmpty()) {
            $this->error('Tidak ada DO status 2-6 yang cocok.');
            return 1;
        }

        $plans = [];
        $hasNota = 0;
        $skip = 0;
        $single = (bool) $this->option('id');

        foreach ($dos as $do) {
            $detail = PackingOrderDetail::where('do_id', $do->id)->first();

            if (!$detail) {
                $this->line($this->label($do) . ': dilewati — detail cost belum ada');
                $skip++;
                continue;
            }
            if ((float) ($do->idr_rate ?? 0) <= 1) {
                $this->line($this->label($do) . ': dilewati — kurs hold (' . $do->idr_rate . ')');
                $skip++;
                continue;
            }
            if ((float) ($detail->grand_total_idr ?? 0) <= 0) {
                $this->line($this->label($do) . ': dilewati — grand total kosong/nol');
                $skip++;
                continue;
            }

            $existing = Invoicing::withTrashed()->where('do_id', $do->id)->first();
            if ($existing) {
                $hasNota++;
                if ($single) {
                    $state = $existing->trashed() ? 'terhapus' : ('status ' . $existing->status);
                    $this->line($this->label($do) . ': dilewati — nota sudah ada (' . $existing->code . ', ' . $state . ')');
                }
                continue;
            }

            $plans[] = $do;
        }

        $this->info('');
        $this->info('Ringkasan: ' . count($plans) . ' DO akan dibuatkan nota, '
            . $hasNota . ' sudah punya nota, ' . $skip . ' dilewati (detail/kurs/total belum valid).');

        if (!$plans) {
            return 0;
        }

        $this->table(
            ['DO id', 'Kode', 'DO Code', 'Status', 'Kurs', 'Grand Total'],
            array_map(function ($do) {
                $detail = PackingOrderDetail::where('do_id', $do->id)->first();
                return [
                    $do->id,
                    $do->code,
                    $do->do_code,
                    $do->status,
                    number_format((float) $do->idr_rate, 0, ',', '.'),
                    number_format((float) $detail->grand_total_idr, 2, ',', '.'),
                ];
            }, $plans)
        );

        if (!$this->option('apply')) {
            $this->comment('Dry-run: belum ada yang dibuat. Tambahkan --apply untuk menerapkan.');
            return 0;
        }

        if (!$this->confirm('Buat nota untuk ' . count($plans) . ' DO?', false)) {
            $this->comment('Dibatalkan.');
            return 0;
        }

        $service = app(SalesOrderClosingService::class);
        $created = 0;
        $failed = [];

        DB::transaction(function () use ($plans, $service, &$created, &$failed) {
            foreach ($plans as $do) {
                try {
                    if ($service->createInvoiceIfNeeded($do->id)) {
                        $created++;
                    } else {
                        $failed[] = $do->id;
                    }
                } catch (\Throwable $e) {
                    $failed[] = $do->id . ' (' . $e->getMessage() . ')';
                }
            }
        });

        $this->info('Nota dibuat: ' . $created . '.');
        if ($failed) {
            $this->warn('Gagal/tidak valid: ' . implode(', ', $failed));
        }

        return 0;
    }

    private function label($do)
    {
        return ($do->do_code ?: $do->code) . ' (id ' . $do->id . ')';
    }
}