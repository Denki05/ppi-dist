<?php

namespace App\Services\Penjualan;

use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\ProductPack;
use App\Entities\Penjualan\PengajuanProforma;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrderProforma;
use App\Entities\Penjualan\SalesOrderProformaDetails;
use App\Entities\Penjualan\SalesOrderProformaItem;
use App\Repositories\CodeRepo;
use App\Services\SalesOrder\SalesOrderWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Retry pembuatan SO Awal + Proforma dari pengajuan yang sudah disetujui
 * namun SO/proforma-nya gagal terbentuk (mis. push AO 422/500).
 *
 * Sumber data MURNI lokal (items_json pengajuan):
 *  - produk di-resolve via master_products_packaging.code (+ preferensi harga)
 *  - baris duplikat ke-2 dst dianggap FREE (beli + bonus), selaras aturan
 *    duplikat baru pp_id|free di AoSalesOrderApiController@store
 *  - brand = mayoritas brand produk ter-resolve (gagal bila campur)
 *  - kurs diinput admin saat retry (pengajuan tidak menyimpan kurs)
 *
 * Guard status 4 (TUTUP) SENGAJA tidak diproses otomatis — butuh diskusi
 * dulu (lempar RuntimeException bila SO terkait sudah Tutup).
 */
class PengajuanRetryService
{
    /**
     * Info read-only untuk tombol retry di show.blade.php.
     * Tidak menulis DB.
     *
     * @return array ['so'=>?SalesOrder,'proforma'=>?SalesOrderProforma,'canRetry'=>bool,
     *                'reason'=>?string,'brand'=>?string,'itemCount'=>int,'freeCount'=>int]
     */
    public function statusFor(PengajuanProforma $p): array
    {
        $out = [
            'so' => null, 'proforma' => null, 'canRetry' => false,
            'reason' => null, 'brand' => null, 'itemCount' => 0, 'freeCount' => 0,
        ];

        if ($p->status !== PengajuanProforma::STATUS_DISETUJUI) {
            $out['reason'] = 'Hanya pengajuan disetujui yang bisa di-retry.';
            return $out;
        }
        if (empty($p->member_id_hasil)) {
            $out['reason'] = 'Belum ada member hasil mutasi.';
            return $out;
        }

        $out['so'] = SalesOrder::where('customer_other_address_id', $p->member_id_hasil)
            ->orderBy('id', 'desc')->first();
        $out['proforma'] = SalesOrderProforma::where('customer_other_address_id', $p->member_id_hasil)
            ->orderBy('id', 'desc')->first();

        if ($out['proforma']) {
            $out['reason'] = 'Proforma sudah ada (' . ($out['proforma']->code ?? ('#' . $out['proforma']->id)) . ').';
            return $out;
        }
        if ($out['so'] && (int) $out['so']->status === 4) {
            $out['reason'] = 'SO sudah TUTUP (status 4) — butuh diskusi dulu, tidak diproses otomatis.';
            return $out;
        }
        if ($out['so']) {
            // SO ada tapi proforma belum → retry hanya buatkan proforma.
            $out['canRetry'] = true;
            $out['reason'] = null;
            return $out;
        }

        // Belum ada SO sama sekali → preview resolusi item/brand agar admin yakin.
        try {
            $resolved = $this->resolveItems($p);
            $out['brand'] = $resolved['brand'];
            $out['itemCount'] = count($resolved['items']);
            $out['freeCount'] = collect($resolved['items'])->where('free', 1)->count();
            $out['canRetry'] = true;
        } catch (\RuntimeException $e) {
            $out['reason'] = $e->getMessage();
        }

        return $out;
    }

