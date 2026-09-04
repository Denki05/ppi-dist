<?php

namespace App\Services;

use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\PackingOrder;
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

    public function printSo($soId)
    {
        $result = SalesOrder::where('id', $soId)->first();
        if (!$result) {
            return ['success' => false, 'message' => 'SO tidak ditemukan'];
        }

        $reportPath = $this->basePath . DIRECTORY_SEPARATOR . 'so' . DIRECTORY_SEPARATOR . 'nota_penjualan.rpt';
        $pdfPath = $this->basePath . DIRECTORY_SEPARATOR . 'so' . DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR . $result->so_code . '.pdf';
        $imgPath = public_path('cr/so/export/' . $result->so_code . '.pdf');
        $imgSavePath = public_path('cr/so/export/images/' . $result->so_code . '.jpg');

        try {
            $crapp = New COM($this->comObject);
            $creport = $crapp->OpenReport($reportPath, 1);

            $creport->Database->Tables(1)->SetLogOnInfo($this->server, $this->database, $this->user, $this->password);
            $creport->EnableParameterPrompting = false;
            $creport->RecordSelectionFormula = "{penjualan_so.id}= $result->id";

            $creport->ExportOptions->DiskFileName = $pdfPath;
            $creport->ExportOptions->PDFExportAllPages = true;
            $creport->ExportOptions->DestinationType = 1;
            $creport->ExportOptions->FormatType = 31;
            $creport->Export(false);

            $creport = null;
            $crapp = null;

            $imagick = new Imagick();
            $imagick->setResolution(300, 300);
            $imagick->readImage($imgPath . '[0-4]');
            $imagick->resetIterator();
            $imagick = $imagick->appendImages(true);
            $imagick->writeImages($imgSavePath, true);

            return ['success' => true, 'file' => $imgSavePath];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal export SO: ' . $e->getMessage()];
        }
    }
}
