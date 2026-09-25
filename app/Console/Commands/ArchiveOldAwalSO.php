<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Entities\Penjualan\SalesOrder;
use Carbon\Carbon;
use Log;

class ArchiveOldAwalSO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'so:archive-old-awal {--days=7 : Jumlah hari sebelum diarsipkan (default: 7)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Arsipkan (invisible) SO Awal CASH/TEMPO yang masih status AWAL setelah X hari';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Mencari SO Awal CASH/TEMPO yang dibuat sebelum {$cutoffDate->format('d-m-Y')}...");

        // Cari SO Awal (bukan indent) yang masih status AWAL dan belum diarsipkan
        $oldAwalSOs = SalesOrder::where('so_indent', SalesOrder::INDENT['NO'])
            ->where('is_archived', 0)
            ->where('status', 1) // Status AWAL saja
            ->whereIn('type_transaction', ['CASH', 'TEMPO']) // CASH atau TEMPO (string)
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($oldAwalSOs->isEmpty()) {
            $this->info('Tidak ada SO Awal yang perlu diarsipkan.');
            return;
        }

        $this->info("Ditemukan {$oldAwalSOs->count()} SO Awal untuk diarsipkan.");

        $archivedCount = 0;

        foreach ($oldAwalSOs as $so) {
            $so->update([
                'is_archived' => 1,
                'archived_at' => now(),
            ]);
            $archivedCount++;
            $this->line("  ✓ {$so->so_code} berhasil diarsipkan.");
        }

        $this->info("Selesai: {$archivedCount} SO Awal diarsipkan (invisible).");
    }
}
