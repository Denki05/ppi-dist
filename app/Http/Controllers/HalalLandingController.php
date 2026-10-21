<?php

namespace App\Http\Controllers;

use App\HalalHelper;
use App\HalalDocument;
use App\HalalList;
use App\HalalPdfItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HalalLandingController extends Controller
{
    /**
     * Basis URL publik untuk link PDF/preview.
     * Default config('app.url'); bisa dioverride lewat env HALAL_PUBLIC_URL
     * supaya landing di hosting terpisah tetap memakai host yang benar.
     */
    public static function baseUrl()
    {
        $base = env('HALAL_PUBLIC_URL');
        return rtrim($base ?: config('app.url'), '/');
    }

    /**
     * Link preview PDF (PDF rombakan per produk).
     * ?v= anti-cache agar popup selalu memuat PDF terbaru.
     */
    public static function previewUrl($row)
    {
        return self::baseUrl() . '/halal/produk/' . $row->id . '/preview'
            . '?v=' . ($row->updated_at ? strtotime((string) $row->updated_at) : time());
    }

    /**
     * Link cek sertifikat di situs resmi BPJPH (khusus produk Indonesia).
     */
    public static function bpjphUrl($row)
    {
        if (($row->sumber ?: 'ID') !== 'ID') {
            return null;
        }
        $noRegis = $row->nomor_sertifikat
            ? 'ID' . preg_replace('/ID$/', '', $row->nomor_sertifikat)
            : '';
        return 'https://bpjph.halal.go.id/cari/sertifikat?produk='
            . rawurlencode($row->nama_produk)
            . '&no_regis=' . rawurlencode($noRegis);
    }

    /**
     * Halaman utama landing halal.lsfragrance.id.
     * Pencarian memakai nama produk, id sertifikat dibawa sebagai hidden field.
     */
    public function index()
    {
        return view('halal.index');
    }

    /**
     * Daftar produk halal untuk landing (public).
     * Hanya status "Cocok" yang ditampilkan -> ini yang sudah lolos
     * pencocokan di SH Produk, jadi aman ditampilkan ke publik.
     *
     * GET /api/halal/produk?q=&sumber=ID|SG|ALL&page=1&per_page=24
     *
     * SEMENTARA: default hanya SH Indonesia (ID). SH Singapura (SG) belum
     * ditampilkan ke publik sampai datanya selesai ditinjau.
     * sumber=SG  -> hanya Singapura, sumber=ALL -> keduanya.
     *
     * Balisan: { data: [...], meta: { current_page, last_page, per_page,
     * total, from, to, total_cocok } }
     */
    public function listProduk(Request $request)
    {
        $q = trim($request->get('q', ''));
        $sumber = strtoupper(trim($request->get('sumber', 'ID')));

        $query = HalalHelper::query()
            ->where('status_pencocokan', HalalHelper::STATUS_COCOK);

        if ($sumber !== 'ALL') {
            // Default & 'ID' -> Indonesia saja; 'SG' -> Singapura saja.
            $query->where('sumber', $sumber === 'SG' ? 'SG' : 'ID');
        }

        // 1 huruf: abaikan filter (tampilkan semua) supaya tidak terasa
        // "produk tidak ditemukan" saat baru mulai mengetik.
        if (mb_strlen($q) >= 2) {
            $this->applySearch($query, $q);
        }

        $rows = $query->orderBy('nama_produk', 'asc')
            // Satu produk bisa punya SH ID dan SH SG -> tampilkan ID dulu.
            ->orderByRaw("CASE WHEN sumber = 'ID' THEN 0 WHEN sumber = 'SG' THEN 1 ELSE 2 END")
            ->get([
                'id', 'kode_produk', 'merek', 'sumber', 'nama_produk',
                'nomor_sertifikat', 'status_pencocokan',
                'nomor_urut_list_halal', 'halaman_pdf', 'nomor_urut_pdf',
                'status_pdf', 'updated_at',
            ]);

        // Satu produk bisa punya baris master dobel (master_product_id beda)
        // -> dedup per (merek, kode, sumber). ID & SG tetap terpisah.
        $unique = [];
        foreach ($rows as $r) {
            $key = $r->merek . '|' . $r->kode_produk . '|' . ($r->sumber ?: 'ID');
            if (!isset($unique[$key])) {
                $unique[$key] = $r;
            }
        }
        $rows = array_values($unique);

        $total = count($rows);
        $perPage = (int) $request->get('per_page', 24);
        if ($perPage < 1) {
            $perPage = 1;
        } elseif ($perPage > 60) {
            $perPage = 60;
        }
        $page = max(1, (int) $request->get('page', 1));
        $lastPage = max(1, (int) ceil($total / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }
        $slice = array_slice($rows, ($page - 1) * $perPage, $perPage);

        // Total produk "Cocok" untuk badge di landing (mengikuti filter).
        $totalCocok = $this->countTotalCocok($sumber);

        return response()->json([
            'data' => array_map(function ($r) {
                return $this->mapRow($r);
            }, $slice),
            'meta' => $this->listMeta($page, $perPage, $total, $totalCocok),
        ]);
    }

    /**
     * Jumlah produk "Cocok" unik (merek + kode + sumber) sesuai filter.
     */
    protected function countTotalCocok($sumber = 'ALL')
    {
        $sql = "SELECT COUNT(DISTINCT CONCAT_WS('|', COALESCE(merek,''), COALESCE(kode_produk,''), COALESCE(sumber,'ID'))) AS c
                 FROM halal_helpers WHERE status_pencocokan = ?";
        $params = [HalalHelper::STATUS_COCOK];

        if ($sumber !== 'ALL') {
            $sql .= ' AND sumber = ?';
            $params[] = $sumber === 'SG' ? 'SG' : 'ID';
        }

        return (int) DB::selectOne($sql, $params)->c;
    }

    /**
     * Metadata list + total produk "Cocok" (untuk badge di landing).
     */
    protected function listMeta($page, $perPage, $total, $totalCocok = null)
    {
        if ($totalCocok === null) {
            $totalCocok = $total;
        }
        $lastPage = max(1, (int) ceil($total / $perPage));
        return [
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $total ? (($page - 1) * $perPage) + 1 : 0,
            'to' => $total ? min($page * $perPage, $total) : 0,
            'total_cocok' => $totalCocok,
        ];
    }

    /**
     * Bentuk satu baris produk untuk API (dipakai list & search).
     */
    protected function mapRow($r)
    {
        $sumber = $r->sumber ?: 'ID';
        $label = $r->nama_produk . ' — ' . $r->merek . ' ' . $r->kode_produk;
        $label .= $sumber === 'SG' ? ' [SG]' : ' [ID]';

        return [
            'id' => $r->id,
            'kode' => $r->kode_produk,
            'merek' => $r->merek,
            'sumber' => $sumber,
            'nama' => $label,
            'nama_produk' => $r->nama_produk,
            // Hidden id sertifikasi untuk popup.
            'sertifikat_id' => $r->nomor_sertifikat,
            'status' => $r->status_pencocokan,
            'button_active' => $r->isButtonActive(),
            'nomor_urut_list' => $r->nomor_urut_list_halal,
            'halaman_pdf' => $r->halaman_pdf,
            'nomor_urut_pdf' => $r->nomor_urut_pdf,
            'preview_url' => self::previewUrl($r),
            'bpjph_url' => self::bpjphUrl($r),
        ];
    }

    /**
     * Filter pencarian produk.
     * Kata kunci dipecah per kata: SETIAP kata harus cocok di salah satu
     * kolom (nama / kode / merek / search_keyword) -> "GCF 999925002" tetap
     * ketemu walau kata pertama dan kedua ada di kolom berbeda.
     */
    protected function applySearch($query, $q)
    {
        $tokens = preg_split('/\s+/', trim($q), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tokens as $token) {
            $keyword = HalalHelper::normalizeKeyword('', $token);
            $query->where(function ($w) use ($token, $keyword) {
                $w->where('nama_produk', 'like', '%' . $token . '%')
                    ->orWhere('kode_produk', 'like', '%' . $token . '%')
                    ->orWhere('merek', 'like', '%' . $token . '%')
                    ->orWhere('search_keyword', 'like', '%' . $keyword . '%');
            });
        }
        return $query;
    }

    /**
     * Autocomplete kata kunci. Sumber = tabel bantuan (halal_helpers),
     * bukan cms_*. Cocok dengan dokumen: tabel bantuan sebagai kata kunci.
     */
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        $query = HalalHelper::query();
        // Landing hanya menampilkan Cocok + Perlu ditinjau.
        // Yang Tidak cocok disembunyikan, ditinjau ulang via SH Produk.
        $query->whereIn('status_pencocokan', [HalalHelper::STATUS_COCOK, HalalHelper::STATUS_TINJAU]);
        // SEMENTARA: hanya SH Indonesia (ID) yang ditampilkan ke publik.
        $sumber = strtoupper(trim($request->get('sumber', 'ID')));
        if ($sumber !== 'ALL') {
            $query->where('sumber', $sumber === 'SG' ? 'SG' : 'ID');
        }
        if (mb_strlen($q) >= 2) {
            $this->applySearch($query, $q);
        } elseif ($q !== '') {
            return response()->json([]);
        }
        // Tanpa kata kunci: tampilkan 15 produk langsung, urut abjad.
        // Dengan kata kunci: hasil urut abjad A-Z.
        // Baris ganda master (merek+kode sama) digabung jadi satu agar
        // tidak tampil dobel di landing; yang Cocok diprioritaskan.

        $rows = $query->orderBy('nama_produk', 'asc')
            // Satu produk bisa punya SH ID dan SH SG -> tampilkan ID dulu.
            ->orderByRaw("CASE WHEN sumber = 'ID' THEN 0 WHEN sumber = 'SG' THEN 1 ELSE 2 END")
            ->limit(60)
            ->get([
                'id', 'kode_produk', 'merek', 'sumber', 'nama_produk',
                'nomor_sertifikat', 'status_pencocokan',
                'nomor_urut_list_halal', 'halaman_pdf', 'nomor_urut_pdf', 'updated_at',
            ]);

        // 1 produk bisa punya 2 baris (ID + SG) -> jangan digabung lintas sumber.
        // Dedup hanya dalam sumber yang sama (jaga-jaga data ganda lama).
        $unique = [];
        foreach ($rows as $r) {
            $key = $r->merek . '|' . $r->kode_produk . '|' . ($r->sumber ?: 'ID');
            if (!isset($unique[$key]) || ($r->isButtonActive() && !$unique[$key]->isButtonActive())) {
                $unique[$key] = $r;
            }
        }
        $rows = array_slice(array_values($unique), 0, 15);

        return response()->json(array_map(function ($r) {
            return $this->mapRow($r);
        }, $rows));
    }

    /**
     * Detail satu produk untuk popup (langkah 4-5 dokumen).
     * Frontend memakai nama produk untuk link ke situs halal resmi,
     * sertifikat_id dipakai sebagai hidden field / bukti pendukung.
     */
    public function show($id)
    {
        $row = HalalHelper::findOrFail($id);
        $sumber = $row->sumber ?: 'ID';

        return response()->json([
            'id' => $row->id,
            'kode' => $row->kode_produk,
            'merek' => $row->merek,
            'sumber' => $sumber,
            'nama' => $row->nama_produk,
            'nama_di_list_halal' => $row->nama_di_list_halal,
            'sertifikat_id' => $row->nomor_sertifikat,
            // Format untuk iframe BPJPH lama (ID saja). SG pakai nomor MUIS apa adanya.
            'no_regis' => $sumber === 'SG'
                ? $row->nomor_sertifikat
                : ($row->nomor_sertifikat ? 'ID' . preg_replace('/ID$/', '', $row->nomor_sertifikat) : null),
            'status' => $row->status_pencocokan,
            'button_active' => $row->isButtonActive(),
            // Preview rombakan internal (halaman asli, produk lain disembunyikan).
            'preview_url' => self::previewUrl($row),
            // Link cek sertifikat di situs resmi BPJPH (khusus ID).
            'bpjph_url' => self::bpjphUrl($row),
            'halal_site_query' => $row->nama_produk,
            'halaman_pdf' => $row->halaman_pdf,
            // Nomor tampil mengikuti konvensi SH Produk: ID = fisik - 1 (cover tak bernomor),
            // SG = apa adanya. Estimasi (belum terverifikasi PDF) wajib dilabeli di UI.
            'halaman_tampil' => $row->halaman_pdf
                ? ($sumber === 'SG' ? (int) $row->halaman_pdf : max(1, (int) $row->halaman_pdf - 1))
                : null,
            'halaman_estimasi' => $row->halaman_pdf
                ? ($row->status_pdf !== HalalHelper::PDF_VERIFIED)
                : false,
            'nomor_urut_pdf' => $row->nomor_urut_pdf,
            'nomor_urut_list' => $row->nomor_urut_list_halal,
            'lembaga' => $sumber === 'SG' ? 'MUIS Singapore' : 'BPJPH Indonesia',
            'perusahaan' => $row->nama_perusahaan ?: ($sumber === 'SG' ? 'SENSES INTERNATIONAL PTE. LTD.' : 'PT Kreasi Mewangikan Indonesia'),
        ]);
    }

    /**
     * Preview PDF rombakan: halaman sesuai produk (no urut + no halaman),
     * produk lain di halaman yang sama disembunyikan.
     * ID -> ambil dari halal_pdf_items; SG (belum ada PDF) -> halaman tunggal.
     */
    public function preview($id)
    {
        $row = HalalHelper::findOrFail($id);
        $sumber = $row->sumber ?: 'ID';

        // ID: rombakan pixel-identik (cover + halaman asli, produk lain ditutup).
        if ($sumber === 'ID' && $row->halaman_pdf) {
            try {
                $bytes = (new \App\Services\Halal\HalalIdRombakan())->build($row);
                return response($bytes, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="SH-ID-' . preg_replace('/[^A-Za-z0-9]+/', '-', $row->kode_produk) . '.pdf"',
                ]);
            } catch (\Throwable $e) {
                // Jatuh ke replica dompdf di bawah bila PDF asli tak tersedia.
                \Log::warning('Rombakan FPDI gagal, fallback dompdf: ' . $e->getMessage());
            }
        }

        // SG: halaman Annex A dibangun dari data QR MUIS memakai template
        // resmi; produk yang dipilih saja yang digambar (produk lain tidak
        // digambar, jadi tidak ada garis putih di atas latar gradasi).
        if ($sumber === 'SG') {
            try {
                $bytes = (new \App\Services\Halal\HalalSgAnnex())->build($row);
                return response($bytes, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="SH-SG-' . preg_replace('/[^A-Za-z0-9]+/', '-', $row->kode_produk) . '.pdf"',
                ]);
            } catch (\Throwable $e) {
                // Jatuh ke replica dompdf di bawah bila gagal.
                \Log::warning('SH SG gagal, fallback dompdf: ' . $e->getMessage());
            }
        }

        $docPdf = $row->pdf_document_id
            ? HalalDocument::find($row->pdf_document_id)
            : HalalDocument::where('jenis', 'pdf')->where('sumber', $sumber)->latest('id')->first();
        $docList = $row->list_document_id
            ? HalalDocument::find($row->list_document_id)
            : HalalDocument::where('jenis', 'list')->where('sumber', $sumber)->latest('id')->first();

        $siblings = [];
        $halaman = $row->halaman_pdf;
        if ($sumber === 'SG' && !$halaman && $row->nomor_urut_list_halal) {
            // QR saja tanpa PDF: estimasi halaman (23/hal) agar "X of 5" tetap tampil.
            $halaman = \App\Services\Halal\HalalSgImporter::estimasiHalaman($row->nomor_urut_list_halal);
        }
        if ($halaman && $docPdf) {
            $items = HalalPdfItem::where('document_id', $docPdf->id)
                ->where('halaman', $halaman)->orderBy('nomor_urut')->get();
            // Fallback: PDF ID lama tidak mengisi pdf_document_id -> cari via map nomor.
            if ($items->isEmpty() && $sumber === 'ID') {
                $alt = HalalDocument::where('jenis', 'pdf')->where('sumber', 'ID')->latest('id')->first();
                if ($alt) {
                    $items = HalalPdfItem::where('document_id', $alt->id)
                        ->where('halaman', $halaman)->orderBy('nomor_urut')->get();
                }
            }
            foreach ($items as $it) {
                $siblings[] = [
                    'nomor' => $it->nomor_urut,
                    'nama' => $it->nama_produk,
                    'pilih' => ((int) $it->nomor_urut === (int) ($row->nomor_urut_pdf ?: $row->nomor_urut_list_halal)),
                ];
            }
        } elseif ($docList && $sumber === 'SG') {
            // SG tanpa PDF: halaman diestimasi 23 nomor (dari QR), siblings = daftar
            // nomor sehalaman agar catatan "disembunyikan" akurat; view hanya
            // menampilkan yang pilih (nomor asli dipertahankan, mis. 12/14/15/21).
            $per = \App\Services\Halal\HalalSgImporter::SG_PER_HALAMAN;
            $atas = ((int) ceil($row->nomor_urut_list_halal / $per)) * $per;
            $bawah = $atas - $per + 1;
            $items = \App\HalalList::where('document_id', $docList->id)
                ->whereBetween('nomor_urut', [$bawah, $atas])->orderBy('nomor_urut')->get();
            foreach ($items as $it) {
                $siblings[] = [
                    'nomor' => $it->nomor_urut,
                    'nama' => $it->nama_lengkap,
                    'pilih' => ((int) $it->nomor_urut === (int) $row->nomor_urut_list_halal),
                ];
            }
        }

        if ($sumber === 'SG') {
            $lembaga = 'MUIS Singapore';
            $perusahaan = $row->nama_perusahaan ?: ($docList->nama_perusahaan ?? 'SENSES INTERNATIONAL PTE. LTD.');
            $sertifikat = $row->nomor_sertifikat ?: ($docList->nomor_sertifikat ?? '-');
            $expired = $row->tanggal_kedaluwarsa ?: ($docList->tanggal_kedaluwarsa ?? null);
            $qr = $docList->qr_url ?? null;
            $issued = '10 Apr 2026';
            $totalProduk = null;
        } else {
            $lembaga = 'BPJPH Indonesia';
            $perusahaan = 'PT Kreasi Mewangikan Indonesia';
            $sertifikat = $row->nomor_sertifikat ?: ($docPdf->nomor_sertifikat ?? '-');
            // Normalisasi tampilan: 3521...ID -> ID3521... (sama spt no_regis).
            if (preg_match('/^(\d{15,25})ID$/', (string) $sertifikat, $mm)) {
                $sertifikat = 'ID' . $mm[1];
            }
            $expired = null;
            $qr = null;
            $issued = null;
            $totalProduk = $docPdf ? $docPdf->total_baris : null;
        }

        $garuda = null;
        $garudaPath = public_path('images/garuda-halal.png');
        if (is_file($garudaPath)) {
            $garuda = base64_encode(file_get_contents($garudaPath));
        }
        // QR TTD elektronik (hasil crop PDF asli) + logo BSrE untuk
        // cover & footer lampiran replica.
        $qrTtd = null;
        $qrTtdPath = public_path('images/halal-qr-ttd.png');
        if (is_file($qrTtdPath)) {
            $qrTtd = base64_encode(file_get_contents($qrTtdPath));
        }
        $bsre = null;
        $bsrePath = public_path('images/halal-bsre.png');
        if (is_file($bsrePath)) {
            $bsre = base64_encode(file_get_contents($bsrePath));
        }

        $pdf = \PDF::loadView('halal.preview', compact(
            'row', 'siblings', 'halaman', 'lembaga', 'perusahaan', 'sertifikat', 'expired', 'qr', 'issued', 'totalProduk', 'garuda', 'qrTtd', 'bsre'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('SH-' . $sumber . '-' . preg_replace('/[^A-Za-z0-9]+/', '-', $row->kode_produk) . '.pdf');
    }
}
