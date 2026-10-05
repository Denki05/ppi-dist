<?php

namespace App\Services\Penjualan;

use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Penjualan\PengajuanProforma;
use App\Repositories\CodeRepo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Menangani verifikasi dan mutasi pengajuan_proforma
 * ke master_customers (parent) + master_customer_other_addresses (child).
 *
 * Field mapping:
 *   pengajuan_proforma         → master_customers (parent store)
 *   perusahaan / prospect_name → name
 *   owner                      → owner_name
 *   phone                      → phone
 *   ktp                        → ktp
 *   npwp                       → npwp
 *   address, city              → address, kota / text_kota
 *   provinsi/kecamatan/kelurahan → teks wilayah
 *
 *   child (member/other_address) menerima data yang sama
 *   dengan member_default = YES dan account_representative = ao_pic.
 */
class PengajuanMutasiService
{
    /** Field wajib di pengajuan_proforma sebelum mutasi bisa dijalankan */
    private const REQUIRED_FIELDS = [
        'prospect_name',
        'phone',
        'address',
        'city',
        'ktp',
    ];

    // ------------------------------------------------------------------
    // PUBLIC: CEK KELENGKAPAN FIELD
    // ------------------------------------------------------------------

    /**
     * Validasi kelengkapan field pengajuan vs kebutuhan master_customers.
     * Mengembalikan array kosong jika lolos, atau array pesan error.
     */
    public function validateFieldKelengkapan(PengajuanProforma $p): array
    {
        $errors = [];

        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty(trim((string) $p->{$field}))) {
                $errors[] = "Field '{$field}' wajib diisi sebelum mutasi.";
            }
        }

        if (!empty($p->ktp) && !preg_match('/^[0-9]{16}$/', (string) $p->ktp)) {
            $errors[] = 'KTP harus 16 digit angka.';
        }

        if (!empty($p->npwp) && !preg_match('/^[0-9]{15}$/', (string) $p->npwp)) {
            $errors[] = 'NPWP harus 15 digit angka bila diisi.';
        }

        return $errors;
    }

    // ------------------------------------------------------------------
    // PUBLIC: CEK DUPLIKAT
    // ------------------------------------------------------------------

    /**
     * Cek apakah phone, KTP, atau NAMA sudah ada di master_customers /
     * master_customer_other_addresses. Mengembalikan array info duplikat
     * (kosong = tidak ada). Dipakai halaman verifikasi (Paket B poin 11:
     * cek SEMUA data pengajuan, bukan cuma HP/KTP).
     *
     * Format:
     * [
     *   ['table' => 'master_customers', 'field' => 'phone', 'id' => 12, 'name' => 'Toko ABC'],
     *   ...
     * ]
     */
    public function cekDuplikat(PengajuanProforma $p): array
    {
        $hits = [];

        // --- parent: phone / KTP ---
        $parentQuery = Customer::withTrashed();
        $parentOr = [];
        if (!empty($p->phone))  $parentOr[] = ['phone', '=', $p->phone];
        if (!empty($p->ktp))    $parentOr[] = ['ktp',   '=', $p->ktp];

        foreach ($parentOr as $cond) {
            $found = $parentQuery->where($cond[0], $cond[2])->first(['id', 'name', 'phone', 'ktp', 'deleted_at']);
            if ($found) {
                $hits[] = [
                    'table'      => 'master_customers',
                    'field'      => $cond[0],
                    'id'         => $found->id,
                    'name'       => $found->name,
                    'deleted_at' => $found->deleted_at,
                ];
            }
        }

        // --- parent: NAMA store (exact, case-insensitive) ---
        foreach ($this->namaCalon($p) as $nama) {
            $found = Customer::withTrashed()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($nama))])
                ->first(['id', 'name', 'phone', 'ktp', 'deleted_at']);
            if ($found) {
                $hits[] = [
                    'table'      => 'master_customers',
                    'field'      => 'name',
                    'id'         => $found->id,
                    'name'       => $found->name,
                    'deleted_at' => $found->deleted_at,
                ];
                break;
            }
        }

        // --- child: phone / KTP ---
        $childOr = [];
        if (!empty($p->phone)) $childOr[] = ['phone', '=', $p->phone];
        if (!empty($p->ktp))   $childOr[] = ['ktp',   '=', $p->ktp];

        foreach ($childOr as $cond) {
            $found = CustomerOtherAddress::where('status', CustomerOtherAddress::STATUS['ACTIVE'])
                ->where($cond[0], $cond[2])
                ->first(['id', 'name', 'phone', 'ktp', 'customer_id']);
            if ($found) {
                $hits[] = [
                    'table'       => 'master_customer_other_addresses',
                    'field'       => $cond[0],
                    'id'          => $found->id,
                    'name'        => $found->name,
                    'customer_id' => $found->customer_id,
                ];
            }
        }

        // --- child: NAMA member (exact, case-insensitive, semua store) ---
        foreach ($this->namaCalon($p) as $nama) {
            $found = CustomerOtherAddress::where('status', CustomerOtherAddress::STATUS['ACTIVE'])
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($nama))])
                ->first(['id', 'name', 'phone', 'ktp', 'customer_id']);
            if ($found) {
                $hits[] = [
                    'table'       => 'master_customer_other_addresses',
                    'field'       => 'name',
                    'id'          => $found->id,
                    'name'        => $found->name,
                    'customer_id' => $found->customer_id,
                ];
                break;
            }
        }

        return $hits;
    }

    /**
     * Nama-nama calon dari pengajuan untuk pencocokan (unik, non-kosong).
     */
    private function namaCalon(PengajuanProforma $p): array
    {
        $out = [];
        foreach ([$p->perusahaan, $p->prospect_name] as $n) {
            $n = trim((string) $n);
            if ($n !== '' && !in_array(mb_strtolower($n), array_map('mb_strtolower', $out), true)) {
                $out[] = $n;
            }
        }
        return $out;
    }

    /**
     * Paket B poin 11: kandidat store + daftar member turunannya untuk
     * pilihan gandeng eksplisit. Parent dicari by KTP (seperti resolveParent),
     * fallback by nama exact. Member = child ACTIVE milik parent tsb.
     *
     * @return array ['parent' => Customer|null, 'members' => Collection]
     */
    public function candidateStore(PengajuanProforma $p): array
    {
        $parent = null;
        if (!empty($p->ktp)) {
            $parent = Customer::withTrashed()->where('ktp', $p->ktp)->first();
        }
        if (!$parent) {
            foreach ($this->namaCalon($p) as $nama) {
                $parent = Customer::withTrashed()
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($nama))])
                    ->first();
                if ($parent) {
                    break;
                }
            }
        }

        $members = collect();
        if ($parent) {
            $members = CustomerOtherAddress::where('customer_id', $parent->id)
                ->where('status', CustomerOtherAddress::STATUS['ACTIVE'])
                ->orderBy('id')
                ->get(['id', 'name', 'phone', 'kota', 'member_default']);
        }

        return ['parent' => $parent, 'members' => $members];
    }

    // ------------------------------------------------------------------
    // PUBLIC: MUTASI UTAMA
    // ------------------------------------------------------------------

    /**
     * Jalankan mutasi: buat/temukan parent & child, update pengajuan.
     *
     * Idempoten: jika customer_id_hasil sudah terisi, skip insert dan
     * kembalikan data yang sudah ada.
     *
     * @param  PengajuanProforma $p
     * @param  string            $verifiedBy  Username admin yang memverifikasi
     * @param  string|null       $catatan
     * @param  bool              $paksa       true = abaikan warning duplikat
     * @param  string            $memberAction 'auto' | 'baru' | 'gandeng:{memberId}'
     *                                        (Paket B poin 11: pilihan eksplisit di halaman verifikasi)
     * @return array             ['customer' => Customer, 'member' => CustomerOtherAddress,
     *                            'parent_baru' => bool, 'member_baru' => bool]
     * @throws \RuntimeException  jika validasi gagal atau duplikat tanpa paksa
     */
    public function mutasi(PengajuanProforma $p, string $verifiedBy, ?string $catatan = null, bool $paksa = false, string $memberAction = 'auto'): array
    {
        // 1. Validasi field
        $errors = $this->validateFieldKelengkapan($p);
        if (!empty($errors)) {
            throw new \RuntimeException(implode(' | ', $errors));
        }

        // 2. Cek status
        if ($p->status === PengajuanProforma::STATUS_DISETUJUI) {
            // Sudah dimutasi — kembalikan data existing
            $customer = Customer::find($p->customer_id_hasil);
            $member   = $p->member_id_hasil ? CustomerOtherAddress::find($p->member_id_hasil) : null;
            return [
                'customer'    => $customer,
                'member'      => $member,
                'parent_baru' => (bool) $p->parent_dibuat_baru,
                'member_baru' => (bool) $p->member_dibuat_baru,
            ];
        }

        // 3. Cek duplikat (kecuali paksa)
        if (!$paksa) {
            $duplikat = $this->cekDuplikat($p);
            if (!empty($duplikat)) {
                $info = collect($duplikat)->map(function ($d) {
                    return "[{$d['table']}] {$d['field']}={$d['id']}({$d['name']})";
                })->implode(', ');
                throw new \RuntimeException('Duplikat ditemukan: ' . $info . '. Gunakan paksa=true untuk tetap lanjutkan.');
            }
        }

        return DB::transaction(function () use ($p, $verifiedBy, $catatan, $memberAction) {
            [$customer, $parentBaru] = $this->resolveParent($p);
            [$member, $memberBaru]   = $this->resolveMember($p, $customer, $memberAction);

            $p->update([
                'status'           => PengajuanProforma::STATUS_DISETUJUI,
                'verified_by'      => $verifiedBy,
                'verified_at'      => now(),
                'catatan'          => $catatan,
                'customer_id_hasil' => $customer->id,
                'member_id_hasil'   => $member->id,
                'parent_dibuat_baru' => $parentBaru,
                'member_dibuat_baru' => $memberBaru,
            ]);

            Log::info('PengajuanMutasi: mutasi selesai', [
                'estimate_number' => $p->estimate_number,
                'customer_id'     => $customer->id,
                'member_id'       => $member->id,
                'parent_baru'     => $parentBaru,
                'member_baru'     => $memberBaru,
                'member_action'   => $memberAction,
                'verified_by'     => $verifiedBy,
            ]);

            return [
                'customer'    => $customer,
                'member'      => $member,
                'parent_baru' => $parentBaru,
                'member_baru' => $memberBaru,
            ];
        });
    }

    // ------------------------------------------------------------------
    // PUBLIC: NOTIFIKASI KE AO (taskManagement)
    // ------------------------------------------------------------------

    /**
     * Kirim callback ke modul AO bahwa mutasi berhasil.
     * AO akan dapat notifikasi inbox → tombol Lanjutkan muncul.
     *
     * Catatan kompatibilitas: Laravel 6 / PHP 7.3 tidak punya
     * Http facade — dipakai Guzzle langsung (sudah ada di vendor).
     * Payload membawa detail item ala order-estimate agar AO bisa
     * membentuk SO awal cash yang masuk proforma.
     */
    public function notifAo(PengajuanProforma $p): bool
    {
        $aoCallbackUrl = config('services.ao_module.notif_inbound_url');
        $aoApiKey      = config('services.ao_module.api_key');

        if (empty($aoCallbackUrl)) {
            Log::warning('PengajuanMutasi: ao_module.notif_inbound_url belum dikonfigurasi, skip notif.');
            return false;
        }

        $items = json_decode((string) $p->items_json, true);
        if (!is_array($items)) {
            $items = [];
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'connect_timeout' => 5,
                'verify' => false,
                'http_errors' => false,
            ]);

            $response = $client->post($aoCallbackUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $aoApiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'ao_pic'          => $p->ao_pic,
                    'estimate_number' => $p->estimate_number,
                    'title'           => 'Mutasi berhasil — lampirkan bukti lalu submit',
                    'body'            => 'Estimate ' . $p->estimate_number . ' sudah dimutasi. Capture 1 foto bukti chat lalu klik Submit Lanjutan.',
                    'termin'          => $p->termin,
                    'grand_total'     => (float) $p->grand_total,
                    'items'           => array_values($items),
                    'customer_id_hasil' => $p->customer_id_hasil,
                    'member_id_hasil'   => $p->member_id_hasil,
                ],
            ]);

            $code = $response->getStatusCode();
            $ok = $code >= 200 && $code < 300;

            $p->update(['notif_ao_status' => $ok ? 'terkirim' : 'gagal']);

            Log::info('PengajuanMutasi: notif AO', [
                'estimate_number' => $p->estimate_number,
                'status'          => $code,
                'ok'              => $ok,
                'sent_customer'   => $p->customer_id_hasil,
                'sent_member'     => $p->member_id_hasil,
            ]);

            return $ok;
        } catch (\Exception $e) {
            $p->update(['notif_ao_status' => 'gagal']);
            Log::warning('PengajuanMutasi: notif AO exception', [
                'estimate_number' => $p->estimate_number,
                'error'           => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ------------------------------------------------------------------
    // PUBLIC: DETEKSI PROFORMA DARI MUTASI PROSPEK
    // ------------------------------------------------------------------

    /**
     * Cari pengajuan yang melahirkan proforma ini.
     * Kunci: member hasil mutasi == customer_other_address_id proforma,
     * fallback via SO (customer_id SO == customer hasil).
     *
     * @param mixed $proforma SalesOrderProforma (properti so_id, customer_other_address_id)
     * @return PengajuanProforma|null
     */
    public function findPengajuanForProforma($proforma)
    {
        if (empty($proforma)) {
            return null;
        }

        $memberId = $proforma->customer_other_address_id ?? null;
        if (!empty($memberId)) {
            $found = PengajuanProforma::where('member_id_hasil', (string) $memberId)
                ->orderBy('id', 'desc')
                ->first();
            if ($found) {
                return $found;
            }
        }

        if (!empty($proforma->so_id)) {
            $soCustomer = DB::table('penjualan_so')->where('id', $proforma->so_id)->value('customer_id');
            if (!empty($soCustomer)) {
                $found = PengajuanProforma::where('customer_id_hasil', $soCustomer)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Notifikasi PEMBATALAN proforma ke modul AO.
     * Dipanggil setelah cancel() sukses. Non-blocking (gagal = status gagal).
     */
    public function notifAoBatal(PengajuanProforma $p, $proformaCode = null): bool
    {
        $aoCallbackUrl = config('services.ao_module.notif_inbound_url');
        $aoApiKey      = config('services.ao_module.api_key');

        if (empty($aoCallbackUrl)) {
            Log::warning('PengajuanMutasi: ao_module.notif_inbound_url belum dikonfigurasi, skip notif batal.');
            return false;
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'connect_timeout' => 5,
                'verify' => false,
                'http_errors' => false,
            ]);

            $response = $client->post($aoCallbackUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $aoApiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'ao_pic'          => $p->ao_pic,
                    'estimate_number' => $p->estimate_number,
                    'type'            => 'proforma_batal',
                    'title'           => 'Proforma dibatalkan' . ($proformaCode ? ' (' . $proformaCode . ')' : ''),
                    'body'            => 'Proforma ' . ($proformaCode ?: $p->estimate_number) . ' dibatalkan oleh admin sales. Customer kembali ke prospek.',
                    'customer_id_hasil' => $p->customer_id_hasil,
                    'member_id_hasil'   => $p->member_id_hasil,
                ],
            ]);

            $code = $response->getStatusCode();
            $ok = $code >= 200 && $code < 300;

            Log::info('PengajuanMutasi: notif batal AO', [
                'estimate_number' => $p->estimate_number,
                'status'          => $code,
                'ok'              => $ok,
            ]);

            return $ok;
        } catch (\Exception $e) {
            Log::warning('PengajuanMutasi: notif batal AO exception', [
                'estimate_number' => $p->estimate_number,
                'error'           => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ------------------------------------------------------------------
    // PRIVATE HELPERS
    // ------------------------------------------------------------------

    /**
     * Temukan atau buat parent (master_customers).
     * Strategi: cari by KTP → jika ada (termasuk trashed) restore + update.
     * Jika tidak ada → buat baru.
     */
    private function resolveParent(PengajuanProforma $p): array
    {
        $existing = Customer::withTrashed()->where('ktp', $p->ktp)->first();

        $nama = !empty($p->perusahaan) ? $p->perusahaan : $p->prospect_name;

        $data = [
            'name'          => $nama,
            'owner_name'    => $p->owner ?? $p->prospect_name,
            'phone'         => $p->phone,
            'ktp'           => $p->ktp,
            'npwp'          => $p->npwp,
            'address'       => $p->address,
            'text_kota'     => $p->city,
            'kota'          => $p->city,
            'text_provinsi' => $p->provinsi,
            'provinsi'      => $p->provinsi,
            'kecamatan'     => $p->kecamatan,
            'text_kecamatan'=> $p->kecamatan,
            'kelurahan'     => $p->kelurahan,
            'text_kelurahan'=> $p->kelurahan,
            'status'        => Customer::STATUS['ACTIVE'],
            'existence'     => Customer::EXISTENCE['ENABLE'],
            'has_tempo'     => Customer::HAS_TEMPO['NO'],
            'saldo'         => 0,
        ];

        // Zone + kategori dari pengajuan ikut masuk ke customer existing.
        // Kategori disimpan sebagai nama di pengajuan -> resolve ke id master.
        if (trim((string) $p->zone) !== '') {
            $data['zone'] = trim((string) $p->zone);
        }
        $categoryId = $this->resolveCategoryId($p->kategori);
        if ($categoryId !== null) {
            $data['category_id'] = $categoryId;
        }

        if ($existing) {
            $existing->restore(); // no-op jika belum trashed
            $existing->update($data);
            return [$existing->fresh(), false];
        }

        // Generate code customer baru
        $data['code'] = $this->generateCustomerCode();
        $customer = Customer::create($data);
        return [$customer, true];
    }

    /**
     * Buat atau update child (master_customer_other_addresses).
     * 1 member default per store yang baru dibuat.
     *
     * Paket B poin 11 — $memberAction:
     *  - 'auto'            : perilaku lama (cocok KTP dalam store → update, else buat baru)
     *  - 'baru'            : paksa buat member baru di store ini (id {parent}.{n+1})
     *  - 'gandeng:{id}'    : pakai member existing tsb (wajib milik store ini) + update datanya
     */
    private function resolveMember(PengajuanProforma $p, Customer $customer, string $memberAction = 'auto'): array
    {
        $existing = null;
        // Gandeng eksplisit: validasi milik store ini
        if (strpos($memberAction, 'gandeng:') === 0) {
            $mid = substr($memberAction, strlen('gandeng:'));
            $existing = CustomerOtherAddress::where('id', $mid)
                ->where('customer_id', $customer->id)
                ->first();
            if (!$existing) {
                throw new \RuntimeException('Member gandengan tidak ditemukan di store ini.');
            }
        } elseif ($memberAction !== 'baru' && !empty($p->ktp)) {
            // 'auto': cocok KTP dalam store → update. 'baru': lewati, langsung buat.
            $existing = CustomerOtherAddress::where('customer_id', $customer->id)
                ->where('ktp', $p->ktp)
                ->first();
        }

        $nama = !empty($p->perusahaan) ? $p->perusahaan : $p->prospect_name;

        $data = [
            'customer_id'            => $customer->id,
            'name'                   => $nama,
            'contact_person'         => $p->owner ?? $p->prospect_name,
            'phone'                  => $p->phone,
            'ktp'                    => $p->ktp,
            'npwp'                   => $p->npwp,
            'address'                => $p->address,
            'text_kota'              => $p->city,
            'kota'                   => $p->city,
            'text_provinsi'          => $p->provinsi,
            'provinsi'               => $p->provinsi,
            'kecamatan'              => $p->kecamatan,
            'text_kecamatan'         => $p->kecamatan,
            'kelurahan'              => $p->kelurahan,
            'text_kelurahan'         => $p->kelurahan,
            'member_default'         => CustomerOtherAddress::MEMBER_DEFAULT['YES'],
            'account_representative' => $p->ao_pic,
            'zone'                   => trim((string) $p->zone) !== '' ? trim((string) $p->zone) : null,
            'status'                 => CustomerOtherAddress::STATUS['ACTIVE'],
            'situation'              => CustomerOtherAddress::SITUATION['ACTIVE'],
            'status_key'             => CustomerOtherAddress::STATUS_KEY['ENABLE'],
            'free_shipping'          => 0, // NON FREE
        ];

        if ($existing) {
            $existing->update($data);
            return [$existing->fresh(), false];
        }

        // ID member pola parent.counting : {customer_id}.{n}, n mulai dari 1
        // (kolom id varchar tanpa auto-increment; model $incrementing=false).
        $last = CustomerOtherAddress::where('customer_id', $customer->id)
            ->where('id', 'like', $customer->id . '.%')
            ->orderBy('id', 'desc')
            ->value('id');
        $next = 1;
        if ($last && preg_match('/\.(\d+)$/', (string) $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        $member = new CustomerOtherAddress($data);
        $member->id = $customer->id . '.' . $next;
        $member->save();

        return [$member->fresh(), true];
    }

    /**
     * Resolve nama kategori (dari pengajuan AO) ke id master_customer_categories.
     * Case-insensitive; null bila kosong/tidak cocok agar tidak memblokir mutasi.
     */
    private function resolveCategoryId($kategori): ?int
    {
        $name = trim((string) $kategori);
        if ($name === '') {
            return null;
        }
        try {
            $found = \App\Entities\Master\CustomerCategory::whereRaw('LOWER(name) = ?', [strtolower($name)])->first(['id']);
            if ($found) {
                return (int) $found->id;
            }
            Log::warning('PengajuanMutasi: kategori tidak cocok master, dilewati', ['kategori' => $name]);
        } catch (\Exception $e) {
            Log::warning('PengajuanMutasi: resolve kategori gagal', ['kategori' => $name, 'error' => $e->getMessage()]);
        }
        return null;
    }

    private function generateCustomerCode(): string
    {
        // Format: CUS-YYMMDD-NNNN
        $prefix = 'CUS-' . now()->format('ymd') . '-';
        $last = Customer::where('code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('code');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

}
