<?php

namespace App\Imports\Accounting;

use App\Services\Accounting\ProductFinanceService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductFinanceImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error = ['No failed import.'];
    public $success = ['No successful import.'];

    private $hasProcessed = false;

    public function collection(Collection $rows)
    {
        $result = app(ProductFinanceService::class)->processImportRows($rows);

        // File template punya 2 sheet (Template + Daftar Mitra) dan reader
        // memanggil ini per sheet. Hanya sheet yang berisi baris terisi
        // yang boleh menentukan pesan akhir; sheet kosong (mis. Daftar
        // Mitra) tidak boleh menimpa hasil sheet Template.
        if ($result['processed'] > 0 || ! $this->hasProcessed) {
            $this->success = $result['success'];
            $this->error = $result['error'];
        }

        if ($result['processed'] > 0) {
            $this->hasProcessed = true;
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
