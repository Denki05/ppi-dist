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
                try {
                    // ✅ Cek apakah SO sudah pernah dimigrasi
                    $existingSO = DB::table('penjualan_so')->where('code', $header->code)->first();
                    if ($existingSO) {
                        $hasItems = DB::table('penjualan_so_item')->where('so_id', $existingSO->id)->exists();
                        if ($hasItems) {
                            // Sudah lengkap → abaikan
                            continue;
                        } else {
                            // SO ada tapi item kosong → hapus dan proses ulang
                            DB::table('penjualan_so')->where('id', $existingSO->id)->delete();
                        }
                    }
                    
                    $customerName = strtolower($header->customer);
                    $matchedAddress = null;


                    // 🔎 1. Validasi customer
                    foreach ($customerAddresses as $addr) {
                        $nameLower = strtolower($addr->name);
                        $kotaLower = strtolower($addr->text_kota);

                        if (strpos($customerName, $nameLower) !== false &&
                            strpos($customerName, $kotaLower) !== false) {
                            $matchedAddress = $addr;
                            break;
                        }
                    }

                    // ❌ Jika customer tidak ditemukan → skip seluruh nota
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

                    // 🔁 2. Validasi semua item dulu sebelum insert
                    $items = DB::table('migrasi_so_list')->where('so_id', $header->id)->get();
                    $itemsToInsert = [];
                    $isValid = true;

                    foreach ($items as $item) {
                        // Cek packaging
                        $packaging = DB::table('master_packaging')
                            ->whereRaw('LOWER(pack_name) = ?', [strtolower($item->packaging)])
                            ->first();

                        // dd(strtolower($item->packaging));

                        if (!$packaging) {
                            DB::table('migrasi_so_gagal')->insert([
                                'code' => $header->code,
                                'keterangan' => "Packaging '{$item->packaging}' tidak ditemukan",
                                'product' => $item->product_code . ' - ' . $item->product_name,
                                'created_at' => now(),
                            ]);
                            $isValid = false;
                            break;
                        }

                        // Cek produk utama
                        $product = DB::table('master_products_packaging')
                            ->where('code', $item->product_code)
                            ->first();

                        // dd($product);

                        if (!$product) {
                            DB::table('migrasi_so_gagal')->insert([
                                'code' => $header->code,
                                'keterangan' => "Produk '{$item->product_code}' tidak ditemukan di master_products_packaging",
                                'product' => $item->product_code . ' - ' . $item->product_name,
                                'created_at' => now(),
                            ]);
                            $isValid = false;
                            break;
                        }

                        // Khusus Senses → parsing ID
                        $id_product = $product->id;
                        if (strtolower($item->brand) === "Senses") {
                            $parts = explode(' ', trim($item->product_code));
                            $codeNumber = $parts[1] ?? null;

                            if (!$codeNumber || !is_numeric($codeNumber)) {
                                DB::table('migrasi_so_gagal')->insert([
                                    'code' => $header->code,
                                    'keterangan' => "Gagal parsing kode produk '{$item->product_code}' untuk brand Senses",
                                    'product' => $item->product_code . ' - ' . $item->product_name,
                                    'created_at' => now(),
                                ]);
                                $isValid = false;
                                break;
                            }

                            $customId = $codeNumber . '-' . $packaging->id;
                            $productFromSenses = DB::table('master_products_packaging')
                                ->where('id', $customId)
                                ->first();
                            
                            if (!$productFromSenses) {
                                DB::table('migrasi_so_gagal')->insert([
                                    'code' => $header->code,
                                    'keterangan' => "Produk Senses dengan ID '{$customId}' tidak ditemukan",
                                    'product' => $item->product_code . ' - ' . $item->product_name,
                                    'created_at' => now(),
                                ]);
                                $isValid = false;
                                break;
                            }

                            $id_product = $productFromSenses->id;
                        }

                        // dd($item);

                        $itemsToInsert[] = [
                            'product_packaging_id' => $id_product,
                            'packaging_id' => $product->packaging_id,
                            'qty' => $item->qty,
                            'disc_usd' => $item->item_disc_amount,
                            'free_product' => 0,
                            'price' => $item->item_price,
                            'status' => 1,
                            'qty_worked' => $item->qty,
                            'created_at' => $header->created_at,
                        ];
                    }

                    // ❌ Jika ada satu saja error → skip nota
                    if (!$isValid || count($itemsToInsert) === 0) {
                        $jumlahGagal++;
                        continue;
                    }

                    // ✅ Insert SO
                    $brand = $header->brand;
                    $brandName = $brand == 247 ? "Senses" : ($brand == 287 ? "GCF" : "PPI");

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

                    // ✅ Insert items
                    foreach ($itemsToInsert as &$item) {
                        $item['so_id'] = $soId;
                    }
                    DB::table('penjualan_so_item')->insert($itemsToInsert);
                    $jumlahBerhasil++;
                } catch (\Throwable $e) {
                    DB::rollBack();
                    DB::beginTransaction();
                    DB::table('migrasi_so_gagal')->insert([
                        'code' => $header->code ?? null,
                        'keterangan' => 'Error pada proses migrasi SO: ' . $e->getMessage(),
                        'customer' => $header->customer ?? null,
                        'product' => null,
                        'created_at' => now(),
                    ]);
                    $jumlahGagal++;
                    continue;
                }
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
                    'created_at' => $header->created_at,
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
                    'created_at' => $header->created_at,
                ]);

                // Ambil data item
                $soItems = DB::table('penjualan_so_item')->where('so_id', $so->id)->get();
                $migrasiItems = DB::table('migrasi_so_list')->where('so_id', $header->id)->get();

                foreach ($soItems as $index => $soItem) {
                    $item = $migrasiItems[$index] ?? null;

                    // dd($item);

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
                        'created_at' => $header->created_at,
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
                    'created_at' => $header->created_at,
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