<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Reports\ReportVariantYear;
use App\Entities\Setting\UserMenu;
use App\Services\ReportVariantYearBuilder;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Auth;
use DB;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportVariantYearController extends Controller
{
    public function __construct()
    {
        $this->view = "superuser.report.variant_year.";
        $this->route = "superuser.report.variant_year";
        $this->user_menu = new UserMenu;
        $this->access = null;

        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $access = $this->user_menu
                ->where("user_id", $user->id)
                ->whereHas("menu", function ($query2) {
                    $query2->where("route_name", $this->route);
                })
                ->first();

            $this->access = $access;

            if ($user->is_superuser == 0 && empty($this->access)) {
                return redirect()->route("superuser.index")
                    ->with("error", "Anda tidak punya akses untuk membuka menu terkait");
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = ReportVariantYear::query();

        if ($request->filled("tahun")) {
            $query->where("tahun", $request->tahun);
        }

        if ($request->filled("material_name")) {
            $query->where("material_name", "like", "%".$request->material_name."%");
        }

        $data["data"] = $query->orderBy("material_name")
                               ->orderBy("product_name")
                               ->orderBy("tahun")
                               ->paginate(50);

        $data["availableYears"] = ReportVariantYear::select("tahun")
                                    ->distinct()
                                    ->orderBy("tahun", "desc")
                                    ->pluck("tahun");

        return view($this->view."index", $data);
    }

    public function syncData(Request $request)
    {
        $validated = $request->validate([
            "start" => "nullable|date",
            "end"   => "nullable|date|after_or_equal:start",
        ]);

        if (empty($validated["start"]) || empty($validated["end"])) {
            $range = DB::table("penjualan_so")->selectRaw("MIN(so_date) as min_date")->first();

            if (empty($range->min_date)) {
                return redirect()->back()->with("error", "Tidak ada data penjualan_so ditemukan.");
            }

            $startDate = Carbon::parse($range->min_date)->startOfDay();
            $endDate   = Carbon::now()->endOfDay();
        } else {
            $startDate = Carbon::parse($validated["start"])->startOfDay();
            $endDate   = Carbon::parse($validated["end"])->endOfDay();
        }

        try {
            $summary = ["total" => 0];

            DB::transaction(function () use ($startDate, $endDate, &$summary) {

                $rows = DB::table("penjualan_do_item")
                    ->join("penjualan_do", "penjualan_do.id", "=", "penjualan_do_item.do_id")
                    ->join("penjualan_so", "penjualan_so.id", "=", "penjualan_do.so_id")
                    ->join("master_products_packaging as varian", "varian.id", "=", "penjualan_do_item.product_packaging_id")
                    ->join("master_products as bahan_baku", "bahan_baku.id", "=", "varian.product_id")
                    ->leftJoin("master_packaging as kemasan", "kemasan.id", "=", "varian.packaging_id")
                    ->select(
                        "bahan_baku.brand_name as brand_name",
                        "bahan_baku.material_code as material_code",
                        "bahan_baku.material_name as material_name",
                        "varian.code as product_code",
                        "varian.name as product_name",
                        DB::raw("COALESCE(kemasan.pack_name, \"-\") as packaging"),
                        DB::raw("YEAR(penjualan_so.so_date) as tahun"),
                        DB::raw("SUM(penjualan_do_item.qty) as qty")
                    )
                    ->where("penjualan_do.status", 6)
                    ->whereNull("bahan_baku.packaging_id")
                    ->where("bahan_baku.status", 1)
                    ->where("varian.status", 1)
                    ->whereBetween("penjualan_so.so_date", [$startDate, $endDate])
                    ->groupBy(
                        "bahan_baku.brand_name",
                        "bahan_baku.material_code",
                        "bahan_baku.material_name",
                        "varian.code",
                        "varian.name",
                        "packaging",
                        DB::raw("YEAR(penjualan_so.so_date)")
                    )
                    ->get();

                foreach ($rows as $row) {
                    DB::table("report_variant_year")->updateOrInsert(
                        [
                            "product_code" => $row->product_code,
                            "packaging"    => $row->packaging,
                            "tahun"        => $row->tahun,
                        ],
                        [
                            "brand_name"    => $row->brand_name,
                            "material_code" => $row->material_code,
                            "material_name" => $row->material_name,
                            "product_name"  => $row->product_name,
                            "qty"           => $row->qty,
                            "updated_at"    => now(),
                            "created_at"    => now(),
                        ]
                    );
                    $summary["total"]++;
                }
            });

            Log::channel("single")->info("[SYNC report_variant_year]", $summary + [
                "range" => $startDate->toDateString()." s/d ".$endDate->toDateString(),
            ]);

            return redirect()->back()->with(
                "message",
                "Berhasil sync data ".$startDate->toDateString()." s/d ".$endDate->toDateString()."! Total baris: ".$summary["total"]
            );
        } catch (\Exception $e) {
            Log::error("Sync report_variant_year failed: ".$e->getMessage());
            return redirect()->back()->with("error", "Error: ".$e->getMessage());
        }
    }

    public function removeDt(Request $request)
    {
        $validated = $request->validate([
            "tahun" => "required|integer",
        ]);

        DB::table("report_variant_year")
            ->where("tahun", $validated["tahun"])
            ->delete();

        return redirect()->back()->with("message", "Berhasil hapus data tahun ".$validated["tahun"]."!");
    }

    public function exportExcel(Request $request)
    {
        $builder = new ReportVariantYearBuilder();
        $years   = $builder->getYears();

        if (empty($years)) {
            return redirect()->back()->with("error", "Tidak ada data untuk di-export. Silakan sync data terlebih dahulu.");
        }

        $tree   = $builder->buildTree();
        $result = $builder->flatten($tree, $years);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Variant Year");

        $yearStartCol = 2; // Mulai kolom B untuk tahun
        $yearEndCol = $yearStartCol + count($years) - 1;
        $lastCol = $this->colLetter($yearEndCol); 

        // ===== Header =====
        $sheet->setCellValue("A1", "LAPORAN VARIAN TAHUNAN");
        $sheet->mergeCells("A1:".$lastCol."1"); 
        $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);
        
        $sheet->setCellValue("A2", "Dicetak pada: ".date("d/m/Y H:i:s"));
        $sheet->mergeCells("A2:".$lastCol."2");

        $headerRow = 4; 
        
        // Header Deskripsi
        $sheet->setCellValue("A". $headerRow, "Deskripsi Item");
        $sheet->mergeCells("A". $headerRow . ":A" . ($headerRow + 1));
        
        // Header Tahun Terbentang
        $sheet->mergeCellsByColumnAndRow($yearStartCol, $headerRow, $yearEndCol, $headerRow);
        $sheet->setCellValueByColumnAndRow($yearStartCol, $headerRow, "TAHUN (" . min($years) . " - " . max($years) . ")");
        
        $col = $yearStartCol;
        foreach ($years as $y) {
            $sheet->setCellValueByColumnAndRow($col, $headerRow + 1, $y);
            $col++;
        }

        // Styling Header
        $headerRange = "A". $headerRow . ":" . $lastCol . ($headerRow + 1);
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ===== Data rows =====
        $rowIndex = $headerRow + 2;

        foreach ($result["rows"] as $row) {
            $sheet->setCellValue("A" . $rowIndex, $row["label"]);

            // FUNGSI TREE / GROUPING EXCEL (setOutlineLevel) SUDAH DIHAPUS.

            // STYLING INDENTASI & FONT BARIS
            if ($row["is_total"]) {
                // Styling untuk baris "Total..." di bawah
                $sheet->getStyle("A" . $rowIndex . ":" . $lastCol . $rowIndex)->getFont()->setBold(true);
                $sheet->getStyle("A" . $rowIndex . ":" . $lastCol . $rowIndex)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A" . $rowIndex)->getAlignment()->setIndent( ($row["level"] * 2) + 1 ); // Total sedikit lebih menjorok
                $sheet->getStyle("A" . $rowIndex)->getFont()->setItalic(true);
            } else {
                // Styling untuk baris Header Grup & Kemasan
                $sheet->getStyle("A" . $rowIndex)->getAlignment()->setIndent($row["level"] * 2);
                if ($row["level"] < 3) {
                    $sheet->getStyle("A" . $rowIndex)->getFont()->setBold(true);
                }
            }

            // PRINT ANGKA
            if (!empty($row["qty_per_year"])) {
                $col = $yearStartCol;
                foreach ($years as $y) {
                    $val = $row["qty_per_year"][$y];
                    $sheet->setCellValueByColumnAndRow($col, $rowIndex, $val > 0 ? $val : '-');
                    if ($val > 0) {
                        $sheet->getStyleByColumnAndRow($col, $rowIndex)->getNumberFormat()->setFormatCode("#,##0");
                    }
                    $sheet->getStyleByColumnAndRow($col, $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $col++;
                }
            }
            $rowIndex++;
        }

        // ===== Grand Total =====
        $rowIndex++; 
        $sheet->setCellValue("A". $rowIndex, "GRAND TOTAL KESELURUHAN");
        
        $col = $yearStartCol;
        foreach ($years as $y) {
            $sheet->setCellValueByColumnAndRow($col, $rowIndex, $result["grand_total"][$y]);
            $sheet->getStyleByColumnAndRow($col, $rowIndex)->getNumberFormat()->setFormatCode("#,##0");
            $sheet->getStyleByColumnAndRow($col, $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $col++;
        }
        
        $sheet->getStyle("A". $rowIndex . ":" . $lastCol . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("A". $rowIndex . ":" . $lastCol . $rowIndex)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A". $rowIndex . ":" . $lastCol . $rowIndex)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        $sheet->setShowGridlines(false);

        // Lebar Kolom
        $sheet->getColumnDimension("A")->setWidth(60); // Dilebarkan untuk struktur panjang
        for ($i = $yearStartCol; $i <= $yearEndCol; $i++) {
            $sheet->getColumnDimension($this->colLetter($i))->setAutoSize(true);
        }

        $sheet->freezePane("B" . ($headerRow + 2)); 

        $exportDir = storage_path("app/exports");
        if (!file_exists($exportDir)) mkdir($exportDir, 0777, true);
        
        $fileName = "report_variant_year_".date("Ymd_His").".xlsx";
        $path = $exportDir."/".$fileName;
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return response()->download($path, $fileName)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        ini_set("memory_limit", "1024M"); // HARUS baris pertama, sebelum apapun
        ini_set("max_execution_time", 300);

        $builder = new ReportVariantYearBuilder();
        $years   = $builder->getYears();

        if (empty($years)) {
            return redirect()->back()->with("error", "Tidak ada data untuk di-export. Silakan sync data terlebih dahulu.");
        }

        $tree = $builder->buildTree();

        // Kelompokkan rows per Brand agar bisa dipecah tabelnya di View
        $groupedData = [];
        foreach ($tree as $brandName => $brandContent) {
            $rows = [];
            // Kita gunakan method walk yang sudah ada untuk tiap brand
            // Kita perlu sedikit modifikasi atau panggil secara manual
            $brandTotal = $this->invokePrivateWalk($builder, [$brandName => $brandContent], 0, $years, $rows);
            $groupedData[$brandName] = [
                "rows" => $rows,
                "total" => $brandTotal
            ];
        }

        $pdfTempDir = storage_path("app/public/temp");
        if (!file_exists($pdfTempDir)) {
            mkdir($pdfTempDir, 0777, true);
        }

        $pdf = PDF::setOptions([
            "isHtml5ParserEnabled" => true,
            "isPhpEnabled" => false,
            "isRemoteEnabled" => false,
            "defaultPaperSize" => "a4",
            "tempDir" => $pdfTempDir,
        ])->loadView($this->view."pdf", [
            "years"       => $years,
            "groupedData" => $groupedData,
            "grandTotal"  => $builder->flatten($tree, $years)["grand_total"],
        ])->setPaper("a4", "landscape");

        return $pdf->download("report_variant_year_".date("Ymd_His").".pdf");
    }

    // Helper untuk memanggil private method walk (atau ubah walk menjadi public di Builder)
    private function invokePrivateWalk($builder, $data, $level, $years, &$rows) {
        $reflection = new \ReflectionClass(get_class($builder));
        $method = $reflection->getMethod("walk");
        $method->setAccessible(true);
        return $method->invokeArgs($builder, [$data, $level, $years, &$rows]);
    }

    private function colLetter($colIndex)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
    }
}