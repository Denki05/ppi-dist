<?php

namespace App\Http\Controllers\Superuser\Master;

use App\HalalDocument;
use App\HalalHelper;
use App\Http\Controllers\Controller;
use App\Services\Halal\HalalMatcher;
use App\Services\Halal\HalalSgImporter;
use App\Entities\Setting\UserMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class ShProdukController extends Controller
{
    public function __construct()
    {
        $this->route = 'superuser.master.sh_produk';
        $this->user_menu = new UserMenu;
        $this->access = null;
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $access = $this->user_menu;
            $access = $access->where('user_id', $user->id)
                ->whereHas('menu', function ($query2) {
                    $query2->where('route_name', $this->route);
                })
                ->first();
            $this->access = $access;
            return $next($request);
        });
    }

    protected function deny($perm)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->$perm == 0) {
                return redirect()->route('superuser.index')
                    ->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        return null;
    }

    /**
     * Dashboard SH Produk: ringkasan status, dokumen sumber, tabel bantuan.
     */
    public function index(Request $request)
    {
        if ($deny = $this->deny('can_read')) {
            return $deny;
        }

        $stats = HalalHelper::select('status_pencocokan', DB::raw('COUNT(*) c'))
            ->groupBy('status_pencocokan')->pluck('c', 'status_pencocokan');
        $statsId = HalalHelper::where('sumber', 'ID')->select('status_pencocokan', DB::raw('COUNT(*) c'))
            ->groupBy('status_pencocokan')->pluck('c', 'status_pencocokan');
        $statsSg = HalalHelper::where('sumber', 'SG')->select('status_pencocokan', DB::raw('COUNT(*) c'))
            ->groupBy('status_pencocokan')->pluck('c', 'status_pencocokan');

        $docList = HalalDocument::where('jenis', HalalDocument::JENIS_LIST)->where('sumber', 'ID')->latest('id')->first();
        $docPdf = HalalDocument::where('jenis', HalalDocument::JENIS_PDF)->where('sumber', 'ID')->latest('id')->first();
        $docListSg = HalalDocument::where('jenis', HalalDocument::JENIS_LIST)->where('sumber', 'SG')->latest('id')->first();
        $docPdfSg = HalalDocument::where('jenis', HalalDocument::JENIS_PDF)->where('sumber', 'SG')->latest('id')->first();

        $q = HalalHelper::query();
        if ($request->filled('sumber')) {
            $q->where('sumber', $request->sumber);
        }
        if ($request->filled('status')) {
            $q->where('status_pencocokan', $request->status);
        }
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $q->where(function ($w) use ($cari) {
                $w->where('nama_produk', 'like', '%' . $cari . '%')
                    ->orWhere('kode_produk', 'like', '%' . $cari . '%')
                    ->orWhere('nama_di_list_halal', 'like', '%' . $cari . '%');
            });
        }
        $rows = $q->orderByRaw("CASE status_pencocokan WHEN 'Tidak cocok' THEN 0 WHEN 'Perlu ditinjau' THEN 1 ELSE 2 END")
            ->orderBy('sumber')->orderBy('merek')->orderBy('kode_produk')
            ->paginate(25)->appends($request->only(['status', 'cari', 'sumber']));

        $docs = HalalDocument::latest('id')->limit(10)->get();

        $petaConfirmed = HalalMatcher::isBrandMapConfirmed();
        $petaCount = HalalHelper::where('sumber', 'ID')->whereIn('merek', array_keys(HalalMatcher::BRANDMAP))->count();

        return view('superuser.master.sh_produk.index', compact('stats', 'statsId', 'statsSg', 'docList', 'docPdf', 'docListSg', 'docPdfSg', 'rows', 'docs', 'petaConfirmed', 'petaCount'));
    }

    /**
     * Filter tabel bantuan (dipakai full reload maupun AJAX hot reload).
     */
    protected function filteredQuery(Request $request)
    {
        $q = HalalHelper::query();
        if ($request->filled('sumber')) {
            $q->where('sumber', $request->sumber);
        }
        if ($request->filled('status')) {
            $q->where('status_pencocokan', $request->status);
        }
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $q->where(function ($w) use ($cari) {
                $w->where('nama_produk', 'like', '%' . $cari . '%')
                    ->orWhere('kode_produk', 'like', '%' . $cari . '%')
                    ->orWhere('nama_di_list_halal', 'like', '%' . $cari . '%');
            });
        }
        return $q->orderByRaw("CASE status_pencocokan WHEN 'Tidak cocok' THEN 0 WHEN 'Perlu ditinjau' THEN 1 ELSE 2 END")
            ->orderBy('sumber')->orderBy('merek')->orderBy('kode_produk');
    }

    protected function statsArray()
    {
        return HalalHelper::select('status_pencocokan', DB::raw('COUNT(*) c'))
            ->groupBy('status_pencocokan')->pluck('c', 'status_pencocokan');
    }

    /**
     * Data tabel untuk hot reload (tanpa refresh halaman).
     */
    public function data(Request $request)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return response()->json(['error' => 'Tidak punya akses'], 403);
            }
        }
        $rows = $this->filteredQuery($request)->paginate(25);
        return response()->json([
            'rows' => $rows->items(),
            'current_page' => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
            'total' => $rows->total(),
            'stats' => $this->statsArray(),
        ]);
    }

    /**
     * Aksi massal via checkbox: ubah status banyak baris sekaligus.
     */
    public function bulk(Request $request)
    {
        if ($deny = $this->deny('can_update')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Tidak punya akses'], 403);
            }
            return $deny;
        }
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'status_pencocokan' => 'required|in:Cocok,Perlu ditinjau,Tidak cocok',
            'catatan_tinjauan' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $n = HalalHelper::whereIn('id', $request->ids)->update([
            'status_pencocokan' => $request->status_pencocokan,
            'catatan_tinjauan' => $request->catatan_tinjauan,
        ]);
        return response()->json(['updated' => $n, 'stats' => $this->statsArray()]);
    }

    /**
     * Upload Excel list halal baru (langkah 1) + langsung import (langkah 2-3).
     */
    public function uploadList(Request $request)
    {
        if ($deny = $this->deny('can_create')) {
            return $deny;
        }
        $validator = Validator::make($request->all(), [
            'file_list' => 'required|file|mimes:xlsx,xls|max:10240',
            'tanggal_list' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->route('superuser.master.sh_produk.index')->withErrors($validator);
        }

        $path = $request->file('file_list')->storeAs(
            'halal/sumber',
            'list-' . $request->tanggal_list . '.' . $request->file('file_list')->getClientOriginalExtension()
        );
        $doc = HalalDocument::create([
            'jenis' => HalalDocument::JENIS_LIST,
            'sumber' => 'ID',
            'file_path' => 'storage/app/' . $path,
            'original_name' => $request->file('file_list')->getClientOriginalName(),
            'tanggal_versi' => $request->tanggal_list,
            'created_by' => Auth::id(),
        ]);
        $res = (new HalalMatcher())->importList(storage_path('app/' . $path), $doc->id, $request->tanggal_list);
        $doc->update(['total_baris' => $res['total']]);

        return redirect()->route('superuser.master.sh_produk.index')
            ->with('success', "List halal diimport: {$res['total']} baris.");
    }

    /**
     * Upload PDF resmi baru (langkah 1) + langsung import per halaman (langkah 5).
     */
    public function uploadPdf(Request $request)
    {
        if ($deny = $this->deny('can_create')) {
            return $deny;
        }
        $validator = Validator::make($request->all(), [
            'file_pdf' => 'required|file|mimes:pdf|max:20480',
            'tanggal_pdf' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->route('superuser.master.sh_produk.index')->withErrors($validator);
        }

        $path = $request->file('file_pdf')->storeAs(
            'halal/sumber',
            'sertifikat-' . $request->tanggal_pdf . '.pdf'
        );
        $doc = HalalDocument::create([
            'jenis' => HalalDocument::JENIS_PDF,
            'sumber' => 'ID',
            'file_path' => 'storage/app/' . $path,
            'original_name' => $request->file('file_pdf')->getClientOriginalName(),
            'tanggal_versi' => $request->tanggal_pdf,
            'created_by' => Auth::id(),
        ]);
        $res = (new HalalMatcher())->importPdf(storage_path('app/' . $path), $doc->id);
        $doc->update(['total_baris' => $res['total'], 'nomor_sertifikat' => $res['cert']]);

        return redirect()->route('superuser.master.sh_produk.index')
            ->with('success', "PDF diimport: {$res['total']} produk, {$res['pages']} halaman, sertifikat {$res['cert']}.");
    }

    /**
     * Jalankan pencocokan (langkah 4+6) memakai dokumen terbaru.
     */
    public function run()
    {
        if ($deny = $this->deny('can_create')) {
            return $deny;
        }
        $docList = HalalDocument::where('jenis', HalalDocument::JENIS_LIST)->where('sumber', 'ID')->latest('id')->first();
        $docPdf = HalalDocument::where('jenis', HalalDocument::JENIS_PDF)->where('sumber', 'ID')->latest('id')->first();
        if (!$docList || !$docPdf) {
            return redirect()->route('superuser.master.sh_produk.index')
                ->with('error', 'Upload dulu Excel list halal dan PDF resmi sebelum menjalankan pencocokan.');
        }
        $stats = (new HalalMatcher())->match(
            $docList->id, $docPdf->id, $docPdf->nomor_sertifikat,
            date('Y-m-d'), $docList->tanggal_versi, $docPdf->tanggal_versi
        );
        // Rapikan sisa baris ganda warisan ID: sisakan id terkecil per (merek, kode, sumber).
        DB::statement("DELETE h1 FROM halal_helpers h1 INNER JOIN halal_helpers h2 ON h1.merek = h2.merek AND h1.kode_produk = h2.kode_produk AND h1.sumber = h2.sumber AND h1.sumber = 'ID' AND h1.id > h2.id");
        $msg = "Pencocokan selesai — Cocok: {$stats['Cocok']}, Perlu ditinjau: {$stats['Perlu ditinjau']}, Tidak cocok: {$stats['Tidak cocok']}.";
        if (!empty($stats['digabung'])) {
            $msg .= " {$stats['digabung']} baris master ganda digabung.";
        }
        return redirect()->route('superuser.master.sh_produk.index')->with('success', $msg);
    }

    /**
     * Import QR MUIS Singapore (sumber SG) + langsung pencocokan SG.
     */
    public function importQrSg(Request $request)
    {
        if ($deny = $this->deny('can_create')) {
            return $deny;
        }
        $validator = Validator::make($request->all(), [
            'qr_url' => 'required|url',
            'tanggal_list_sg' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->route('superuser.master.sh_produk.index')->withErrors($validator);
        }
        try {
            $res = (new HalalSgImporter())->importQr($request->qr_url, $request->tanggal_list_sg, Auth::id());
        } catch (\Exception $e) {
            return redirect()->route('superuser.master.sh_produk.index')
                ->with('error', 'Gagal import QR MUIS: ' . $e->getMessage());
        }
        $stats = (new HalalSgImporter())->match(
            $res['doc']->id, null, $res['cert'], date('Y-m-d'),
            $request->tanggal_list_sg, null, $res['expired'], $res['doc']->nama_perusahaan
        );

        return redirect()->route('superuser.master.sh_produk.index')
            ->with('success', "QR SG diimport: {$res['total']} produk, sertifikat {$res['cert']} — Cocok: {$stats['Cocok']}, Tinjau: {$stats['Perlu ditinjau']}, Tidak: {$stats['Tidak cocok']}.");
    }

    /**
     * Upload PDF Annex A MUIS (sumber SG, 5 halaman) + import per nomor.
     */
    public function uploadPdfSg(Request $request)
    {
        if ($deny = $this->deny('can_create')) {
            return $deny;
        }
        $validator = Validator::make($request->all(), [
            'file_pdf_sg' => 'required|file|mimes:pdf|max:20480',
            'tanggal_pdf_sg' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->route('superuser.master.sh_produk.index')->withErrors($validator);
        }
        $path = $request->file('file_pdf_sg')->storeAs(
            'halal/sumber',
            'sertifikat-SG-' . $request->tanggal_pdf_sg . '.pdf'
        );
        $doc = HalalDocument::create([
            'jenis' => HalalDocument::JENIS_PDF,
            'sumber' => 'SG',
            'lembaga' => HalalSgImporter::LEMBAGA,
            'file_path' => 'storage/app/' . $path,
            'original_name' => $request->file('file_pdf_sg')->getClientOriginalName(),
            'tanggal_versi' => $request->tanggal_pdf_sg,
            'created_by' => Auth::id(),
        ]);
        $res = (new HalalSgImporter())->importPdf(storage_path('app/' . $path), $doc->id);
        $doc->update(['total_baris' => $res['total'], 'nomor_sertifikat' => $res['cert']]);

        return redirect()->route('superuser.master.sh_produk.index')
            ->with('success', "PDF SG diimport: {$res['total']} produk, {$res['pages']} halaman, sertifikat {$res['cert']}.");
    }

    /**
     * Jalankan pencocokan SG memakai dokumen SG terbaru.
     */
    public function runSg()
    {
        if ($deny = $this->deny('can_create')) {
            return $deny;
        }
        $docList = HalalDocument::where('jenis', HalalDocument::JENIS_LIST)->where('sumber', 'SG')->latest('id')->first();
        $docPdf = HalalDocument::where('jenis', HalalDocument::JENIS_PDF)->where('sumber', 'SG')->latest('id')->first();
        if (!$docList) {
            return redirect()->route('superuser.master.sh_produk.index')
                ->with('error', 'Import dulu QR MUIS Singapore sebelum menjalankan pencocokan SG.');
        }
        $stats = (new HalalSgImporter())->match(
            $docList->id, $docPdf ? $docPdf->id : null, $docList->nomor_sertifikat,
            date('Y-m-d'), $docList->tanggal_versi, $docPdf ? $docPdf->tanggal_versi : null,
            $docList->tanggal_kedaluwarsa, $docList->nama_perusahaan
        );
        DB::statement("DELETE h1 FROM halal_helpers h1 INNER JOIN halal_helpers h2 ON h1.merek = h2.merek AND h1.kode_produk = h2.kode_produk AND h1.sumber = h2.sumber AND h1.sumber = 'SG' AND h1.id > h2.id");

        return redirect()->route('superuser.master.sh_produk.index')
            ->with('success', "Pencocokan SG selesai — Cocok: {$stats['Cocok']}, Perlu ditinjau: {$stats['Perlu ditinjau']}, Tidak cocok: {$stats['Tidak cocok']}.");
    }

    /**
     * Konfirmasi aturan 5: PPI FF / PPI NON FF = IVA.
     * Setelah ini, jalankan pencocokan ulang agar hasilnya menjadi Cocok.
     */
    public function confirmBrandMap()
    {
        if ($deny = $this->deny('can_update')) {
            return $deny;
        }
        HalalMatcher::confirmBrandMap();
        return redirect()->route('superuser.master.sh_produk.index')
            ->with('success', 'Peta merek PPI FF / PPI NON FF = IVA dikonfirmasi. Jalankan pencocokan ulang.');
    }
    public function review(Request $request, $id)
    {
        if ($deny = $this->deny('can_update')) {
            return $deny;
        }
        $validator = Validator::make($request->all(), [
            'status_pencocokan' => 'required|in:Cocok,Perlu ditinjau,Tidak cocok',
            'catatan_tinjauan' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return redirect()->route('superuser.master.sh_produk.index')->withErrors($validator);
        }
        $row = HalalHelper::findOrFail($id);
        $row->update($request->only(['status_pencocokan', 'catatan_tinjauan']));
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $row->status_pencocokan, 'stats' => $this->statsArray()]);
        }
        return redirect()->route('superuser.master.sh_produk.index')
            ->with('success', "Produk {$row->kode_produk} ditinjau: {$row->status_pencocokan}.");
    }
}
