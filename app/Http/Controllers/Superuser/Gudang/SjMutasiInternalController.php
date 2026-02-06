<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Gudang\MutasiShowroom;
use App\Entities\Gudang\StockMove;
use App\Repositories\CodeRepo;
use App\Entities\Setting\UserMenu;
use PDF;
use Validator;
use Carbon\Carbon;
use Auth;
use DB;

class SjMutasiInternalController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.sj_mutasi_internal.";
        $this->route = "superuser.gudang.sj_mutasi_internal";
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

    public function index(Request $request)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()
                    ->route('superuser.index')
                    ->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // TAB AKTIF (PENDING)
        $data['mutasiAktif'] = MutasiShowroom::where('status', 2)
            ->where('status_barang', 0)
            ->orderBy('tanggal', 'desc')
            ->paginate(10, ['*'], 'aktif');
        
        // TAB BELUM DIAMBIL
        $data['mutasiBelumDiambil'] = MutasiShowroom::where('status', 2)
            ->where('status_barang', 1)
            ->where('print_count', '>', 0)
            ->orderBy('tanggal', 'desc')
            ->paginate(10, ['*'], 'belum');
        
        // TAB SELESAI
        $data['mutasiSelesai'] = MutasiShowroom::where('status', 2)
            ->where('status_barang', 2)
            ->orderBy('tanggal', 'desc')
            ->paginate(10, ['*'], 'selesai');


        return view($this->view . "index", $data);
    }
    
    public function refreshTabs()
    {
        $aktif = MutasiShowroom::where('status', 2)
            ->where('status_barang', 0)
            ->count();
    
        $belum = MutasiShowroom::where('status', 2)
            ->where('status_barang', 1)
            ->count();
    
        $selesai = MutasiShowroom::where('status', 2)
            ->where('status_barang', 2)
            ->count();
    
        return response()->json([
            'aktif'   => $aktif,
            'belum'   => $belum,
            'selesai' => $selesai,
        ]);
    }


    public function show($id)
    {
        $mutasi = MutasiShowroom::with('details.product_packaging')
            ->findOrFail($id);

        return view(
            'superuser.gudang.sj_mutasi_internal.partials._detailWrapper',
            compact('mutasi')
        );
    }

    public function step1Save(Request $request)
    {
        DB::beginTransaction();
        try {
            $mutasi = MutasiShowroom::with('details')
                ->lockForUpdate()
                ->findOrFail($request->mutasi_id);

            // Validasi status
            if ($mutasi->status_checked == 0 && $mutasi->status_checked == 2) {
                throw new \Exception('Status tidak valid');
            }

            $checkedIds = $request->items ?? [];
            
            $totalItem = $mutasi->details->count();
            $totalChecked = count($checkedIds);

            if ($totalChecked !== $totalItem) {
                throw new \Exception(
                    'Checklist harus lengkap. Semua produk dalam mutasi wajib dicentang.'
                );
            }

            foreach ($mutasi->details as $detail) {

                if (in_array($detail->id, $checkedIds)) {

                    // Tandai checklist
                    $detail->is_checked = 1;
                    $detail->save();

                    // POTONG STOK (TANPA LOG)
                    DB::table('master_product_min_stocks')
                        ->where('product_packaging_id', $detail->product_packaging_id)
                        ->decrement('quantity', $detail->qty);
                }
            }

            // Update header
            $mutasi->status_checked = 1;
            $mutasi->updated_by = Auth::id();
            $mutasi->save();

            DB::commit();

            $mutasi->refresh();

            $html = view(
                'superuser.gudang.sj_mutasi_internal.partials._detailWrapper',
                compact('mutasi')
            )->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function step1Cancel(Request $request)
    {
        DB::beginTransaction();
        try {
            $mutasi = MutasiShowroom::with('details')
                ->lockForUpdate()
                ->findOrFail($request->mutasi_id);

            if ($mutasi->status_checked != 1) {
                throw new \Exception('Tidak bisa cancel');
            }

            foreach ($mutasi->details as $detail) {

                if ($detail->is_checked == 1) {

                    // BALIKKAN STOK
                    DB::table('master_product_min_stocks')
                        ->where('product_packaging_id', $detail->product_packaging_id)
                        ->increment('qty', $detail->qty);

                    $detail->is_checked = 0;
                    $detail->save();
                }
            }

            $mutasi->status_checked = 0;
            $mutasi->save();

            DB::commit();

            $mutasi->refresh();

            $html = view(
                'superuser.gudang.sj_mutasi_internal.partials._detailWrapper',
                compact('mutasi')
            )->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function step2Cancel(Request $request)
    {
        DB::beginTransaction();
        try {
            $mutasi = MutasiShowroom::with('details')
                ->lockForUpdate()
                ->findOrFail($request->mutasi_id);

            if ($mutasi->status_checked != 1) {
                throw new \Exception('Status tidak valid');
            }

            foreach ($mutasi->details as $detail) {
                if ($detail->is_checked) {

                    DB::table('master_product_min_stocks')
                        ->where('product_packaging_id', $detail->product_packaging_id)
                        ->increment('quantity', $detail->qty);

                    $detail->is_checked = 0;
                    $detail->save();
                }
            }

            $mutasi->status_checked = 2; // CANCELED
            $mutasi->print_count = 0;
            $mutasi->save();

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function step2Next(Request $request)
    {
        try {
    
            $mutasi = MutasiShowroom::findOrFail($request->mutasi_id);
    
            /**
             * VALIDASI 1
             * Surat Jalan wajib sudah dicetak
             */
            if ($mutasi->print_count == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Surat Jalan belum dicetak'
                ], 422);
            }
    
            /**
             * VALIDASI 2 (OPSIONAL TAPI AMAN)
             * Pastikan ada minimal 1 item yang dichecklist
             */
            $checkedCount = $mutasi->details()
                ->where('is_checked', 1)
                ->count();
    
            if ($checkedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada produk yang dichecklist'
                ], 422);
            }
    
            /**
             * VALIDASI 3 (OPSIONAL – JAGA FLOW)
             * Status mutasi masih aktif
             */
            if ($mutasi->status_barang == 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mutasi sudah selesai diproses'
                ], 422);
            }
    
            /**
             * LOLOS → BOLEH LANJUT STEP 3
             */
            return response()->json([
                'success' => true
            ]);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi tidak ditemukan'
            ], 404);
    
        }
    }


    public function step3Update(Request $request)
    {
        DB::beginTransaction();
        try {
            $mutasi = MutasiShowroom::with('details')
                ->lockForUpdate()
                ->findOrFail($request->mutasi_id);

            $oldStatus = $mutasi->status_barang;
            $newStatus = (int) $request->status_barang;

            // Validasi transisi
            if ($oldStatus == 3) {
                throw new \Exception('Mutasi sudah DIAMBIL');
            }

            if (in_array($oldStatus, [1]) && $newStatus != 2) {
                throw new \Exception('Status hanya bisa diubah ke DIAMBIL');
            }

            // Update status mutasi
            $mutasi->status_barang = $newStatus;
            $mutasi->save();

            // CATAT STOK MOVE HANYA JIKA FINAL DIAMBIL
            if ($newStatus === 2 && $oldStatus !== 2) {
                foreach ($mutasi->details as $detail) {
                    // Ambil balance terakhir
                    $lastStock = DB::table('gudang_move_stock')
                        ->where('product_packaging_id', $detail->product_packaging_id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $quantity_after_move = $lastStock ? $lastStock->stock_balance - $detail->qty : 0;

                    StockMove::create([
                        'code_transaction' => $mutasi->kode,
                        'warehouse_id' => $mutasi->warehouse_from_id,
                        'product_packaging_id' => $detail->product_packaging_id,
                        'stock_in' => 0,
                        'stock_out' => $detail->qty,
                        'stock_balance' => $quantity_after_move,
                        'note' => 'Mutasi Showroom - DIAMBIL',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'to_selesai' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}