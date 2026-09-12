<?php

namespace Tests\Unit;

use App\Services\Accounting\ProductFinanceService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ProductFinanceImportFilterTest extends TestCase
{
    private function svc(): ProductFinanceService
    {
        return new ProductFinanceService();
    }

    public function test_empty_rows_are_skipped()
    {
        $rows = new Collection([
            ['brand' => 'X', 'code' => 'A', 'name' => 'N', 'kemasan' => 'DUS', 'mitra' => '', 'harga_beli' => '', 'harga_jual' => ''],
            ['brand' => 'X', 'code' => 'B', 'name' => 'N', 'kemasan' => 'DUS', 'mitra' => '  ', 'harga_beli' => ' ', 'harga_jual' => ''],
        ]);

        $this->assertSame([], $this->svc()->extractTargets($rows));
    }

    public function test_only_filled_rows_are_kept_with_trim()
    {
        $rows = new Collection([
            ['brand' => 'X', 'code' => ' A ', 'name' => 'N', 'kemasan' => ' DUS ', 'mitra' => ' UNIFRA ', 'harga_beli' => ' 10 ', 'harga_jual' => '12'],
            ['brand' => 'X', 'code' => 'B', 'name' => 'N', 'kemasan' => 'DUS', 'mitra' => '', 'harga_beli' => '', 'harga_jual' => ''],
            ['brand' => 'X', 'code' => 'C', 'name' => 'N', 'kemasan' => 'DUS', 'mitra' => 'UNIFRA', 'harga_beli' => '', 'harga_jual' => '12'],
        ]);

        $targets = $this->svc()->extractTargets($rows);

        $this->assertCount(2, $targets);
        $this->assertSame('UNIFRA', $targets[0]['mitra']);
        $this->assertSame('A', $targets[0]['code']);
        $this->assertSame('10', $targets[0]['beli']);
        // Baris terisi sebagian tetap diteruskan agar diproses jadi pesan error jelas.
        $this->assertSame('', $targets[1]['beli']);
    }

    public function test_zero_price_counts_as_filled()
    {
        $rows = new Collection([
            ['brand' => 'X', 'code' => 'A', 'name' => 'N', 'kemasan' => 'DUS', 'mitra' => 'UNIFRA', 'harga_beli' => '0', 'harga_jual' => '0'],
        ]);

        $this->assertCount(1, $this->svc()->extractTargets($rows));
    }
}
