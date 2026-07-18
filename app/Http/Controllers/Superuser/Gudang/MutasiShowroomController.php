<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Gudang\MutasiShowroom;
use App\Entities\Gudang\MutasiShowroomDetail;
use App\Entities\Gudang\MutasiShowroomHistory;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Vendor;
use App\Entities\Master\Product;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Services\BrandPriceCalculator;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use App\Services\MutasiShowroomFreeSOService;
use PDF;
use Validator;
use Carbon\Carbon;
use Auth;
use DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class MutasiShowroomController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.mutasi_showroom.";
        $this->route = "superuser.gudang.mutasi_showroom";
        $this->user_menu = new UserMenu;
        $this->access = null;
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $access = $this->user_menu;
            $access = $access->where('user_id',$user->id)
                             ->whereHas('menu',function($query2){
                                $query2->where('route_name',$this->route);
                             })
                             ->first();
            $this->access = $access;
            return $next($request);
        });
    }

    private function isFinance()
    {
        return in_array(Auth::id(), [31, 36, 1]);
    }

    private function denyPrintAccess()
    {
        if (request()->ajax()) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk print invoice'
            ], 403);
        }

        return redirect()
            ->back()
            ->with('error', 'Anda tidak memiliki hak akses untuk print invoice');
    }

    public function index()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view($this->view."index");
    }

    public function listPartial(Request $request)
    {
        $query = MutasiShowroom::withCount('details')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        // ================= DEFAULT RANGE =================
        // Awal bulan berjalan s/d hari ini
        $startDate = $request->input(
            'start_date',
            now()->startOfMonth()->format('Y-m-d')
        );

        $endDate = $request->input(
            'end_date',
            now()->format('Y-m-d')
        );

        $query->whereBetween('tanggal', [$startDate, $endDate]);

        // ================= FILTER STATUS =================
        // if ($request->filled('status')) {

        //     if ($request->status === 'process') {
        //         $query->whereIn('status', [
        //             MutasiShowroom::STATUS['ACTIVE'],
        //             MutasiShowroom::STATUS['PUBLISH'],
        //             MutasiShowroom::STATUS['SENT'],
        //         ]);
        //     }

        //     if ($request->status === 'settle') {
        //         $query->whereIn('status', [
        //             MutasiShowroom::STATUS['SETTEL'],
        //         ]);
        //     }
        // }

        $brands = DB::table('master_brand_lokal')
            ->whereIn('brand_name', ['GCF', 'Senses', 'PPI FF', 'PPI NON FF'])
            ->orderBy('brand_name')
            ->get();

        $types  = MutasiShowroom::TYPE;
        
        $customer = CustomerOtherAddress::with('store')
            ->whereHas('store', function($query) {
                $query->where('status', 1);
            })
            ->orderBy('name')
            ->get();

        $data = [
            'mutasi_showrooms' => $query->paginate(10)->appends($request->all()),
            'rangeStart'       => $startDate,
            'rangeEnd'         => $endDate,
            'statusSelected'   => $request->status,
            'brands'           => $brands,
            'types'           => $types,
            'customerAddresses' => $customer
        ];

        return view($this->view . 'partials._listPartial', $data);
    }

    public function createPartial(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['gudangs']   = Warehouse::orderBy('name')->get();
        $data['vendors']   = Vendor::orderBy('name')->get();
        $data['types']    = MutasiShowroom::TYPE;
        $data['brands'] = DB::table('master_brand_lokal')
            ->whereIn('brand_name', ['GCF', 'Senses', 'PPI FF', 'PPI NON FF'])
            ->orderBy('brand_name')
            ->get();


        return view($this->view."partials._createPartial", $data);
    }

    public function createMode1()
    {
        return view('superuser.gudang.mutasi_showroom.partials.create_mode1', [
            'kode'   => CodeRepo::generateMutasiShowroom(),
            'types'  => MutasiShowroom::TYPE,
            'brands' => DB::table('master_brand_lokal')
                ->whereIn('brand_name', ['GCF', 'Senses', 'PPI FF', 'PPI NON FF'])
                ->orderBy('brand_name')
                ->get(),
        ]);
    }

    public function createMode2()
    {
        return view('superuser.gudang.mutasi_showroom.partials.create_mode2', [
            'kode'   => CodeRepo::generateMutasiShowroom(),
            'types'  => MutasiShowroom::TYPE,
            'brands' => DB::table('master_brand_lokal')
                ->whereIn('brand_name', ['GCF', 'Senses', 'PPI FF', 'PPI NON FF'])
                ->orderBy('brand_name')
                ->get(),
        ]);
    }

    public function showPartial($id)
    {
        $mutasi = MutasiShowroom::with([
            'details.product_packaging' // relasi item mutasi
        ])->findOrFail($id);

        return view('superuser.gudang.mutasi_showroom.partials.viewer', compact('mutasi'));
    }


    public function get_product_pack(Request $request)
    {
        if ($request->ajax()) {

            $keyword   = trim($request->id);
            $brandName = $request->brand_name;

            $product = Product::where('master_products.on_order', 1)
                ->leftJoin('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
                ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                ->leftJoin('master_product_types', 'master_products_packaging.type_id', '=', 'master_product_types.id')
                ->leftJoin('master_warehouses', 'master_products_packaging.warehouse_id', '=', 'master_warehouses.id')
                ->where('master_products.status', 1)

                // Ã¢Å“â€¦ filter brand hanya jika dikirim
                ->when($brandName, function ($q) use ($brandName) {
                    $q->where('master_products.brand_name', $brandName);
                })

                ->where(function ($q) use ($keyword) {
                    $q->where('master_products_packaging.code', 'LIKE', "%{$keyword}%")
                    ->orWhere('master_products_packaging.name', 'LIKE', "%{$keyword}%");
                })

                ->select(
                    'master_products_packaging.id as id',
                    'master_products_packaging.code as ProductCode',
                    'master_products_packaging.name as productName',
                    'master_products_packaging.price as productPrice',
                    'master_packaging.id as productPackagingID',
                    'master_packaging.pack_name as productPackaging',
                    'master_warehouses.name as warehouseName',
                    'master_product_types.name as typeName'
                )
                ->get();

            return response()->json([
                'results' => $product->map(function ($key) {
                    return [
                        'id'   => $key->id,
                        'text' => $key->ProductCode.' - '.$key->productName.' ('.$key->productPackaging.')',
                        'packName' => $key->productPackaging
                    ];
                })
            ]);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
    
            Log::info('[MUTASI] Incoming request', $request->all());
    
            // =========================
            // VALIDASI
            // =========================
            try {
                $request->validate([
                    'type'                  => 'required|in:1,2,3,4',
                    'brand_name'            => 'required|string',
                    'gudang_id'             => 'required|exists:master_warehouses,id',
                    'vendor_id'             => 'required|exists:master_vendors,id',
                    'items'                 => 'required|array|min:1',
                    'items.*.product_id'    => 'required|exists:master_products_packaging,id',
                    'items.*.qty'           => 'required|numeric|min:0.01',
                ]);
    
                if (MutasiShowroom::isEksternal($request->type)) {
                    $request->validate([
                        'customer_id' => 'required|exists:master_customer_other_addresses,id'
                    ]);
                }
    
            } catch (ValidationException $ve) {
    
                Log::error('[MUTASI][VALIDATION ERROR]', [
                    'errors' => $ve->errors(),
                    'input'  => $request->all(),
                ]);
    
                throw $ve; // lempar ulang agar response tetap 422
            }
    
            // =========================
            // CREATE HEADER
            // =========================
            Log::info('[MUTASI] Generate kode start');
    
            $kode = CodeRepo::generateMutasiShowroom($request->type);
    
            Log::info('[MUTASI] Kode generated', ['kode' => $kode]);
    
            $mutasi = MutasiShowroom::create([
                'kode'                      => $kode,
                'brand_name'                => $request->brand_name,
                'type'                      => $request->type,
                'warehouse_from_id'         => $request->gudang_id,
                'warehouse_to_id'           => $request->vendor_id,
                'customer_other_address_id' => $request->customer_id ?? null,
                'so_id'                     => null,
                'tanggal'                   => now(),
                'status'                    => MutasiShowroom::STATUS['ACTIVE'],
                'created_by'                => auth()->id(),
            ]);
    
            Log::info('[MUTASI] Header created', ['id' => $mutasi->id]);
    
            // =========================
            // CREATE DETAIL
            // =========================
            foreach ($request->items as $i => $item) {
    
                Log::info("[MUTASI] Insert detail #{$i}", $item);
    
                MutasiShowroomDetail::create([
                    'penjualan_showroom_id' => $mutasi->id,
                    'product_packaging_id'  => $item['product_id'],
                    'qty'                   => $item['qty'],
                    'price'                 => 0,
                    'total_price'           => 0,
                    'note'                  => $item['note'] ?? null,
                ]);
            }
    
            DB::commit();
    
            Log::info('[MUTASI] Commit success', ['kode' => $kode]);
    
            return response()->json([
                'status'  => 'success',
                'message' => 'Mutasi Showroom berhasil disimpan',
                'data'    => $mutasi
            ]);
    
        } catch (\Throwable $e) {
    
            DB::rollBack();
    
            Log::critical('[MUTASI][ERROR]', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'input'   => $request->all(),
            ]);
    
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan mutasi showroom',
                'debug'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function print_pdf(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $mutasi = MutasiShowroom::with('details.product_packaging.packaging')
            ->where('id', $id)
            ->firstOrFail();

        // BATAS PRINT (kecuali developer)
        if ($mutasi->print_count >= 2 && auth()->id() != 1) {
            abort(403, 'Dokumen ini sudah dicetak maksimal 2 kali.');
        }

        // UPDATE COUNTER PRINT
        $mutasi->increment('print_count');

        if (empty($mutasi->printed_at)) {
            $mutasi->updated_by = auth()->id();
            $mutasi->printed_at = now();
            $mutasi->save();
        }

        return PDF::loadView(
            'superuser.gudang.mutasi_showroom.print_pdf',
            compact('mutasi')
        )
        ->setPaper('A5', 'landscape')
        ->stream('mutasi-showroom-' . $mutasi->kode . '.pdf');
    }

    public function publish(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $mutasi = MutasiShowroom::findOrFail($id);

        // Validasi status
        if ($mutasi->status != MutasiShowroom::STATUS['ACTIVE']) {
            return response()->json([
                'status'  => false,
                'message' => 'Status tidak valid untuk publish'
            ], 422);
        }

        // Wajib sudah print
        // if (empty($mutasi->printed_at) && ($mutasi->print_count ?? 0) < 1) {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'Dokumen wajib diprint sebelum publish'
        //     ], 422);
        // }

        // Update status
        $mutasi->update([
            'updated_by'    => Auth::id(),
            'status'        => MutasiShowroom::STATUS['PUBLISH'],
            'update_at'     => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Mutasi berhasil dipublish'
        ]);
    }

    public function sent(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $mutasi = MutasiShowroom::findOrFail($id);

        // validasi status
        if ($mutasi->status != MutasiShowroom::STATUS['PUBLISH']) {
            return response()->json([
                'message' => 'Mutasi harus berstatus PUBLISH'
            ], 422);
        }

        // contoh validasi role (silakan sesuaikan)
        $userId = auth()->id();
        $isDeveloper    = $userId == 1;
        $isFinanceStaff = $userId == 31 || $userId == 36;

        if (!$isDeveloper && !$isFinanceStaff) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak untuk mengirim mutasi'
            ], 403);
        }

        $mutasi->update([
            'status'   => MutasiShowroom::STATUS['SENT'],
            'updated_at'  => now(),
            'updated_by'  => Auth::id(),
        ]);

        // POTONG STOK DI GUDANG ASAL
        foreach ($mutasi->details as $detail) {
            $stockAraya = ProductMinStock::where('warehouse_id', $mutasi->warehouse_from_id)
                ->where('product_packaging_id', $detail->product_packaging_id)
                ->first();

            $stockAraya->quantity -= $detail->qty;
            $stockAraya->save();

            StockMove::create([
                'code_transaction'     => $mutasi->kode,
                'warehouse_id'         => $mutasi->warehouse_from_id,
                'product_packaging_id' => $detail->product_packaging_id,
                'stock_in'             => 0,
                'stock_out'            => $detail->qty,
                'stock_balance'        => $stockAraya->quantity,
                'note'                 => 'Mutasi Showroom: ' . $mutasi->kode,
                'created_by'           => Auth::id(),
            ]);
        }

        return response()->json([
            'message' => 'Mutasi berhasil dikirim'
        ]);
    }

    // public function updateListPartial(Request $request)
    // {
    //     $items = MutasiShowroomDetail::whereHas('mutasi_showroom', function ($q) use ($request) {

    //             $q->where('status', MutasiShowroom::STATUS['PUBLISH'])
    //             ->where('type', '!=', 3);

    //             if ($request->filled('start_date') && $request->filled('end_date')) {
    //                 $q->whereBetween('tanggal', [
    //                     $request->start_date,
    //                     $request->end_date
    //                 ]);
    //             }
    //         })
    //         ->where(function ($q) {
    //             $q->where('price_usd', 0)
    //             ->orWhere('total_price', 0);
    //         })
    //         ->with([
    //             'product_packaging.product:id,code,name',
    //             'mutasi_showroom:id,kode,tanggal,brand_name,status',
    //         ])
    //         ->get();

    //     /**
    //      * GROUPING
    //      * BRAND -> KODE MUTASI
    //      */
    //     $data['groups'] = $items
    //         ->groupBy(function ($item) {
    //             return $item->mutasi_showroom->brand_name;
    //         })
    //         ->map(function ($brandGroup) {
    //             return $brandGroup->groupBy(function ($item) {
    //                 return $item->mutasi_showroom->kode;
    //             });
    //         });

    //     return view($this->view.'partials.update_list_partial', $data);
    // }

    public function updateListPartial(Request $request)
    {
        $items = MutasiShowroomDetail::whereHas('mutasi_showroom', function ($q) use ($request) {
                
                $q->where(function ($sub) {
                    // Panggil yang PUBLISH (Biasa)
                    $sub->where(function ($q1) {
                        $q1->where('status', \App\Entities\Gudang\MutasiShowroom::STATUS['PUBLISH'])
                           ->where('type', '!=', 3);
                    })
                    // ATAU Panggil yang SETTLE tapi khusus tipe Promosi (Tipe 5)
                    ->orWhere(function ($q2) {
                        $q2->where('status', \App\Entities\Gudang\MutasiShowroom::STATUS['SETTLE'])
                           ->where('type', \App\Entities\Gudang\MutasiShowroom::TYPE_SYSTEM_FREE_SO);
                    });
                });

                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $q->whereBetween('tanggal', [
                        $request->start_date,
                        $request->end_date
                    ]);
                }
            })
            ->where(function ($q) {
                $q->where('price_usd', 0)
                  ->orWhere('total_price', 0)
                  ->orWhereHas('mutasi_showroom', function ($sq) {
                      $sq->where('type', \App\Entities\Gudang\MutasiShowroom::TYPE_SYSTEM_FREE_SO);
                  });
            })
            ->with([
                'product_packaging.product:id,code,name',
                'mutasi_showroom:id,kode,tanggal,brand_name,status,type',
            ])
            ->get();

        $data['groups'] = $items
            ->groupBy(function ($item) {
                return $item->mutasi_showroom->brand_name;
            })
            ->map(function ($brandGroup) {
                return $brandGroup->groupBy(function ($item) {
                    return $item->mutasi_showroom->kode;
                });
            });

        return view($this->view.'partials.update_list_partial', $data);
    }

    public function settlePrices(Request $request)
    {
        if (!$this->isFinance()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $items = $request->input('items', []);
    
        if (empty($items)) {
            return response()->json(['message' => 'Data kosong'], 422);
        }
    
        try {
            DB::transaction(function () use ($items) {
                $processedKode = [];
    
                foreach ($items as $kode => $details) {
                    foreach ($details as $detailId => $data) {
    
                        $detail = MutasiShowroomDetail::with('mutasi_showroom')
                            ->where('id', $detailId)
                            ->lockForUpdate()
                            ->first();
    
                        if (!$detail) continue;
    
                        $mutasi = $detail->mutasi_showroom;
                        $plUsd  = (float) ($data['pl_usd'] ?? 0);
                        $kurs   = (float) ($data['kurs']   ?? 0);
    
                        if ($plUsd <= 0 || $kurs <= 0) continue;
    
                        // set kurs sekali per mutasi
                        if ((float) $mutasi->kurs === 0.0) {
                            $mutasi->update(['kurs' => $kurs]);
                        }
    
                        // hitung netto USD via service
                        $priceUsd = BrandPriceCalculator::calculateUsd(
                            $mutasi->brand_name,
                            $plUsd
                        );
    
                        $priceIdr = $priceUsd * $mutasi->kurs;
    
                        $detail->update([
                            'price_usd'   => $priceUsd,
                            'price_idr'   => $priceIdr,
                            'total_price' => $priceIdr * $detail->qty,
                        ]);
    
                        $processedKode[] = $mutasi->kode;
                    }
                }
    
                if (!empty($processedKode)) {
                    $kodeUnik = array_values(array_unique($processedKode));
    
                    MutasiShowroomHistory::create([
                        'tanggal'      => now(),
                        'kode_mutasi'  => implode(',', $kodeUnik),
                        'total_mutasi' => count($kodeUnik),
                        'status'       => 1,
                        'printed_at'   => null,
                        'printed_by'   => null,
                    ]);
    
                    MutasiShowroom::whereIn('kode', $kodeUnik)
                        ->update(['status' => MutasiShowroom::STATUS['SETTLE']]);
                }
            });
    
            return response()->json(['success' => true, 'message' => 'Settle berhasil']);
    
        } catch (\Throwable $e) {
            \Log::error('Settle Mutasi Gagal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat settle'
            ], 500);
        }
    }

    // public function updatePrice(Request $request)
    // {
    //     // HARD GUARD (tidak bisa dibypass)
    //     if (!$this->isFinance()) {
    //         return response()->json([
    //             'message' => 'Anda tidak memiliki hak akses untuk update harga'
    //         ], 403);
    //     }

    //     if (Auth::user()->is_superuser == 0) {
    //         if (
    //             empty($this->access) ||
    //             empty($this->access->user) ||
    //             $this->access->can_update == 0
    //         ) {
    //             return response()->json([
    //                 'message' => 'Anda tidak punya akses update'
    //             ], 403);
    //         }
    //     }

    //     $request->validate([
    //         'product_packaging_id' => 'required|exists:master_products_packaging,id',
    //         'price_pl_usd'         => 'required|numeric|min:0.01',
    //         'kurs'                 => 'required|numeric|min:1',
    //     ]);

    //     DB::transaction(function () use ($request) {

    //         $details = MutasiShowroomDetail::where('product_packaging_id', $request->product_packaging_id)
    //             ->where('price_usd', 0)
    //             ->whereHas('mutasi_showroom', function ($q) {
    //                 $q->where('status', MutasiShowroom::STATUS['SENT'])
    //                 ->where('kurs', 0);
    //             })
    //             ->with('mutasi_showroom')
    //             ->get();

    //         $mutasiIds = [];

    //         foreach ($details as $detail) {

    //             $mutasi = $detail->mutasi_showroom;

    //             if ($mutasi->kurs == 0) {
    //                 $mutasi->update(['kurs' => $request->kurs]);
    //             }

    //             $priceUsd = BrandPriceCalculator::calculateUsd(
    //                 $mutasi->brand_name,
    //                 $request->price_pl_usd
    //             );

    //             $priceIdr = $priceUsd * $mutasi->kurs;

    //             $detail->update([
    //                 'price_usd'   => $priceUsd,
    //                 'price_idr'   => $priceIdr,
    //                 'total_price' => $detail->qty * $priceIdr,
    //             ]);

    //             $mutasiIds[] = $mutasi->id;
    //         }

    //         foreach (array_unique($mutasiIds) as $mutasiId) {

    //             $stillZero = MutasiShowroomDetail::where('penjualan_showroom_id', $mutasiId)
    //                 ->where('total_price', 0)
    //                 ->exists();

    //             if (!$stillZero) {
    //                 MutasiShowroom::where('id', $mutasiId)
    //                     ->update(['status' => MutasiShowroom::STATUS['ACC']]);
    //             }
    //         }
    //     });

    //     return response()->json([
    //         'message' => 'Harga USD, kurs, dan total berhasil diperbarui'
    //     ]);
    // }

    public function doneIndex()
    {
        return view($this->view . 'partials.done');
    }

    private function renderAction($row)
    {
        $user = auth()->user();

        $isFinance = in_array($user->id, [31, 36, 1]);
        $canPrint  = $user->is_superuser == 1
            || (!empty($this->access) && $this->access->can_print == 1);

        if (!$isFinance || !$canPrint) {
            return '
                <span class="badge rounded-pill bg-light text-dark border"
                    style="font-weight:bold;font-size:12px;">
                    PUBLISH
                </span>
            ';
        }

        return '
            <button type="button"
                    class="btn btn-sm btn-outline-primary btnPrintInvoice"
                    data-id="'.$row->id.'">
                Print
            </button>
        ';
    }

    // public function doneData(Request $request)
    // {
    //     $query = MutasiShowroom::where('status', MutasiShowroom::STATUS['SETTLE'])
    //         ->orderBy('tanggal', 'desc');

    //     if ($request->filled('start_date') && $request->filled('end_date')) {
    //         $query->whereBetween('tanggal', [
    //             $request->start_date,
    //             $request->end_date
    //         ]);
    //     }

    //     $data = $query->paginate(10);

    //     $rows = [];
    //     foreach ($data as $index => $row) {
    //         $rows[] = [
    //             'id'           => $row->id,
    //             'no'           => $data->firstItem() + $index,
    //             'kode'         => $row->kode,
    //             'brand'        => $row->brand_name,
    //             'tanggal'      => \Carbon\Carbon::parse($row->tanggal)->format('d M Y'),
    //             'total_mutasi' => $row->total_mutasi,
    //             'type'         => $row->type() ?? '-', // Pastikan field ini ada di model
    //             'status'       => $row->status() ?? '-',
    //             'action'       => $this->renderAction($row),
    //         ];
    //     }

    //     return response()->json([
    //         'data'       => $rows,
    //         'current_page' => $data->currentPage(),
    //         'last_page'    => $data->lastPage(),
    //         'from'       => $data->firstItem(),
    //         'to'         => $data->lastItem(),
    //         'total'      => $data->total(),
    //         // Kita tidak mengirim (string) $data->links() karena akan menggunakan manual pagination JS
    //     ]);
    // }

    public function doneData(Request $request)
    {
        $query = MutasiShowroomHistory::where('status', 1)
            ->orderBy('tanggal', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $data = $query->paginate(10);

        $rows = [];
        foreach ($data as $index => $row) {

            $rows[] = [
                'id' => $row->id,
                'no' => $data->firstItem() + $index,
                'kode' => $row->kode,
                'brand' => $row->brand_name,
                'tanggal' => \Carbon\Carbon::parse($row->tanggal)->format('d M Y'),
                'total_mutasi' => $row->total_mutasi,
                'action' => $this->renderAction($row),
            ];
        }

        return response()->json([
            'data' => $rows,
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
            'total' => $data->total(),
            'current_page' => $data->currentPage(), // Menambah baris ini
            'last_page' => $data->lastPage(),       // Menambah baris ini
            'pagination' => (string) $data->links()
        ]);
    }

    public function printInvoice($id)
    {
        // ðŸ”’ FINANCE GUARD
        if (!$this->isFinance()) {
            return $this->denyPrintAccess();
        }

        if (
            Auth::user()->is_superuser == 0 &&
            !$this->isFinance() &&
            (empty($this->access) || $this->access->can_print == 0)
        ) {
            return $this->denyPrintAccess();
        }

        // ================= AMBIL HISTORY =================
        $history = MutasiShowroomHistory::findOrFail($id);

        // ================= PARSE KODE MUTASI =================
        $kodeMutasiList = array_filter(array_map(
            'trim',
            explode(',', $history->kode_mutasi)
        ));

        // ================= AMBIL DATA MUTASI =================
        $mutasiList = MutasiShowroom::with([
                'details.product_packaging.product',
                'details.product_packaging.packaging',
                'warehouse_from'
            ])
            ->whereIn('kode', $kodeMutasiList)
            ->orderBy('tanggal')
            ->get();

        if ($mutasiList->isEmpty()) {
            abort(404, 'Data mutasi tidak ditemukan');
        }

        // ================= GENERATE PDF =================
        $pdf = PDF::loadView(
            $this->view . 'invoice_pdf_list',
            [
                'mutasiList' => $mutasiList,
                'history'    => $history,
            ]
        )->setPaper('a4', 'portrait');

        return $pdf->stream(
            'Invoice-Mutasi-' . $history->tanggal . '.pdf'
        );
    }

    public function getItemsByKode(string $kode)
    {
        $mutasi = MutasiShowroom::where('kode', $kode)
            ->with([
                'details.product_packaging.product:id,code,name'
            ])
            ->first();

        if (!$mutasi) {
            return response()->json([
                'message' => 'Data mutasi tidak ditemukan'
            ], 404);
        }

        $items = $mutasi->details->map(function ($detail) {
            return [
                'id'    => $detail->id,
                'code'  => $detail->product_packaging->code ?? '-',
                'name'  => $detail->product_packaging->name ?? '-',
                'pl_usd'  => $detail->product_packaging->price ?? '-',
                'qty'   => $detail->qty,

                // opsional (jika nanti mau auto-fill)
                'price_usd' => $detail->price_usd,
                'price_idr' => $detail->price_idr,
            ];
        });

        return response()->json([
            'kode'  => $mutasi->kode,
            'brand' => $mutasi->brand_name,
            'kurs'  => $mutasi->kurs,
            'items' => $items,
        ]);
    }

    public function detail(Request $request)
    {
        $mutasi = MutasiShowroom::with(['details.product_packaging.product', 'details.product_packaging.packaging', 'customer_other_address'])
            ->where('status', MutasiShowroom::STATUS['SETTLE'])
            ->orderBy('tanggal', 'desc')
            ->get();

        $dataList = $mutasi->map(function ($m) {
        return [
            'id'       => $m->id,
            'kode'     => $m->kode,
            'tanggal'  => $m->tanggal->format('d M Y'),
            'type'     => $m->type(),
            'brand'    => $m->brand_name,
            'customer' => $m->customer_other_address->name ?? '-',
            'details'  => $m->details->map(function($d) {
                return [
                    'product_name'   => $d->product_packaging->product->name,
                    'packaging_name' => $d->product_packaging->packaging->name,
                    'qty'            => $d->qty
                ];
            })->toArray(), // jangan lupa ->toArray() untuk kompatibilitas JSON
        ];
    })->toArray();

        return response()->json(['dataList' => $dataList]);
    }

    // Tambahkan printRequest mirip printInvoice
    public function printRequest($id)
    {
        $mutasi = MutasiShowroom::with(['details.product_packaging.product', 'details.product_packaging.packaging', 'customer'])->findOrFail($id);

        $pdf = PDF::loadView('superuser.gudang.mutasi_showroom.request_pdf', ['mutasi' => $mutasi])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Request-Mutasi-' . $mutasi->tanggal->format('Ymd') . '.pdf');
    }

    public function generateFreeSO()
    {
        $service = new MutasiShowroomFreeSOService();

        $created = $service->generate(
            '2026-01-01',
            '2026-02-28'
        );

        return response()->json([
            'status' => true,
            'message' => $created . ' mutasi berhasil dibuat'
        ]);
    }
}