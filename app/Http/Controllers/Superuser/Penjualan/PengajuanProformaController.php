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

        if ($pengajuan->status === PengajuanProforma::STATUS_MENUNGGU) {
            $duplikat = $this->mutasiSvc->cekDuplikat($pengajuan);
        }

        return view('superuser.penjualan.pengajuan_proforma.show', compact('pengajuan', 'duplikat', 'fieldErrors'));
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
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $result = $this->mutasiSvc->mutasi(
                $pengajuan,
                $this->adminName(),
                $request->input('catatan'),
                (bool) $request->input('paksa', false)
            );

            // Kirim notif ke AO (non-blocking — gagal tidak rollback mutasi)
            $this->mutasiSvc->notifAo($pengajuan->fresh());

            $msg  = 'Mutasi berhasil. ';
            $msg .= $result['parent_baru'] ? 'Customer baru dibuat. ' : 'Customer existing dipakai. ';
            $msg .= $result['member_baru'] ? 'Member baru dibuat.' : 'Member existing dipakai.';

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
    // TOLAK (sebelum mutasi)
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

        $pengajuan->update([
            'status'      => PengajuanProforma::STATUS_DITOLAK,
            'catatan'     => $request->catatan,
            'verified_by' => $this->adminName(),
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('superuser.penjualan.pengajuan_proforma.index')
            ->with('success', 'Pengajuan ditolak.');
    }

    // ------------------------------------------------------------------
    // CANCEL PROFORMA (setelah mutasi — customer sudah di existing)
    // ------------------------------------------------------------------

    /**
     * POST /penjualan/pengajuan-proforma/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
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
                $request->input('proforma_code')
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