    /**
     * Eksekusi retry: buat SO (bila belum ada) + proforma Terbuat.
     * Idempoten: SO/proforma yang sudah ada dipakai ulang, tidak digandakan.
     *
     * @return array ['so'=>SalesOrder,'proforma'=>SalesOrderProforma,'so_baru'=>bool]
     * @throws \RuntimeException
     */
    public function retry(PengajuanProforma $p, int $userId, float $kurs): array
    {
        if ($p->status !== PengajuanProforma::STATUS_DISETUJUI) {
            throw new \RuntimeException('Hanya pengajuan disetujui yang bisa di-retry.');
        }
        if (empty($p->member_id_hasil) || empty($p->customer_id_hasil)) {
            throw new \RuntimeException('Pengajuan ini belum punya hasil mutasi.');
        }
        if ($kurs < 1000) {
            throw new \RuntimeException('Kurs tidak valid (' . $kurs . '). Gunakan angka penuh, cth: 19000.');
        }

        $member = CustomerOtherAddress::where('id', $p->member_id_hasil)->first();
        if (!$member) {
            throw new \RuntimeException('Member hasil mutasi tidak ditemukan: ' . $p->member_id_hasil);
        }

        return DB::transaction(function () use ($p, $member, $userId, $kurs) {
            // 1. Proforma sudah ada → selesai (idempoten).
            $proforma = SalesOrderProforma::where('customer_other_address_id', $p->member_id_hasil)
                ->orderBy('id', 'desc')->first();
            if ($proforma) {
                throw new \RuntimeException('Proforma sudah ada, retry dibatalkan (tidak digandakan).');
            }

            // 2. SO existing?
            $so = SalesOrder::where('customer_other_address_id', $p->member_id_hasil)
                ->orderBy('id', 'desc')->first();
            $soBaru = false;

            if ($so && (int) $so->status === 4) {
                throw new \RuntimeException('SO sudah TUTUP (status 4) — butuh diskusi dulu, tidak diproses otomatis.');
            }

            if (!$so) {
                $resolved = $this->resolveItems($p);
                $typeName = $this->mapTermin($p->termin);

                $so = new SalesOrder();
                $so->so_code = CodeRepo::generateSoAwal();
                $so->brand_name = $resolved['brand'];
                $so->customer_id = $p->customer_id_hasil;
                $so->customer_other_address_id = $p->member_id_hasil;
                $so->type_transaction = $typeName;
                $so->so_for = 1;
                $so->so_date = null;
                $so->type_so = 'nonppn';
                $so->approval_mou = 0;
                $so->idr_rate = $kurs;
                $so->disc_percent = 0;
                $so->disc_idr = 0;
                $so->disc_usd = 0;
                $so->disc_kemasan = 0;
                $so->note = trim('[RETRY:' . $p->estimate_number . '] dibuat manual dari pengajuan proforma');
                if (\Illuminate\Support\Facades\Schema::hasColumn('penjualan_so', 'ao_order_number')) {
                    $so->ao_order_number = 'RETRY:' . $p->estimate_number;
                }
                $so->is_proforma = 0;
                $so->code = null;
                $so->status = 1;
                $so->so_indent = SalesOrder::INDENT['NO'];
                $so->condition = 1;
                $so->payment_status = 0;
                $so->count_rev = 0;
                $so->created_by = $userId ?: null;
                if (in_array($typeName, ['CASH', 'TEMPO'], true)) {
                    $so->is_estimate = 1;
                    $so->estimate_code = $p->estimate_number;
                } else {
                    $so->is_estimate = 0;
                }
                $so->save();

                foreach ($resolved['items'] as $ri) {
                    $d = new SalesOrderItem();
                    $d->so_id = $so->id;
                    $d->product_packaging_id = $ri['pp_id'];
                    $d->price = $ri['price'];
                    $d->qty = $ri['qty'];
                    $d->disc_usd = $ri['disc'];
                    $d->packaging_id = $ri['packaging_id'];
                    $d->free_product = $ri['free'];
                    $d->kontrak = 0;
                    $d->created_by = $userId ?: null;
                    $d->status = 1;
                    $d->save();
                }

                // Samakan alur push AO: lanjutkan. Untuk CASH+estimate, workflow
                // OTOMATIS membuatkan dokumen proforma (status AKTIF=1, tinggal
                // kalkulasi admin) — tidak perlu dibuat manual lagi.
                $wf = new SalesOrderWorkflowService();
                $hasil = $wf->lanjutkan($so);
                $so->refresh();
                $soBaru = true;

                Log::info('PengajuanRetry: SO dibuat', [
                    'estimate' => $p->estimate_number, 'so_id' => $so->id, 'so_code' => $so->so_code,
                    'tipe' => $hasil['type'],
                ]);
            }

            // 3. Proforma: untuk jalur proforma (CASH) ambil yang dibuat workflow;
            // fallback buat manual bila belum ada. Jalur lanjutan non-CASH
            // memang tanpa proforma (SO status 2 = SO Lanjutan).
            $proforma = SalesOrderProforma::where('so_id', $so->id)->orderBy('id', 'desc')->first();
            if (!$proforma && $so->is_proforma == 1) {
                $proforma = $this->buatProformaTerbuat($so);
            }

            // 4. Catat jejak di pengajuan (tanpa ubah status hasil mutasi).
            $p->update([
                'catatan' => trim(($p->catatan ? $p->catatan . ' | ' : '') . 'RETRY-SO: ' . $so->so_code . ($proforma ? ' + ' . $proforma->code : ' (tanpa proforma)')),
            ]);

            return ['so' => $so->fresh(), 'proforma' => $proforma, 'so_baru' => $soBaru];
        });
    }

