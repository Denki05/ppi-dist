<?php

namespace App\Services;

use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\PackingOrder;
use COM;
use Imagick;

class CrystalReportService
{
    private $server;
    private $user;
    private $password;
    private $database;
    private $comObject;
    private $basePath;

    public function __construct()
    {
        $this->server = env('CRYSTAL_SERVER', 'LOCAL');
        $this->user = env('CRYSTAL_USER', 'root');
        $this->password = env('CRYSTAL_PASSWORD', '');
        $this->database = env('CRYSTAL_DATABASE', 'ppi-dist');
        $this->comObject = 'CrystalDesignRunTime.Application';
        $this->basePath = base_path('public') . DIRECTORY_SEPARATOR . 'cr';
    }

    public function printProforma($id)
    {
        $result = SalesOrder::where('id', $id)->first();
        if (!$result) {
            return ['success' => false, 'message' => 'SO tidak ditemukan'];
        }

        $get_do = PackingOrder::where('so_id', $result->id)->first();

        $reportPath = $this->basePath . DIRECTORY_SEPARATOR . 'proforma' . DIRECTORY_SEPARATOR . 'proforma.rpt';
        $pdfPath = $this->basePath . DIRECTORY_SEPARATOR . 'proforma' . DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR . $result->code . '.pdf';

        if (!class_exists('COM')) {
            return ['success' => false, 'message' => 'PHP COM extension / Crystal Runtime tidak tersedia di server ini.'];
        }

        try {
            $crapp = New COM($this->comObject);
            $creport = $crapp->OpenReport($reportPath, 1);

            $creport->Database->Tables(1)->SetLogOnInfo($this->server, $this->database, $this->user, $this->password);
            $creport->EnableParameterPrompting = false;
            $creport->RecordSelectionFormula = "{penjualan_do.id}= $get_do->id";

            $creport->ExportOptions->DiskFileName = $pdfPath;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = 31;
            $creport->Export(false);

            $creport = null;
            $crapp = null;

            return ['success' => true, 'file' => $pdfPath, 'filename' => $result->code . '.pdf'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal export proforma: ' . $e->getMessage()];
        }
    }

    /**
     * Cetak nota SO via dompdf (pengganti Crystal Report agar jalan di semua OS).
     * Kontrak return sama: ['success', 'file' => path JPG (jika Imagick ada) atau PDF, ...]
     */
    public function printSo($soId)
    {
        // NOTE: 'member' sengaja tidak di-eager-load: PK string model CustomerOtherAddress
        // membuat eager load belongsTo mengembalikan null; lazy-load justru jalan normal.
        $result = SalesOrder::with(['so_detail.product_pack.packaging'])->where('id', $soId)->first();
        if (!$result) {
            return ['success' => false, 'message' => 'SO tidak ditemukan'];
        }

        $exportDir = $this->basePath . DIRECTORY_SEPARATOR . 'so' . DIRECTORY_SEPARATOR . 'export';
        $imagesDir = $exportDir . DIRECTORY_SEPARATOR . 'images';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        if (!is_dir($imagesDir)) {
            mkdir($imagesDir, 0755, true);
        }

        $pdfPath = $exportDir . DIRECTORY_SEPARATOR . $result->so_code . '.pdf';
        $imgSavePath = $imagesDir . DIRECTORY_SEPARATOR . $result->so_code . '.jpg';

        // Fallback tanggal bila so_date kosong (dipakai view nota)
        if (empty($result->so_date)) {
            $result->so_date = $result->created_at;
        }

        try {
            \PDF::loadView('superuser.penjualan.sales_order.print_pdf', ['result' => $result])
                ->setPaper('a5', 'landscape')
                ->save($pdfPath);

            // Convert PDF -> JPG seperti alur lama (maks 5 halaman pertama digabung vertikal)
            if ($this->pdfToImage($pdfPath, $imgSavePath)) {
                return ['success' => true, 'file' => $imgSavePath];
            }

            return ['success' => true, 'file' => $pdfPath];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal export SO: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Gagal export SO: ' . $e->getMessage()];
        }
    }

    /**
     * Convert PDF -> JPG tunggal (5 halaman pertama digabung vertikal).
     * Urutan: Imagick dulu, lalu Ghostscript + GD. False bila tak ada yang tersedia.
     */
    protected function pdfToImage($pdfPath, $imgSavePath, $maxPages = 5, $dpi = 150)
    {
        try {
            if (class_exists('Imagick')) {
                $imagick = new Imagick();
                $imagick->setResolution($dpi, $dpi);
                $imagick->readImage($pdfPath . '[0-' . ($maxPages - 1) . ']');
                $imagick->resetIterator();
                $imagick = $imagick->appendImages(true);
                $imagick->setImageFormat('jpg');
                $imagick->writeImages($imgSavePath, true);

                return true;
            }

            $gs = $this->findGhostscript();
            if (!$gs || !function_exists('exec') || !function_exists('imagecreatefromjpeg')) {
                return false;
            }

            // Render per halaman dengan nama file eksplisit (hindari pola %d
            // yang tidak tersubstitusi di sebagian environment/CLI)
            $pages = [];
            for ($i = 1; $i <= $maxPages; $i++) {
                $tmp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
                    . DIRECTORY_SEPARATOR . 'so_nota_' . $i . '.jpg';
                @unlink($tmp);
                $cmd = $gs
                    . ' -dBATCH -dNOPAUSE -sDEVICE=jpeg -dJPEGQ=90 -r' . (int) $dpi
                    . ' -dFirstPage=' . $i . ' -dLastPage=' . $i
                    . ' -sOutputFile=' . escapeshellarg($tmp)
                    . ' ' . escapeshellarg($pdfPath) . ' 2>&1';
                exec($cmd);
                if (is_file($tmp)) {
                    $pages[] = $tmp;
                } else {
                    break; // halaman tidak ada -> stop, hindari request halaman kosong
                }
            }
            if (empty($pages)) {
                return false;
            }

            // Gabung vertikal via GD
            $width = 0;
            $height = 0;
            $images = [];
            foreach ($pages as $tmp) {
                $img = @imagecreatefromjpeg($tmp);
                if (!$img) {
                    continue;
                }
                $images[] = $img;
                $width = max($width, imagesx($img));
                $height += imagesy($img);
            }
            foreach ($pages as $tmp) {
                @unlink($tmp);
            }
            if (empty($images)) {
                return false;
            }

            $canvas = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
            $y = 0;
            foreach ($images as $img) {
                imagecopy($canvas, $img, 0, $y, 0, 0, imagesx($img), imagesy($img));
                $y += imagesy($img);
                imagedestroy($img);
            }
            imagejpeg($canvas, $imgSavePath, 90);
            imagedestroy($canvas);

            return is_file($imgSavePath);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Cari binary Ghostscript (Windows: gswin64c/gswin32c, Linux: gs).
     */
    protected function findGhostscript()
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        foreach (['gswin64c', 'gswin32c', 'gs'] as $bin) {
            if ($isWindows) {
                $out = [];
                @exec('where ' . escapeshellarg($bin) . ' 2>NUL', $out);
                if (!empty($out)) {
                    return $bin;
                }
            } else {
                $out = [];
                @exec('which ' . escapeshellarg($bin) . ' 2>/dev/null', $out);
                if (!empty($out)) {
                    return $bin;
                }
            }
        }

        return null;
    }
}
