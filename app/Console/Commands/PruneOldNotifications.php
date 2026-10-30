<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PruneOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notif:prune
                            {--do-days=7 : Hapus notifikasi DO yang lebih tua dari X hari (0 = semua)}
                            {--other-days=90 : Hapus notifikasi lain yang lebih tua dari X hari}
                            {--dry-run : Hanya tampilkan jumlah, tidak menghapus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus notifikasi lama agar badge tidak menumpuk (DO default 7 hari, lainnya 90 hari)';

    /**
     * Tipe notifikasi yang dikelola aplikasi ini. Sengaja dibatasi agar
     * notifikasi pihak lain di tabel yang sama tidak ikut terhapus.
     *
     * @var array
     */
    protected $types = [
        'App\Notifications\DoNotification',
        'App\Notifications\SoNotification',
        'App\Notifications\PayableNotification',
        'App\Notifications\ReceivingNotification',
        'App\Notifications\InternalRevisionOtpNotification',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $doDays = (int) $this->option('do-days');
        $otherDays = max(1, (int) $this->option('other-days'));
        $dryRun = (bool) $this->option('dry-run');

        $doQuery = DB::table('notifications')
            ->where('type', 'App\Notifications\DoNotification');

        if ($doDays > 0) {
            $doQuery->where('created_at', '<', Carbon::now()->subDays($doDays));
        }

        $otherQuery = DB::table('notifications')
            ->whereIn('type', array_values(array_diff($this->types, ['App\Notifications\DoNotification'])))
            ->where('created_at', '<', Carbon::now()->subDays($otherDays));

        $doCount = $doQuery->count();
        $otherCount = $otherQuery->count();

        $this->info('Notifikasi DO' . ($doDays > 0 ? " > {$doDays} hari" : ' (semua)') . ": {$doCount}");
        $this->info("Notifikasi lain > {$otherDays} hari: {$otherCount}");

        if ($dryRun) {
            $this->warn('Dry run: tidak ada data yang dihapus.');
            return;
        }

        if ($doCount === 0 && $otherCount === 0) {
            $this->info('Tidak ada notifikasi yang perlu dihapus.');
            return;
        }

        $deletedDo = $doCount > 0 ? $doQuery->delete() : 0;
        $deletedOther = $otherCount > 0 ? $otherQuery->delete() : 0;

        $this->info("Selesai. Dihapus: DO={$deletedDo}, lainnya={$deletedOther}.");
    }
}
