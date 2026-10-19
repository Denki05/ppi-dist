<?php

namespace App\Http\Controllers;

use App\HalalHelper;
use App\HalalDocument;
use App\HalalList;
use App\HalalPdfItem;
use Illuminate\Http\Request;

class HalalLandingController extends Controller
{
    /**
     * Halaman utama landing halal.lsfragrance.id.
     * Pencarian memakai nama produk, id sertifikat dibawa sebagai hidden field.
     */
    public function index()
    {
        return view('halal.index');
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
        if (mb_strlen($q) >= 2) {
            $keyword = HalalHelper::normalizeKeyword('', $q);
            $query->where(function ($w) use ($keyword, $q) {
                $w->where('search_keyword', 'like', '%' . $keyword . '%')
                    ->orWhere('nama_produk', 'like', '%' . $q . '%')
                    ->orWhere('kode_produk', 'like', '%' . $q . '%');
            });
        } elseif ($q !== '') {
            return response()->json([]);
        }
        // Tanpa kata kunci: tampilkan 15 produk langsung, urut abjad.
        // Dengan kata kunci: hasil urut abjad A-Z.
        // Baris ganda master (merek+kode sama) digabung jadi satu agar
        // tidak tampil dobel di landing; yang Cocok diprioritaskan.

        $rows = $query->orderBy('nama_produk', 'asc')
            ->limit(60)
            ->get([
                'id', 'kode_produk', 'merek', 'sumber', 'nama_produk',
                'nomor_sertifikat', 'status_pencocokan',
                'nomor_urut_list_halal', 'halaman_pdf', 'nomor_urut_pdf',
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
            $label = $r->nama_produk . ' — ' . $r->merek . ' ' . $r->kode_produk;
            $label .= $r->sumber === 'SG' ? ' [SG]' : ' [ID]';
            return [
                'id' => $r->id,
                'kode' => $r->kode_produk,
                'merek' => $r->merek,
                'sumber' => $r->sumber ?: 'ID',
                // Teks tampil = nama + badge sumber agar user tahu ada 2 SH.
                'nama' => $label,
                'nama_produk' => $r->nama_produk,
                // Hidden id sertifikasi untuk popup.
                'sertifikat_id' => $r->nomor_sertifikat,
                'status' => $r->status_pencocokan,
                'button_active' => $r->isButtonActive(),
                'nomor_urut_list' => $r->nomor_urut_list_halal,
                'halaman_pdf' => $r->halaman_pdf,
                'nomor_urut_pdf' => $r->nomor_urut_pdf,
                'preview_url' => rtrim(url('/halal/produk/' . $r->id . '/preview'), '/'),
            ];
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
            'preview_url' => rtrim(url('/halal/produk/' . $row->id . '/preview'), '/'),
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

        // SG terverifikasi PDF: rombakan pixel-identik (cover + annex asli).
        // Baris estimasi (belum terverifikasi) tetap memakai replica dompdf.
        if ($sumber === 'SG' && $row->halaman_pdf && $row->status_pdf === HalalHelper::PDF_VERIFIED) {
            try {
                $bytes = (new \App\Services\Halal\HalalSgRombakan())->build($row);
                return response($bytes, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="SH-SG-' . preg_replace('/[^A-Za-z0-9]+/', '-', $row->kode_produk) . '.pdf"',
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Rombakan SG gagal, fallback dompdf: ' . $e->getMessage());
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
