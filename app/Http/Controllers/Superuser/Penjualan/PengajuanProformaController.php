<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Penjualan\PengajuanProforma;
use App\Entities\Penjualan\ProformaCancelLog;
use App\Entities\Penjualan\SalesOrder;
use App\Services\Penjualan\PengajuanMutasiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PengajuanProformaController extends Controller
{
    /** @var PengajuanMutasiService */
    private $mutasiSvc;

    public function __construct(PengajuanMutasiService $mutasiSvc)
    {
        $this->mutasiSvc = $mutasiSvc;
    }

    // ------------------------------------------------------------------
    // INDEX — daftar pengajuan
    // ------------------------------------------------------------------

    public function index(Request $request)
    {
        $query = PengajuanProforma::orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', PengajuanProforma::STATUS_MENUNGGU);
        }

        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($qb) use ($q) {
                $qb->where('prospect_name', 'like', $q)
                   ->orWhere('phone', 'like', $q)
                   ->orWhere('estimate_number', 'like', $q);
            });
        }

        $items = $query->paginate(20)->appends(request()->query());

        // Badge count untuk tab menunggu
        $counts = [
            'menunggu' => PengajuanProforma::where('status', PengajuanProforma::STATUS_MENUNGGU)->count(),
            'revisi' => PengajuanProforma::where('status', PengajuanProforma::STATUS_REVISI)->count(),
        ];

        return view('superuser.penjualan.pengajuan_proforma.index', compact('items', 'counts'));
    }

    // ------------------------------------------------------------------
    // SHOW — detail + info duplikat
    // ------------------------------------------------------------------

    public function show($id)
    {
        $pengajuan = PengajuanProforma::findOrFail($id);

        $duplikat   = [];
        $fieldErrors = $this->mutasiSvc->validateFieldKelengkapan($pengajuan);
        // Paket B poin 11: kandidat store + member turunannya untuk pilihan gandeng
        $candidateStore = ['parent' => null, 'members' => collect()];
        // Retry SO+proforma: hanya relevan pasca-mutasi (disetujui + ada member hasil)
        $retryInfo = ['so' => null, 'proforma' => null, 'canRetry' => false, 'reason' => null, 'brand' => null, 'itemCount' => 0, 'freeCount' => 0];

        if ($pengajuan->status === PengajuanProforma::STATUS_MENUNGGU) {
            $duplikat = $this->mutasiSvc->cekDuplikat($pengajuan);
            $candidateStore = $this->mutasiSvc->candidateStore($pengajuan);
        }

        if ($pengajuan->status === PengajuanProforma::STATUS_DISETUJUI && !empty($pengajuan->member_id_hasil)) {
            try {
                $retryInfo = app(\App\Services\Penjualan\PengajuanRetryService::class)->statusFor($pengajuan);
            } catch (\Exception $e) {
                $retryInfo['reason'] = 'Cek retry gagal: ' . $e->getMessage();
            }
        }

        return view('superuser.penjualan.pengajuan_proforma.show', compact('pengajuan', 'duplikat', 'fieldErrors', 'candidateStore', 'retryInfo'));
    }

    // ------------------------------------------------------------------
    // DOKUMEN KTP/NPWP — sajikan file private (disk local) via auth
    // File disimpan di storage/app/pengajuan_proforma/{id}/xxx,
    // jadi tidak bisa diakses via asset('storage/...'). Route ini
    // yang dipakai tombol Lihat di show.blade.php.
    // GET /penjualan/pengajuan-proforma/{id}/dokumen/{jenis}
    // ------------------------------------------------------------------
    public function dokumen($id, $jenis)
    {
        $pengajuan = PengajuanProforma::findOrFail($id);

        if (!in_array($jenis, ['ktp', 'npwp'], true)) {
            abort(404);
        }

        $col = $jenis === 'ktp' ? 'ktp_photo_path' : 'npwp_photo_path';
        $path = $pengajuan->{$col};

        if (empty($path) || !Storage::disk('local')->exists($path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        return response()->file(storage_path('app/' . $path));
    }

    /**
     * GET bukti capture ke-{index} (0-based) dari bukti_list.
     */
    public function bukti($id, $index)
    {
        $pengajuan = PengajuanProforma::findOrFail($id);
        $list = json_decode((string) $pengajuan->bukti_list, true) ?: [];
        $path = $list[(int) $index] ?? null;

        if (empty($path) || !Storage::disk('local')->exists($path)) {
            abort(404, 'Bukti tidak ditemukan.');
        }

        return response()->file(storage_path('app/' . $path));
    }

    // ------------------------------------------------------------------
    // VERIFIKASI + MUTASI
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/verifikasi
     */
    public function verifikasi(Request $request, $id)
    {
        $pengajuan = PengajuanProforma::findOrFail($id);

        if ($pengajuan->status !== PengajuanProforma::STATUS_MENUNGGU) {
            return back()->with('error', 'Pengajuan ini sudah ' . $pengajuan->status . ', tidak bisa diverifikasi ulang.');
        }

        $validator = Validator::make($request->all(), [
            'catatan' => 'nullable|string|max:1000',
            'paksa'   => 'nullable|boolean',
            // Paket B poin 11: auto | baru | gandeng:{memberId}
            'member_action' => ['nullable', 'string', 'max:100', 'regex:/^(auto|baru|gandeng:[A-Za-z0-9.\-_]+)$/'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $memberAction = (string) $request->input('member_action', 'auto');
            $result = $this->mutasiSvc->mutasi(
                $pengajuan,
                $this->adminName(),
                $request->input('catatan'),
                (bool) $request->input('paksa', false),
                $memberAction
            );

            // Kirim notif ke AO (non-blocking — gagal tidak rollback mutasi)
            $this->mutasiSvc->notifAo($pengajuan->fresh());

            $msg  = 'Mutasi berhasil. ';
            $msg .= $result['parent_baru'] ? 'Customer baru dibuat. ' : 'Customer existing dipakai. ';
            if (strpos($memberAction, 'gandeng:') === 0) {
                $msg .= 'Member digandeng (' . $result['member']['id'] . ').';
            } else {
                $msg .= $result['member_baru'] ? 'Member baru dibuat.' : 'Member existing dipakai.';
            }

            return redirect()
                ->route('superuser.penjualan.pengajuan_proforma.show', $id)
                ->with('success', $msg);

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Exception $e) {
            Log::error('Verifikasi pengajuan gagal', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem, coba lagi.')->withInput();
        }
    }

    // ------------------------------------------------------------------
    // ISI DOKUMEN (KTP/NPWP + foto) — sebelum mutasi ke baris pengajuan,
    // sesudah mutasi langsung ke data CUSTOMER (+ sinkron baris pengajuan).
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/dokumen-simpan
     */
    public function updateDokumen(Request $request, $id)
    {
        if (!\App\Helper\ProformaAccess::canRevisiBatal(Auth::user())) {
            $msg = 'Isi dokumen hanya untuk admin sales dan management.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $pengajuan = PengajuanProforma::findOrFail($id);

        // Dokumen hanya bisa diubah saat menunggu/revisi/disetujui; ditolak/dibatalkan read-only
        if (!in_array($pengajuan->status, [PengajuanProforma::STATUS_MENUNGGU, PengajuanProforma::STATUS_REVISI, PengajuanProforma::STATUS_DISETUJUI], true)) {
            $msg = 'Dokumen terkunci (status ' . $pengajuan->status . ').';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $validator = Validator::make($request->all(), [
            'ktp' => 'nullable|digits:16',
            'npwp' => 'nullable|digits:15',
            'phone' => 'nullable|string|max:30',
            'ktp_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'npwp_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            $msg = $validator->errors()->first();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::transaction(function () use ($request, $pengajuan) {
                $data = [];
                foreach (['ktp', 'npwp', 'phone'] as $f) {
                    if ($request->filled($f)) {
                        $data[$f] = trim((string) $request->input($f));
                    }
                }
                foreach (['ktp_photo' => 'ktp_photo_path', 'npwp_photo' => 'npwp_photo_path'] as $file => $col) {
                    if ($request->hasFile($file)) {
                        $old = $pengajuan->{$col};
                        $data[$col] = $request->file($file)->store('pengajuan_proforma/' . $pengajuan->id, 'local');
                        if ($col === 'ktp_photo_path') {
                            $data['foto_ktp_ada'] = true;
                        } else {
                            $data['foto_npwp_ada'] = true;
                        }
                        if (!empty($old) && Storage::disk('local')->exists($old)) {
                            Storage::disk('local')->delete($old);
                        }
                    }
                }

                $sudahMutasi = !empty($pengajuan->customer_id_hasil) || !empty($pengajuan->member_id_hasil);
                if ($sudahMutasi) {
                    // Sesudah mutasi: tulis langsung ke master customer + member
                    if (!empty($pengajuan->customer_id_hasil) && isset($data['ktp'])) {
                        Customer::where('id', $pengajuan->customer_id_hasil)->update(['ktp' => $data['ktp']]);
                    }
                    if (!empty($pengajuan->customer_id_hasil) && isset($data['npwp'])) {
                        Customer::where('id', $pengajuan->customer_id_hasil)->update(['npwp' => $data['npwp']]);
                    }
                    if (!empty($pengajuan->member_id_hasil)) {
                        $m = [];
                        if (isset($data['ktp'])) {
                            $m['ktp'] = $data['ktp'];
                        }
                        if (isset($data['npwp'])) {
                            $m['npwp'] = $data['npwp'];
                        }
                        if (isset($data['phone'])) {
                            $m['phone'] = $data['phone'];
                        }
                        if (!empty($m)) {
                            CustomerOtherAddress::where('id', $pengajuan->member_id_hasil)->update($m);
                        }
                    }
                }

                if (!empty($data)) {
                    $pengajuan->update($data);
                }
            });

            $msg = 'Dokumen tersimpan.'
                . (!empty($pengajuan->member_id_hasil) ? ' Data customer ikut diperbarui.' : '');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Update dokumen pengajuan gagal', ['id' => $id, 'error' => $e->getMessage()]);
            $msg = 'Gagal menyimpan dokumen.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return back()->with('error', $msg);
        }
    }

    // ------------------------------------------------------------------
    // KIRIM ULANG NOTIFIKASI AO (bila member_hasil tidak masuk / gagal)
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/notif-ulang
     * Idempoten: hanya kirim ulang, tidak mengubah mutasi.
     */
    public function notifUlang(Request $request, $id)
    {
        $pengajuan = PengajuanProforma::findOrFail($id);

        if (empty($pengajuan->member_id_hasil)) {
            return back()->with('error', 'Belum ada hasil mutasi — mutasikan dulu sebelum kirim notif.');
        }

        try {
            $ok = $this->mutasiSvc->notifAo($pengajuan->fresh());

            if ($ok) {
                return back()->with('success', 'Notifikasi terkirim ke AO.');
            }

            return back()->with('error', 'Notifikasi gagal terkirim — cek URL/key/capaian server AO di log.');
        } catch (\Exception $e) {
            Log::error('Kirim ulang notif gagal', ['id' => $id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Terjadi kesalahan sistem, coba lagi.');
        }
    }

    // ------------------------------------------------------------------
    // RETRY BUAT SO + PROFORMA (pasca-mutasi, sumber lokal items_json)
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/retry-so
     * Membuat SO Awal + proforma Terbuat dari items_json pengajuan bila
     * push AO gagal (422 duplikat / 500). Idempoten: tidak menggandakan
     * proforma yang sudah ada; SO Tutup (status 4) ditolak (butuh diskusi).
     */
    public function retrySo(Request $request, $id)
    {
        if (!\App\Helper\ProformaAccess::canRevisiBatal(Auth::user())) {
            $msg = 'Retry SO hanya untuk admin sales dan management.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $pengajuan = PengajuanProforma::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kurs' => 'required|numeric|min:1000',
        ], [
            'kurs.required' => 'Kurs wajib diisi.',
            'kurs.min' => 'Kurs tidak valid. Gunakan angka penuh, cth: 19000.',
        ]);

        if ($validator->fails()) {
            $msg = $validator->errors()->first();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $result = app(\App\Services\Penjualan\PengajuanRetryService::class)
                ->retry($pengajuan, (int) Auth::id(), (float) $request->input('kurs'));

            $msg = 'Retry berhasil. SO ' . $result['so']->so_code
                . ($result['so_baru'] ? ' (baru)' : ' (existing dipakai)')
                . ($result['proforma']
                    ? ' + proforma ' . $result['proforma']->code . ' (Aktif — lanjut kalkulasi admin).'
                    : ' (SO Lanjutan, tanpa proforma).')
                . ' Jangan Sync lagi dari AO untuk estimate ini agar tidak ganda.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return redirect()
                ->route('superuser.penjualan.pengajuan_proforma.show', $id)
                ->with('success', $msg);
        } catch (\RuntimeException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Exception $e) {
            Log::error('Retry SO pengajuan gagal', ['id' => $id, 'error' => $e->getMessage()]);
            $msg = 'Terjadi kesalahan sistem, coba lagi.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return back()->with('error', $msg)->withInput();
        }
    }

    // ------------------------------------------------------------------
    // KEMBALIKAN / REVISI pre-mutasi : baris DIPERTAHANKAN (status revisi),
    // AO perbaiki data + ajukan ulang (kirim data lagi full).
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/kembalikan
     */
    public function kembalikan(Request $request, $id)
    {
        $pengajuan = PengajuanProforma::findOrFail($id);

        if ($pengajuan->status !== PengajuanProforma::STATUS_MENUNGGU) {
            return back()->with('error', 'Hanya pengajuan menunggu yang bisa dikembalikan revisi.');
        }

        $validator = Validator::make($request->all(), [
            'catatan' => 'required|string|max:1000',
        ], ['catatan.required' => 'Catatan revisi wajib diisi.']);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pengajuan->update([
            'status'      => PengajuanProforma::STATUS_REVISI,
            'catatan'     => $request->catatan,
            'verified_by' => $this->adminName(),
            'verified_at' => now(),
        ]);

        $notifOk = $this->notifyAoRevisi(
            $pengajuan->estimate_number,
            'Revisi admin sales: ' . trim((string) $request->catatan)
        );

        return redirect()
            ->route('superuser.penjualan.pengajuan_proforma.index')
            ->with('success', 'Pengajuan dikembalikan untuk revisi.'
                . ($notifOk
                    ? ' AO sudah diberi tahu — estimate kembali draft, ajukan ulang bila sudah diperbaiki.'
                    : ' (Notifikasi otomatis ke AO gagal — minta AO perbaiki manual / hubungi IT.)'));
    }

    // ------------------------------------------------------------------
    // KEMBALIKAN KE AO pasca-mutasi : ubah data di AO, proforma diupdate
    // (TANPA pengajuan/mutasi ulang). Hanya admin sales / management.
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/kembalikan-ao
     */
    public function kembalikanAo(Request $request, $id)
    {
        if (!\App\Helper\ProformaAccess::canRevisiBatal(Auth::user())) {
            $msg = 'Kembalikan ke AO hanya untuk admin sales dan management.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $pengajuan = PengajuanProforma::findOrFail($id);

        if ($pengajuan->status !== PengajuanProforma::STATUS_DISETUJUI) {
            return back()->with('error', 'Hanya pengajuan disetujui (sudah mutasi) yang bisa dikembalikan ke AO.');
        }

        if (empty($pengajuan->member_id_hasil)) {
            return back()->with('error', 'Pengajuan ini belum punya member hasil mutasi.');
        }

        $validator = Validator::make($request->all(), [
            'catatan' => 'required|string|max:1000',
        ], ['catatan.required' => 'Catatan revisi untuk AO wajib diisi.']);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pengajuan->update([
            'revisi_ao' => true,
            'catatan' => ($pengajuan->catatan ? $pengajuan->catatan . ' | ' : '') . 'REVISI-AO: ' . $request->catatan,
        ]);

        // Sembunyikan proforma dari semua tab selama revisi (status 0);
        // kembali ke Aktif (1) saat update revisi dari AO tiba.
        if (!empty($pengajuan->member_id_hasil)) {
            $pf = \App\Entities\Penjualan\SalesOrderProforma::where(
                'customer_other_address_id', $pengajuan->member_id_hasil
            )->orderBy('id', 'desc')->first();
            if ($pf && !empty($pf->so_id)) {
                SalesOrder::where('id', $pf->so_id)->update(['status_proforma' => 0]);
            }
        }

        $notifOk = $this->notifyAoRevisi(
            $pengajuan->estimate_number,
            'Revisi data (tanpa pengajuan ulang): ' . trim((string) $request->catatan),
            'post_mutasi'
        );

        $msg = 'Dikembalikan ke AO untuk revisi data.'
            . ($notifOk
                ? ' AO ubah via Edit; proforma diperbarui otomatis tanpa pengajuan/mutasi ulang.'
                : ' (Notifikasi otomatis ke AO gagal — minta AO buka dari inbox / hubungi IT.)');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()
            ->route('superuser.penjualan.pengajuan_proforma.show', $id)
            ->with('success', $msg);
    }

    // ------------------------------------------------------------------
    // TOLAK = hapus / cabut semua (baris + file dihapus, AO kembali draft).
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/tolak
     */
    public function tolak(Request $request, $id)
    {
        $pengajuan = PengajuanProforma::findOrFail($id);

        if ($pengajuan->status !== PengajuanProforma::STATUS_MENUNGGU) {
            return back()->with('error', 'Hanya pengajuan menunggu yang bisa ditolak.');
        }

        $validator = Validator::make($request->all(), [
            'catatan' => 'required|string|max:1000',
        ], ['catatan.required' => 'Alasan penolakan wajib diisi.']);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $alasan = 'Ditolak admin sales: ' . trim((string) $request->catatan);
        $notifOk = $this->cabutPengajuan($pengajuan, $alasan);

        return redirect()
            ->route('superuser.penjualan.pengajuan_proforma.index')
            ->with('success', 'Pengajuan ditolak dan dicabut total.'
                . ($notifOk
                    ? ' AO sudah diberi tahu — estimate kembali draft.'
                    : ' (Notifikasi otomatis ke AO gagal — minta AO perbaiki manual / hubungi IT.)'));
    }

    /**
     * Cabut total pengajuan pre-mutasi: hapus file + baris + kabari AO.
     * Dipakai tolak() dan hapus(). Mengembalikan hasil notif (bool).
     */
    private function cabutPengajuan(PengajuanProforma $pengajuan, string $alasan): bool
    {
        $estimateNumber = $pengajuan->estimate_number;

        foreach (['ktp_photo_path', 'npwp_photo_path'] as $col) {
            $p = $pengajuan->{$col};
            if (!empty($p) && Storage::disk('local')->exists($p)) {
                Storage::disk('local')->delete($p);
            }
        }
        foreach (json_decode((string) $pengajuan->bukti_list, true) ?: [] as $bp) {
            if (is_string($bp) && Storage::disk('local')->exists($bp)) {
                Storage::disk('local')->delete($bp);
            }
        }

        $pengajuan->delete();

        Log::info('Pengajuan proforma dicabut total', [
            'estimate_number' => $estimateNumber,
            'by' => $this->adminName(),
            'alasan' => $alasan,
        ]);

        return $this->notifyAoRevisi($estimateNumber, $alasan);
    }

    // ------------------------------------------------------------------
    // HAPUS / MINTA REVISI (sebelum mutasi — input AO keliru)
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/hapus
     * Hapus pengajuan yang inputnya keliru agar AO bisa perbaiki + ajukan ulang.
     * Guard: hanya menunggu/ditolak/dibatalkan yang BELUM dimutasi
     * (customer_id_hasil + member_id_hasil masih kosong). Yang sudah
     * disetujui wajib lewat Batalkan Proforma (ada rollback customer).
     * Best-effort: kabari AO agar estimate-nya kembali draft + inbox.
     */
    public function hapus(Request $request, $id)
    {
        $pengajuan = PengajuanProforma::findOrFail($id);

        if (!in_array($pengajuan->status, [
            PengajuanProforma::STATUS_MENUNGGU,
            PengajuanProforma::STATUS_DITOLAK,
            PengajuanProforma::STATUS_DIBATALKAN,
        ], true)) {
            return back()->with('error', 'Pengajuan sudah ' . $pengajuan->status . ' — gunakan Batalkan Proforma, bukan Hapus.');
        }

        if (!empty($pengajuan->customer_id_hasil) || !empty($pengajuan->member_id_hasil)) {
            return back()->with('error', 'Pengajuan ini sudah dimutasi ke customer existing — gunakan Batalkan Proforma.');
        }

        $validator = Validator::make($request->all(), [
            'alasan' => 'required|string|max:1000',
        ], ['alasan.required' => 'Alasan hapus/revisi wajib diisi.']);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $estimateNumber = $pengajuan->estimate_number;
        $aoPic = $pengajuan->ao_pic;
        $alasan = trim((string) $request->alasan);

        $notifOk = $this->cabutPengajuan($pengajuan, $alasan);

        $msg = 'Pengajuan ' . $estimateNumber . ' dihapus.'
            . ($notifOk
                ? ' AO (' . $aoPic . ') sudah diberi tahu — estimate kembali draft, silakan minta AO perbaiki + ajukan ulang.'
                : ' (Notifikasi otomatis ke AO gagal — minta AO perbaiki manual / hubungi IT. Alasan: ' . $alasan . ')');

        return redirect()
            ->route('superuser.penjualan.pengajuan_proforma.index')
            ->with('success', $msg);
    }

    /**
     * Kabari modul AO agar estimate kembali draft (best-effort).
     * POST {AO}/api/ao/estimate-revisi (agenda token) — turunan dari
     * AO_MODULE_NOTIF_URL yang sudah ada (.../api/ao/notif).
     * $mode: pra_mutasi (draft + ajukan ulang) | post_mutasi (edit + update proforma).
     */
    private function notifyAoRevisi(string $estimateNumber, string $alasan, string $mode = 'pra_mutasi'): bool
    {
        $notifUrl = (string) config('services.ao_module.notif_inbound_url');
        $apiKey = (string) config('services.ao_module.api_key');

        if ($notifUrl === '') {
            Log::warning('notifyAoRevisi: ao_module.notif_inbound_url belum dikonfigurasi, skip.');
            return false;
        }

        $revisiUrl = str_replace('/api/ao/notif', '/api/ao/estimate-revisi', $notifUrl);
        if ($revisiUrl === $notifUrl) {
            $revisiUrl = rtrim($notifUrl, '/') . '-revisi';
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'connect_timeout' => 5,
                'verify' => false,
                'http_errors' => false,
            ]);

            $response = $client->post($revisiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'estimate_number' => $estimateNumber,
                    'alasan' => $alasan,
                    'mode' => $mode,
                ],
            ]);

            $code = $response->getStatusCode();
            $ok = $code >= 200 && $code < 300;

            Log::info('notifyAoRevisi', [
                'estimate_number' => $estimateNumber,
                'status' => $code,
                'ok' => $ok,
            ]);

            return $ok;
        } catch (\Exception $e) {
            Log::warning('notifyAoRevisi exception', [
                'estimate_number' => $estimateNumber,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ------------------------------------------------------------------
    // CANCEL PROFORMA (setelah mutasi — customer sudah di existing)
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        // Paket A poin 10: cabut pengajuan (batal) hanya admin sales / management
        if (!\App\Helper\ProformaAccess::canRevisiBatal(Auth::user())) {
            $msg = 'Batalkan pengajuan hanya untuk admin sales dan management.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $pengajuan = PengajuanProforma::findOrFail($id);

        if (!in_array($pengajuan->status, [PengajuanProforma::STATUS_DISETUJUI, PengajuanProforma::STATUS_DIBATALKAN], true)) {
            $msg = 'Hanya pengajuan disetujui/dibatalkan yang bisa diproses di sini.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $validator = Validator::make($request->all(), [
            'alasan'            => 'required|string|max:1000',
            'proforma_code'     => 'nullable|string|max:100',
            'rollback_customer' => 'nullable|boolean',
        ], ['alasan.required' => 'Alasan pembatalan wajib diisi.']);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $rollback = (bool) $request->input('rollback_customer', true);

        try {
            DB::transaction(function () use ($pengajuan, $request, $rollback) {
                // 1. Catat log cancel
                $log = ProformaCancelLog::create([
                    'pengajuan_proforma_id' => $pengajuan->id,
                    'estimate_number'       => $pengajuan->estimate_number,
                    'customer_name'         => $pengajuan->prospect_name,
                    'phone'                 => $pengajuan->phone,
                    'address'               => $pengajuan->address,
                    'city'                  => $pengajuan->city,
                    'provinsi'              => $pengajuan->provinsi,
                    'kecamatan'             => $pengajuan->kecamatan,
                    'kelurahan'             => $pengajuan->kelurahan,
                    'ktp'                   => $pengajuan->ktp,
                    'npwp'                  => $pengajuan->npwp,
                    'ao_pic'                => $pengajuan->ao_pic,
                    'customer_id'           => $pengajuan->customer_id_hasil,
                    'member_id'             => $pengajuan->member_id_hasil,
                    'grand_total'           => $pengajuan->grand_total,
                    'proforma_code'         => $request->input('proforma_code'),
                    'cancelled_by'          => $this->adminName(),
                    'cancelled_at'          => now(),
                    'alasan'                => $request->alasan,
                    'customer_rolled_back'  => false,
                ]);

                // 2. Hapus dokumen proforma + reset SO-nya (agar hilang dari tab),
                // lalu rollback customer dengan SO tersebut dikecualikan dari
                // pengecekan "masih ada transaksi aktif".
                $proformaHapus = $this->hapusProformaTerkait($pengajuan, $request->input('proforma_code'));

                // 3. Rollback customer
                $rolledBack = false;
                if ($rollback) {
                    $rolledBack = $this->rollbackCustomer($pengajuan, $proformaHapus['so_id']);
                }

                // 3. Update log dengan hasil rollback
                $log->update(['customer_rolled_back' => $rolledBack]);

                // 4. Update status pengajuan
                $pengajuan->update([
                    'status'  => PengajuanProforma::STATUS_DIBATALKAN,
                    'catatan' => ($pengajuan->catatan ? $pengajuan->catatan . ' | ' : '') . 'CANCEL: ' . $request->alasan,
                ]);

                Log::info('Proforma cancel', [
                    'estimate_number' => $pengajuan->estimate_number,
                    'customer_id'     => $pengajuan->customer_id_hasil,
                    'rolled_back'     => $rolledBack,
                    'by'              => $this->adminName(),
                ]);
            });

            // Kabari AO (non-blocking — gagal notif tidak membatalkan cancel)
            $notifOk = $this->mutasiSvc->notifAoBatal(
                $pengajuan->fresh(),
                $request->input('proforma_code'),
                trim((string) $request->input('alasan', ''))
            );

            $msg = 'Proforma dibatalkan. Log audit tersimpan.'
                . ($notifOk ? ' AO sudah diberi tahu.' : ' (Notif ke AO gagal — cek koneksi.)');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'notif_ao' => $notifOk]);
            }

            return redirect()
                ->route('superuser.penjualan.pengajuan_proforma.index')
                ->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Cancel proforma gagal', ['id' => $id, 'error' => $e->getMessage()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    // ------------------------------------------------------------------
    // PRIVATE
    // ------------------------------------------------------------------

    /**
     * Hapus dokumen proforma terkait pengajuan + reset SO-nya.
     * Mengembalikan ['dihapus' => bool, 'so_id' => id|null].
     * Proforma yang sudah lanjutan (nota) TIDAK ikut dihapus.
     */
    private function hapusProformaTerkait(PengajuanProforma $pengajuan, $proformaCode = null)
    {
        $proforma = null;

        if (!empty($proformaCode)) {
            $proforma = \App\Entities\Penjualan\SalesOrderProforma::where('code', $proformaCode)->first();
        }

        if (!$proforma && !empty($pengajuan->member_id_hasil)) {
            $proforma = \App\Entities\Penjualan\SalesOrderProforma::where(
                'customer_other_address_id', $pengajuan->member_id_hasil
            )->orderBy('id', 'desc')->first();
        }

        if (!$proforma) {
            return ['dihapus' => false, 'so_id' => null];
        }

        // Jangan sentuh proforma yang sudah jadi nota/lanjutan
        if ((int) $proforma->so_lanjutan === 1 || (int) $proforma->status === 4) {
            Log::warning('hapusProformaTerkait: sudah lanjutan, dilewati', [
                'proforma_id' => $proforma->id,
            ]);

            return ['dihapus' => false, 'so_id' => $proforma->so_id];
        }

        if (method_exists($proforma, 'items') || isset($proforma->items)) {
            try {
                $proforma->items()->delete();
            } catch (\Exception $e) {
            }
        }

        if (method_exists($proforma, 'details_cost')) {
            try {
                $proforma->details_cost()->delete();
            } catch (\Exception $e) {
            }
        }

        $soId = $proforma->so_id;
        $proforma->delete();

        if (!empty($soId)) {
            // SO asal ikut dihapus total bila belum punya DO (bila sudah ada
            // DO, biarkan + catat agar tidak memutus rantai gudang).
            $punyaDo = DB::table('penjualan_do')->where('so_id', $soId)->exists();

            if ($punyaDo) {
                SalesOrder::where('id', $soId)->update([
                    'is_proforma' => 1,
                    'status_proforma' => 0,
                    'updated_by' => Auth::id(),
                ]);

                Log::warning('hapusProformaTerkait: SO punya DO, hanya reset (tidak hapus)', [
                    'so_id' => $soId,
                ]);
            } else {
                DB::table('penjualan_so_item')->where('so_id', $soId)->delete();
                SalesOrder::where('id', $soId)->delete();
            }
        }

        return ['dihapus' => true, 'so_id' => $soId];
    }

    /**
     * Hapus TOTAL member + parent (bila baru dari mutasi) agar tidak jadi
     * data sampah. Jejak audit tetap ada di ProformaCancelLog + baris
     * pengajuan (status dibatalkan) — jadi hapus fisik di sini aman.
     * Melewati bila masih ada transaksi aktif lain di luar SO asal.
     */
    private function rollbackCustomer(PengajuanProforma $pengajuan, $excludeSoId = null)
    {
        if (!empty($pengajuan->customer_id_hasil)) {
            $soQuery = SalesOrder::where('customer_id', $pengajuan->customer_id_hasil)
                ->where('status', '!=', 0);

            if (!empty($excludeSoId)) {
                $soQuery->where('id', '!=', $excludeSoId);
            }

            if ($soQuery->exists()) {
                Log::warning('rollbackCustomer: ada SO aktif lain, skip rollback', [
                    'customer_id' => $pengajuan->customer_id_hasil,
                ]);
                return false;
            }
        }

        // Member : hapus fisik (tidak ada SoftDeletes; ID dotted tidak dipakai ulang
        // karena counting lanjut dari max suffix yang ada).
        if (!empty($pengajuan->member_id_hasil)) {
            CustomerOtherAddress::where('id', $pengajuan->member_id_hasil)->delete();
        }

        // Parent : hapus fisik HANYA bila baru dibuat dari mutasi ini DAN
        // tidak punya member lain yang masih aktif.
        if ($pengajuan->parent_dibuat_baru && !empty($pengajuan->customer_id_hasil)) {
            $sisaMember = CustomerOtherAddress::where('customer_id', $pengajuan->customer_id_hasil)->exists();

            if ($sisaMember) {
                Log::warning('rollbackCustomer: masih ada member lain, parent tidak dihapus fisik', [
                    'customer_id' => $pengajuan->customer_id_hasil,
                ]);

                $customer = Customer::find($pengajuan->customer_id_hasil);
                if ($customer) {
                    $customer->delete(); // soft-delete : arsip, bukan sampah aktif
                }

                return true;
            }

            $customer = Customer::withTrashed()->find($pengajuan->customer_id_hasil);
            if ($customer) {
                $customer->forceDelete();
            }
        }

        return true;
    }

    private function adminName()
    {
        $user = Auth::user();
        if (!$user) {
            return 'system';
        }
        return $user->name ?? $user->username ?? 'system';
    }
}