    // ------------------------------------------------------------------
    // PRIVATE
    // ------------------------------------------------------------------

    private function mapTermin($termin): string
    {
        $t = strtoupper(trim((string) $termin));
        if (strpos($t, 'TEMPO') === 0) {
            return 'TEMPO';
        }
        return 'CASH';
    }

    /**
     * Resolve items_json → item SO lengkap.
     * @return array ['brand'=>string,'items'=>[...]]
     * @throws \RuntimeException
     */
    private function resolveItems(PengajuanProforma $p): array
    {
        $raw = json_decode((string) $p->items_json, true);
        if (!is_array($raw) || count($raw) < 1) {
            throw new \RuntimeException('items_json pengajuan kosong — buat SO manual.');
        }

        $seen = [];
        $items = [];
        $brands = [];
        foreach ($raw as $idx => $row) {
            $code = trim((string) ($row['code'] ?? ''));
            $qty = (float) ($row['qty'] ?? 0);
            $price = (float) ($row['price'] ?? 0);
            $total = isset($row['total']) ? (float) $row['total'] : null;
            if ($code === '' || $qty < 0.01 || $price < 0) {
                throw new \RuntimeException('Baris item ke-' . ($idx + 1) . ' tidak valid (code/qty/price).');
            }

            // Heuristik free: kemunculan ke-2 dst kode yang sama = bonus.
            $seen[$code] = ($seen[$code] ?? 0) + 1;
            $free = $seen[$code] > 1 ? 1 : 0;

            $pp = $this->resolveProduct($code, $price);
            $disc = 0;
            if (!$free && $total !== null && $total < $price * $qty) {
                $disc = round($price * $qty - $total, 2);
            }

            $items[] = [
                'pp_id' => $pp->id,
                'price' => $price,
                'qty' => $qty,
                'disc' => $disc,
                'packaging_id' => $pp->packaging_id,
                'free' => $free,
            ];
            $brands[] = $this->brandOf($pp);
        }

        // Validasi duplikat pp_id|free (selaras store API).
        $keys = array_map(function ($v) {
            return (string) $v['pp_id'] . '|' . (int) $v['free'];
        }, $items);
        if (count($keys) !== count(array_unique($keys))) {
            throw new \RuntimeException('Item duplikat (produk + tipe free sama 2x) — rapikan dulu manual.');
        }

        // Brand mayoritas; tolak bila campur (SO hanya 1 brand).
        $count = array_count_values(array_filter($brands));
        if (empty($count)) {
            throw new \RuntimeException('Brand produk tidak terdeteksi — buat SO manual.');
        }
        arsort($count);
        $top = array_key_first($count);
        if (count($count) > 1) {
            $second = array_values($count)[1];
            if ($second >= reset($count) / 2 && $second > 0 && count(array_unique(array_filter($brands))) > 1) {
                // Campur berarti — hanya tolak bila benar-benar 2 brand berbeda signifikan.
                $uniq = array_values(array_unique(array_filter($brands)));
                if (count($uniq) > 1) {
                    throw new \RuntimeException('Item campur brand (' . implode('/', $uniq) . ') — buat SO manual per brand.');
                }
            }
        }

        return ['brand' => $top, 'items' => $items];
    }

