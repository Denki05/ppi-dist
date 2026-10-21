<?php

namespace App\Services\Halal;

use App\HalalDocument;

/**
 * Aset visual PDF resmi MUIS (gambar latar halaman) yang diekstrak sekali
 * lalu dipakai ulang untuk menutupi produk yang tidak dipilih pada halaman
 * Annex A. Menutup dengan potongan latar (bukan kotak putih) membuat
 * produk tersembunyi menyatu dengan gradasi latar resmi.
 */
class HalalSgAssets
{
    const DIR = 'halal/aset/sg';

    /**
     * Siapkan aset (gambar latar). Diekstrak dari PDF resmi bila belum ada.
     *
     * @param \App\HalalDocument $docPdf dokumen PDF SG
     * @return array|null ['bg' => path jpg/png]
     */
    public static function ensure(HalalDocument $docPdf)
    {
        $dir = self::dir();
        $bgJpg = $dir . '/bg.jpg';
        $bgPng = $dir . '/bg.png';
        if (is_file($bgJpg) || is_file($bgPng)) {
            return ['bg' => is_file($bgJpg) ? $bgJpg : $bgPng];
        }
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $path = self::resolvePath($docPdf->file_path);
        if (!is_file($path)) {
            return null;
        }
        try {
            $pdf = new HalalSgPdf($path);
            foreach ($pdf->pageObjects() as $num) {
                $dict = $pdf->objectDict($num);
                if ($dict === '') {
                    continue;
                }
                $places = $pdf->imagePlacements($dict);
                if (count($places) !== 2) {
                    continue;
                }
                // Latar = gambar dengan area placement terbesar.
                $best = null;
                foreach ($places as $p) {
                    if (!$p['object']) {
                        continue;
                    }
                    $area = abs($p['matrix'][0] * $p['matrix'][3]);
                    if ($best === null || $area > $best['area']) {
                        $best = ['object' => $p['object'], 'area' => $area];
                    }
                }
                if (!$best) {
                    continue;
                }
                if ($pdf->saveImage($best['object'], $bgJpg)) {
                    return ['bg' => $bgJpg];
                }
                if ($pdf->saveImage($best['object'], $bgPng)) {
                    return ['bg' => $bgPng];
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Gagal ekstrak aset PDF SG: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Lokasi folder aset.
     * @return string
     */
    public static function dir()
    {
        return storage_path('app/' . self::DIR);
    }

    /**
     * Buang cache aset (dipanggil saat PDF SG baru diunggah).
     */
    public static function clearCache()
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/*') as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    /**
     * Path absolut file dari kolom file_path dokumen.
     * @param string $filePath
     * @return string
     */
    public static function resolvePath($filePath)
    {
        $filePath = (string) $filePath;
        $cands = [
            base_path($filePath),
            storage_path('app/' . preg_replace('#^(storage/app/|app/)#', '', $filePath)),
            public_path($filePath),
        ];
        foreach ($cands as $c) {
            if (is_file($c)) {
                return $c;
            }
        }
        return base_path($filePath);
    }
}
