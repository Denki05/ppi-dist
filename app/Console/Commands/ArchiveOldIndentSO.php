<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Entities\Penjualan\SalesOrder;
use Carbon\Carbon;
use Log;

class ArchiveOldIndentSO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'so:archive-old-indent {--days=14 : Jumlah hari sebelum diarsipkan (default: 14)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Arsipkan (invisible) SO Indent yang sudah lebih dari X hari';

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

        $this->info("Mencari SO Indent yang dibuat sebelum {$cutoffDate->format('d-m-Y')}...");

        // Cari SO Indent yang sudah lebih dari X hari dan belum diarsipkan
        $oldIndentSOs = SalesOrder::where('so_indent', SalesOrder::INDENT['YES'])
            ->where('is_archived', 0)
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($oldIndentSOs->isEmpty()) {
            $this->info('Tidak ada SO Indent yang perlu diarsipkan.');
            return;
        }

        $this->info("Ditemukan {$oldIndentSOs->count()} SO Indent untuk diarsipkan.");

        $archivedCount = 0;

        foreach ($oldIndentSOs as $so) {
            $so->update([
                'is_archived' => 1,
                'archived_at' => now(),
            ]);
            $archivedCount++;
            $this->line("  ✓ {$so->so_code} berhasil diarsipkan.");
        }

        $this->info("Selesai: {$archivedCount} SO Indent diarsipkan (invisible).");
    }
}
