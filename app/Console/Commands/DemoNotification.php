<?php

namespace App\Console\Commands;

use App\Entities\Penjualan\PengajuanProforma;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Notifikasi contoh untuk menguji lonceng, kartu pengingat, dan toast.
 *
 *   php artisan notif:demo
 *   php artisan notif:demo --user=nia
 *   php artisan notif:demo --clean
 *
 * Hanya untuk pengujian; baris demo diberi kode DEMO- dan selalu
 * dibersihkan sebelum data baru dibuat supaya tidak menumpuk.
 * Opsi --clean menghapus semua notifikasi demo + dummy pengajuan.
 */
class DemoNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notif:demo
                            {--user=developer : Username penerima notifikasi demo}
                            {--clean : Hapus semua notifikasi demo + dummy pengajuan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buat notifikasi contoh untuk menguji lonceng, kartu pengingat, dan toast';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('clean')) {
            $notif = DB::table('notifications')
                ->where('data', 'like', '%DEMO-%')
                ->delete();
            $pengajuan = PengajuanProforma::where('estimate_number', 'DEMO-EST-001')->delete();

            $this->info("Demo dibersihkan: {$notif} notifikasi, {$pengajuan} pengajuan dummy.");
            return;
        }

        $username = $this->option('user');

        $user = DB::table('superusers')->where('username', $username)->first();

        if (!$user) {
            $this->error("User '{$username}' tidak ditemukan. Cek daftar username di tabel superusers.");
            return;
        }

        $now = Carbon::now();

        $deleted = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('data', 'like', '%DEMO-%')
            ->delete();

        // Dummy pengajuan proforma supaya notifikasi "Pengajuan Proforma"
        // punya halaman detail yang benar-benar bisa dibuka saat diklik.
        // firstOrCreate: id tetap sama antar-run, jadi notif demo tidak
        // menunjuk ke baris yang sudah terhapus.
        $demoPengajuan = PengajuanProforma::firstOrCreate(
            ['estimate_number' => 'DEMO-EST-001'],
            [
                'ao_pic' => $user->username,
                'prospect_name' => 'Prospek Demo Parfum',
                'perusahaan' => 'PT Demo Parfum',
                'owner' => 'Budi Demo',
                'phone' => '081234567890',
                'address' => 'Jl. Contoh No. 1',
                'city' => 'SURABAYA',
                'provinsi' => 'JAWA TIMUR',
                'kecamatan' => 'GUBENG',
                'kelurahan' => 'AIRLANGGA',
                'zone' => 'Jawa Timur',
                'kategori' => 'Umum (semua prospek)',
                'ktp' => null,
                'npwp' => null,
                'termin' => 'CASH',
                'foto_ktp_ada' => false,
                'foto_npwp_ada' => false,
                'bukti_ada' => true,
                'grand_total' => 2500000,
                'items_count' => 2,
                'items_json' => json_encode([
                    ['code' => 'DEMO-01', 'name' => 'Parfum Demo 100ml', 'qty' => 2, 'price' => 1000000, 'total' => 2000000],
                    ['code' => 'DEMO-02', 'name' => 'Botol Demo 50ml', 'qty' => 5, 'price' => 100000, 'total' => 500000],
                ]),
                'submitted_at' => $now,
                'status' => PengajuanProforma::STATUS_MENUNGGU,
                'catatan' => 'DEMO - data contoh notifikasi, aman dihapus.',
            ]
        );

        $samples = [
            [
                'minutes_ago' => 5,
                'type' => 'App\Notifications\DoNotification',
                'data' => [
                    'id' => 0,
                    'code' => 'DEMO-DO-001',
                    'customer' => 'Customer Demo',
                    'customer_kota' => 'SURABAYA',
                    'status' => 6,
                ],
            ],
            [
                'minutes_ago' => 4,
                'type' => 'App\Notifications\SoNotification',
                'data' => [
                    'id' => 0,
                    'code' => 'DEMO-SO-001',
                    'customer' => 'Toko Demo Jaya',
                    'customer_kota' => 'CIREBON',
                    'type_transaction' => 'TEMPO',
                    'kind' => 'proforma',
                ],
            ],
            [
                'minutes_ago' => 3,
                'type' => 'App\Notifications\SoNotification',
                'data' => [
                    'id' => 0,
                    'code' => 'DEMO-SO-002',
                    'customer' => 'Apotek Demo Sehat',
                    'customer_kota' => 'MALANG',
                    'type_transaction' => 'CASH',
                    'kind' => 'lanjutan',
                ],
            ],
            [
                // Menu Prospek/Estimate: pengajuan proforma dari AO.
                'minutes_ago' => 2,
                'type' => 'App\Notifications\PengajuanProformaNotification',
                'data' => [
                    'id' => $demoPengajuan->id,
                    'code' => 'DEMO-EST-001',
                    'customer' => 'Prospek Demo Parfum',
                    'customer_kota' => 'SURABAYA',
                    'type_transaction' => 'CASH',
                    'prospect_name' => 'Prospek Demo Parfum',
                    'perusahaan' => 'PT Demo Parfum',
                    'ao_pic' => $demoPengajuan->ao_pic,
                    'grand_total' => 2500000,
                    'priority' => 'action',
                ],
            ],
            [
                // Prioritas Mendesak: memicu bunyi dua nada + pengingat.
                'minutes_ago' => 1,
                'type' => 'App\Notifications\InternalRevisionOtpNotification',
                'data' => [
                    // Kode DEMO- dipakai juga oleh pembersih agar baris OTP
                    // tidak menumpuk saat perintah dijalankan berulang.
                    'code' => 'DEMO-OTP-001',
                    'otp' => '482913',
                    'message' => 'Kode OTP revisi internal. Berlaku 5 menit.',
                ],
            ],
        ];

        $rows = [];

        foreach ($samples as $sample) {
            $createdAt = $now->copy()->subMinutes($sample['minutes_ago']);

            $data = $sample['data'];
            $data['created_at'] = $createdAt->toDateTimeString();

            $rows[] = [
                'id' => Str::uuid()->toString(),
                'type' => $sample['type'],
                'notifiable_type' => 'App\Entities\Account\Superuser',
                'notifiable_id' => $user->id,
                'data' => json_encode($data),
                'read_at' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        DB::table('notifications')->insert($rows);

        if ($deleted > 0) {
            $this->line("Demo lama dibersihkan: {$deleted} baris.");
        }

        $this->info(count($rows)." notifikasi demo dibuat untuk {$user->name} ({$user->username}).");
        $this->line('5 baris: 1 Mendesak (OTP), 3 Perlu tindakan (SO + Pengajuan Proforma), 1 Informasi (DO).');
        $this->line('Pengajuan Proforma memakai dummy estimate DEMO-EST-001 (bisa diklik ke halaman detail).');
        $this->line('Buka trans lalu refresh: modal pengingat (hari ini & kemarin) muncul dan lonceng berkedip.');
        $this->line('Klik di halaman sekali agar browser mengizinkan suara, lalu tes lewat tombol "Tes suara" di panel lonceng.');
        $this->line('Jalankan ulang perintah ini kapan saja untuk memunculkan notifikasi baru lagi.');
    }
}
