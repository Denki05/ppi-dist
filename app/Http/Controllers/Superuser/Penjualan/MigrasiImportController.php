<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Penjualan\ImportSoHeader;
use App\Imports\Penjualan\ImportSoList;
use App\Entities\Master\CustomerOtherAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrasiImportController extends Controller
{
    public function import(Request $request)
    {
        try {
            if ($request->hasFile('header')) {
                Excel::import(new ImportSoHeader, $request->file('header'));
            }

            if ($request->hasFile('list')) {
                Excel::import(new ImportSoList, $request->file('list'));
            }

            return back()->with('success', 'Import berhasil.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function prosesMigrasi(Request $request)
    {
        DB::beginTransaction();
        try {
            $headers = DB::table('migrasi_so_header')->get();
            $customerAddresses = DB::table('master_customer_other_addresses')->get();

            $jumlahBerhasil = 0;
            $jumlahGagal = 0;

            foreach ($headers as $header) {
                $customerName = strtolower($header->customer);
                $matchedAddress = null;

                // Cocokkan customer: berdasarkan name + kota
                foreach ($customerAddresses as $addr) {
                    $nameLower = strtolower($addr->name);
                    $kotaLower = strtolower($addr->text_kota);

                    if (strpos($customerName, $nameLower) !== false &&
                        strpos($customerName, $kotaLower) !== false) {
                        $matchedAddress = $addr;
                        break;
                    }
                }

                // Jika tidak cocok, log gagal
                if (!$matchedAddress) {
                    DB::table('migrasi_so_gagal')->insert([
                        'code' => $header->code,
                        'keterangan' => "Customer tidak ditemukan: " . $header->customer,
                        'customer' => $header->customer,
                        'product' => null,
                        'created_at' => now(),
                    ]);
                    $jumlahGagal++;
                    continue;
                }

                // Cek duplikasi kode SO
                $exists = DB::table('penjualan_so')->where('so_code', $header->code)->exists();
                if ($exists) {
                    continue;
                }

                $brand = $header->brand;

                if($brand == 247) {
                    $brandName = "Senses";
                } elseif ($brand == 287) {
                    $brandName = "GCF";
                } elseif ($brand == 327){
                    $brandName = "PPI";
                }

                // Insert ke penjualan_so
                $soId = DB::table('penjualan_so')->insertGetId([
                    'so_code' => $header->code,
                    'code' => $header->code,
                    'so_date' => $header->date,
                    'brand_name' => $brandName,
                    'origin_warehouse_id' => 2,
                    'customer_id' => $matchedAddress->customer_id,
                    'customer_other_address_id' => $matchedAddress->id,
                    'type_transaction' => $header->type_transaction,
                    'rekening' => $header->bank ?? null,
                    'type_so' => 'nonppn',
                    'idr_rate' => $header->idr_rate,
                    'status' => 4,
                    'note' => 'Migrasi data',
                    'created_at' => $header->created_at,
                ]);

                // Proses item
                $items = DB::table('migrasi_so_list')->where('so_id', $header->id)->get();
                $jumlahItemBerhasil = 0;

                foreach ($items as $item) {
                    // 1. Temukan packaging_id berdasarkan nama packaging
                    $packaging = DB::table('master_packaging')
                        ->whereRaw('LOWER(pack_name) = ?', [strtolower($item->packaging)])
                        ->first();

                    if (!$packaging) {
                        DB::table('migrasi_so_gagal')->insert([
                            'code' => $header->code,
                            'keterangan' => "Packaging '{$item->packaging}' tidak ditemukan",
                            'created_at' => now(),
                        ]);
                        continue;
                    }

                    // 2. Temukan product_packaging_id
                    $product = DB::table('master_products_packaging')
                        ->where('code', $item->product_code)
                        ->first();

                    $fullCode = $item->product_code;

                    if (!$product) {
                        DB::table('migrasi_so_gagal')->insert([
                            'code' => $header->code,
                            'keterangan' => "Produk '{$fullCode}' tidak ditemukan di master_products_packaging",
                            'customer' => null,
                            'product' => $item->product_code . ' - ' . $item->product_name,
                            'created_at' => now(),
                        ]);
                        continue;
                    }

                    // 3. Penentuan product_packaging_id berdasarkan brand
                    $brand = strtolower($item->brand);

                    if ($brand === "Senses") {
                        $parts = explode(' ', trim($item->product_code));
                        $codeNumber = isset($parts[1]) ? $parts[1] : null;

                        if (!$codeNumber || !is_numeric($codeNumber)) {
                            DB::table('migrasi_so_gagal')->insert([
                                'code' => $header->code,
                                'keterangan' => "Gagal parsing kode produk '{$item->product_code}' untuk brand Senses",
                                'created_at' => now(),
                            ]);
                            continue;
                        }

                        $customId = $codeNumber . '-' . $packaging->id;

                        // Cari berdasarkan ID (contoh: 1234-4)
                        $productFromSenses = DB::table('master_products_packaging')
                            ->where('id', $customId)
                            ->first();

                        if (!$productFromSenses) {
                            DB::table('migrasi_so_gagal')->insert([
                                'code' => $header->code,
                                'keterangan' => "Produk Senses dengan ID '{$customId}' tidak ditemukan",
                                'created_at' => now(),
                            ]);
                            continue;
                        }

                        $id_product = $productFromSenses->id; // ini adalah '1234-4'
                    } else {
                        $id_product = $product->id;
                    }

                    // 3. Insert item ke penjualan_so_item
                    DB::table('penjualan_so_item')->insert([
                        'so_id' => $soId,
                        'product_packaging_id' => $id_product,
                        'packaging_id' => $product->packaging_id,
                        'qty' => $item->qty,
                        'disc_usd' => $item->item_disc_amount,
                        'free_product' => 0,
                        'price' => $item->item_price,
                        'status' => 1,
                        'qty_worked' => $item->qty,
                        'created_at' => $header->created_at,
                    ]);

                    $jumlahItemBerhasil++;
                }

                // Jika semua item gagal
                if ($jumlahItemBerhasil === 0) {
                    // Hapus SO kosong
                    // DB::table('penjualan_so')->where('id', $soId)->delete();

                    DB::table('migrasi_so_gagal')->insert([
                        'code' => $header->code,
                        'keterangan' => "Semua produk tidak ditemukan di master_products_packaging",
                    ]);
                    $jumlahGagal++;
                    continue;
                }

                $jumlahBerhasil++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Migrasi selesai: $jumlahBerhasil berhasil, $jumlahGagal gagal.");
        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat migrasi: ' . $e->getMessage());
        }
    }

    public function prosesKalkulasiDO(Request $request)
    {
        $soCodes = DB::table('penjualan_so')->pluck('code');
        $headers = DB::table('migrasi_so_header')->whereIn('code', $soCodes)->get();

        foreach ($headers as $header) {
            DB::beginTransaction();

            try {
                $so = DB::table('penjualan_so')->where('code', $header->code)->first();
                if (!$so) {
                    throw new \Exception("SO dengan kode {$header->code} tidak ditemukan di penjualan_so.");
                }

                // Buat header DO
                $doId = DB::table('penjualan_do')->insertGetId([
                    'code' => $header->code,
                    'do_code' => $header->code,
                    'so_id' => $so->id,
                    'warehouse_id' => $so->origin_warehouse_id,
                    'customer_id' => $so->customer_id,
                    'customer_other_address_id' => $so->customer_other_address_id,
                    'idr_rate' => $header->idr_rate,
                    'type_transaction' => $header->type_transaction,
                    'status' => 6,
                    'created_at' => $so->created_at,
                ]);

                // Buat detail DO
                DB::table('penjualan_do_details')->insert([
                    'do_id' => $doId,
                    'discount_1' => $header->disc_percent ?? 0.00,
                    'discount_2' => $header->disc_percent_2 ?? 0.00,
                    'discount_1_idr' => $header->disc_amount ?? 0.00,
                    'discount_2_idr' => $header->disc_amount_2 ?? 0.00,
                    'ppn_idr' => $header->ppn_idr ?? 0.00,
                    'ppn_percent' => $header->ppn ?? 0.00,
                    'purchase_total_idr' => $header->subtotal ?? 0.00,
                    'delivery_cost_idr' => $header->delivery_cost ?? 0.00,
                    'other_cost_idr' => $header->cost_resi ?? 0.00,
                    'grand_total_idr' => $header->grand_total ?? 0.00,
                    'created_at' => $so->created_at,
                ]);

                // Ambil data item
                $soItems = DB::table('penjualan_so_item')->where('so_id', $so->id)->get();
                $migrasiItems = DB::table('migrasi_so_list')->where('so_id', $header->id)->get();

                foreach ($soItems as $index => $soItem) {
                    $item = $migrasiItems[$index] ?? null;

                    if (!$item) {
                        throw new \Exception("Item migrasi tidak ditemukan untuk SO {$header->code}, index {$index}");
                    }

                    DB::table('penjualan_do_item')->insert([
                        'do_id' => $doId,
                        'product_packaging_id' => $soItem->product_packaging_id,
                        'so_item_id' => $soItem->id,
                        'packaging_id' => $soItem->packaging_id,
                        'qty' => $item->qty,
                        'price' => $item->item_price,
                        'usd_disc' => $item->item_disc_amount,
                        'total_disc' => $item->qty * $item->item_disc_amount,
                        'total' => ($item->item_price - $item->item_disc_amount) * $item->qty,
                        'created_at' => $so->created_at,
                    ]);
                }

                // Insert ke finance_invoicing
                DB::table('finance_invoicing')->insert([
                    'code' => $header->code,
                    'do_id' => $doId,
                    'customer_id' => $so->customer_id,
                    'customer_other_address_id' => $so->customer_other_address_id,
                    'grand_total_idr' => $header->grand_total ?? 0.00,
                    'status' => 1,
                    'created_at' => $so->created_at,
                ]);

                DB::commit();
                Log::info("Berhasil memproses DO untuk SO {$header->code}");
            } catch (\Throwable $e) {
                dd($e);
                DB::rollBack();
                Log::error("Gagal memproses SO {$header->code}: " . $e->getMessage());
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Proses kalkulasi DO selesai (periksa log untuk error per SO jika ada)',
        ]);
    }
}