    private function resolveProduct(string $code, float $price): ProductPack
    {
        $cands = ProductPack::where('code', $code)->get();
        if ($cands->isEmpty()) {
            throw new \RuntimeException('Produk kode "' . $code . '" tidak ditemukan di master.');
        }
        if ($cands->count() === 1) {
            return $cands->first();
        }
        // Preferensi harga sama (toleransi 1 sen), lalu packaging terkecil (-4 dulu).
        $same = $cands->filter(function ($c) use ($price) {
            return abs((float) $c->price - $price) < 0.011;
        });
        $pool = $same->count() ? $same : $cands;
        return $pool->sortBy('packaging_id')->first();
    }

    private function brandOf(ProductPack $pp): ?string
    {
        try {
            $row = DB::table('master_products')->where('id', $pp->product_id)->value('brand_name');
            $row = trim((string) $row);
            return $row !== '' ? $row : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Buatkan dokumen proforma status Terbuat dari SO (idempoten per so_id).
     */
    private function buatProformaTerbuat(SalesOrder $so): SalesOrderProforma
    {
        $exists = SalesOrderProforma::where('so_id', $so->id)->first();
        if ($exists) {
            return $exists;
        }

        $items = SalesOrderItem::where('so_id', $so->id)->get();
        if ($items->isEmpty()) {
            throw new \RuntimeException('SO tanpa item, proforma tidak bisa dibuat.');
        }

        $typeMap = ['CASH' => 1, 'TEMPO' => 2, 'MARKETPLACE' => 3, 'COD' => 4];
        $typeInt = $typeMap[strtoupper(trim((string) $so->type_transaction))] ?? 1;

        $doc = new SalesOrderProforma();
        $doc->so_id = $so->id;
        $doc->code = CodeRepo::generateSoProforma();
        $doc->customer_other_address_id = $so->customer_other_address_id;
        $doc->so_date = now()->toDateString();
        $doc->so_brand_name = $so->brand_name;
        $doc->so_type_transaction = $typeInt;
        $doc->so_idr_rate = $so->idr_rate;
        $doc->warehouse_id = null;
        $doc->note = '[RETRY] dari ' . ($so->so_code ?: ('SO#' . $so->id)) . ' — warehouse + kalkulasi oleh admin.';
        $doc->status = 1;
        $doc->so_lanjutan = 0;
        $doc->transfer_verified = 0;
        $doc->exsisting_customer = 1;
        $doc->created_by = $so->created_by;
        $doc->save();

        $purchase = 0;
        foreach ($items as $it) {
            $line = new SalesOrderProformaItem();
            $line->so_proforma_id = $doc->id;
            $line->product_packaging_id = $it->product_packaging_id;
            $line->price = (float) $it->price;
            $line->qty = (float) $it->qty;
            $line->disc_usd = (float) ($it->disc_usd ?: 0);
            $line->packaging_id = $it->packaging_id;
            $line->free_product = (int) ($it->free_product ?: 0);
            $line->total_item = ((float) $it->price) * ((float) $it->qty);
            $line->save();
            $purchase += $line->total_item;
        }

        $cost = new SalesOrderProformaDetails();
        $cost->so_proforma_id = $doc->id;
        $cost->discount_1_percent = 0;
        $cost->discount_1 = 0;
        $cost->discount_2_percent = 0;
        $cost->discount_2 = 0;
        $cost->discount_idr = 0;
        $cost->voucher_idr = 0;
        $cost->purchase_total_idr = $purchase;
        $cost->delivery_cost_idr = 0;
        $cost->grand_total_idr = $purchase;
        $cost->save();

        $so->status_proforma = 2; // TERBUAT — samakan alur push AO
        $so->save();

        Log::info('PengajuanRetry: proforma dibuat', ['so_id' => $so->id, 'proforma' => $doc->code]);

        return $doc;
    }
}
