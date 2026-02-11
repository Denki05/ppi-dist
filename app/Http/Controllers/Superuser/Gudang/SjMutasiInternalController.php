<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Gudang\MutasiShowroom;
use App\Entities\Gudang\MutasiOut;
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

    public function index()
    {
        return view($this->view . 'index');
    }

    public function table(Request $request)
    {
        $type = $request->type;

        if ($type === 'showroom') {

            $data['type'] = 'showroom';

            $data['mutasiAktif'] = MutasiShowroom::where('status', 2)
                ->where('status_barang', 0)
                ->orderBy('tanggal', 'desc')
                ->paginate(10);

            $data['mutasiBelumDiambil'] = MutasiShowroom::where('status', 2)
                ->where('status_barang', 1)
                ->where('print_count', '>', 0)
                ->orderBy('tanggal', 'desc')
                ->paginate(10);

            $data['mutasiSelesai'] = MutasiShowroom::where('status', 2)
                ->where('status_barang', 2)
                ->orderBy('tanggal', 'desc')
                ->paginate(10);

            return view('superuser.gudang.sj_mutasi_internal.partials.table_showroom', $data);
        }

        if ($type === 'gudang') {

            $data['type'] = 'gudang';

            $data['mutasiAktif'] = MutasiOut::where('status', MutasiOut::STATUS['PUBLISH'])
                ->where('status_barang', 0)
                ->orderBy('date', 'desc')
                ->paginate(10);

            $data['mutasiBelumDiambil'] = MutasiOut::whereIn('status', [
                    MutasiOut::STATUS['PUBLISH'],
                    MutasiOut::STATUS['ACC']
                ])
                ->where('status_barang', 1)
                ->orderBy('date', 'desc')
                ->paginate(10);


            $data['mutasiSelesai'] = MutasiOut::where('status', MutasiOut::STATUS['ACC'])
                ->orderBy('date', 'desc')
                ->paginate(10);

            return view('superuser.gudang.sj_mutasi_internal.partials.table_gudang', $data);
        }

        abort(404);
    }
    
    public function refreshTabs(Request $request)
    {
        $type = $request->type ?? 'showroom';
    
        if ($type === 'gudang') {
    
            $aktif = MutasiOut::where('status', MutasiOut::STATUS['PUBLISH'])
                ->where('status_barang', 0)
                ->count();
    
            $belum = MutasiOut::whereIn('status', [
                    MutasiOut::STATUS['PUBLISH'],
                    MutasiOut::STATUS['ACC']
                ])
                ->where('status_barang', 1)
                ->count();
    
            $selesai = MutasiOut::where('status', MutasiOut::STATUS['ACC'])
                ->where('status_barang', 2)
                ->count();
    
        } else {
    
            $aktif = MutasiShowroom::where('status', 2)
                ->where('status_barang', 0)
                ->count();
    
            $belum = MutasiShowroom::where('status', 2)
                ->where('status_barang', 1)
                ->count();
    
            $selesai = MutasiShowroom::where('status', 2)
                ->where('status_barang', 2)
                ->count();
        }
    
        return response()->json([
            'aktif'   => $aktif,
            'belum'   => $belum,
            'selesai' => $selesai,
        ]);
    }

    public function show(Request $request, $id)
    {
        $type = $request->type;
    
        // Jika type tidak dikirim, deteksi dari database
        if (!$type) {
            if (MutasiOut::where('id', $id)->exists()) {
                $type = 'gudang';
            } else {
                $type = 'showroom';
            }
        }
    
        if ($type === 'showroom') {
            $mutasi = MutasiShowroom::with('details.product_packaging')
                ->findOrFail($id);
        } else {
            $mutasi = MutasiOut::with('mutasiOutDetails.product_pack')
                ->findOrFail($id);
    
            $mutasi->details = $mutasi->mutasiOutDetails;
        }
    
        return view(
            'superuser.gudang.sj_mutasi_internal.partials._detailWrapper',
            compact('mutasi', 'type')
        );
    }

    public function step1Save(Request $request)
    {
        DB::beginTransaction();
        try {
            $type = $request->type ?? 'showroom'; // default showroom

            // dd($type);

            if ($type === 'showroom') {
                $mutasi = MutasiShowroom::with('details')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->details;
            } else { // gudang
                $mutasi = MutasiOut::with('mutasiOutDetails')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->mutasiOutDetails;
            }

            // Validasi status
            if ($mutasi->status_checked == 0 && $mutasi->status_checked == 2) {
                throw new \Exception('Status tidak valid');
            }

            $checkedIds = $request->items ?? [];

            $totalItem = $details->count();
            $totalChecked = count($checkedIds);

            if ($totalChecked !== $totalItem) {
                throw new \Exception(
                    'Checklist harus lengkap. Semua produk dalam mutasi wajib dicentang.'
                );
            }

            foreach ($details as $detail) {
                if (in_array($detail->id, $checkedIds)) {

                    // Tandai checklist
                    $detail->is_checked = 1;
                    $detail->save();

                    // POTONG STOK
                    $productId = $detail->product_packaging_id;

                    if ($type === 'showroom') {
                        $qty = (int) ($detail->qty ?? 0);
                    } else { // gudang
                        $qty = (int) ($detail->quantity ?? 0);
                    }

                    if ($qty > 0 && $productId) {
                        DB::table('master_product_min_stocks')
                            ->where('product_packaging_id', $productId)
                            ->decrement('quantity', $qty);
                    }
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
                compact('mutasi', 'type')
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
            
            $type = $request->type ?? 'showroom'; // default showroom

            if ($type === 'showroom') {
                $mutasi = MutasiShowroom::with('details')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->details;
            } else { // gudang
                $mutasi = MutasiOut::with('mutasiOutDetails')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->mutasiOutDetails;
            }

            if ($mutasi->status_checked != 1) {
                throw new \Exception('Tidak bisa cancel');
            }

            foreach ($details as $detail) {

                if ($detail->is_checked == 1) {

                    // BALIKKAN STOK
                    $quantity = $type === 'showroom' ? ($detail->qty ?? 0) : ($detail->quantity ?? 0);
                    DB::table('master_product_min_stocks')
                        ->where('product_packaging_id', $detail->product_packaging_id)
                        ->increment('quantity', $quantity);

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
            dd($e);
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

            $type = $request->type ?? 'showroom'; // default showroom

            if ($type === 'showroom') {
                $mutasi = MutasiShowroom::with('details')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->details;
            } else { // gudang
                $mutasi = MutasiOut::with('mutasiOutDetails')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->mutasiOutDetails;
            }

            if ($mutasi->status_checked != 1) {
                throw new \Exception('Status tidak valid');
            }

            foreach ($details as $detail) {
                if ($detail->is_checked) {

                    $productId = $detail->product_packaging_id;

                    if ($type === 'showroom') {
                        $qty = (int) ($detail->qty ?? 0);
                    } else { // gudang
                        $qty = (int) ($detail->quantity ?? 0);
                    }

                    DB::table('master_product_min_stocks')
                        ->where('product_packaging_id', $productId)
                        ->increment('quantity', $qty);

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

            $type = $request->type ?? 'showroom';
    
            if ($type === 'showroom') {
                $mutasi = MutasiShowroom::with('details')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->details;
            } else { // gudang
                $mutasi = MutasiOut::with('mutasiOutDetails')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->mutasiOutDetails;
            }
    
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
            $checkedCount = $details
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
            dd($e);
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
            $type = $request->type ?? 'showroom';

            if ($type === 'showroom') {
                $mutasi = MutasiShowroom::with('details')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->details;
            } else {
                $mutasi = MutasiOut::with('mutasiOutDetails')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->mutasiOutDetails;
            }

            $oldStatus = (int) $mutasi->status_barang;
            $newStatus = (int) $request->status_barang;

            // ==============================
            // VALIDASI TRANSISI
            // ==============================
            if ($oldStatus == 2) {
                throw new \Exception('Mutasi sudah DIAMBIL');
            }

            if ($oldStatus == 1 && $newStatus != 2) {
                throw new \Exception('Status hanya bisa diubah ke DIAMBIL');
            }

            // ==============================
            // UPDATE STATUS BARANG
            // ==============================
            $mutasi->status_barang = $newStatus;

            // ==============================
            // UPDATE STATUS HEADER
            // ==============================
            if ($type === 'gudang') {

                if ($newStatus === 1) {
                    // Belum Diambil → tetap PUBLISH (2)
                    $mutasi->status = 2;
                }

                if ($newStatus === 2) {
                    // Sudah Diambil → ACC (3)
                    $mutasi->status = 3;
                }
            }

            $mutasi->save();

            // ==============================
            // CATAT STOCK MOVE HANYA JIKA DIAMBIL
            // ==============================
            if ($newStatus === 2 && $oldStatus !== 2) {

                foreach ($details as $detail) {

                    $productId = $detail->product_packaging_id;

                    $lastStock = DB::table('gudang_move_stock')
                        ->where('product_packaging_id', $productId)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $quantity = $type === 'showroom'
                        ? $detail->qty
                        : $detail->quantity;

                    $quantity_after_move = $lastStock
                        ? $lastStock->stock_balance - $quantity
                        : 0;

                    StockMove::create([
                        'code_transaction' => $type === 'showroom'
                            ? $mutasi->kode
                            : $mutasi->code,

                        'warehouse_id' => $type === 'showroom'
                            ? $mutasi->warehouse_from_id
                            : $mutasi->warehouse_from,

                        'product_packaging_id' => $productId,
                        'stock_in' => 0,
                        'stock_out' => $quantity,
                        'stock_balance' => $quantity_after_move,
                        'note' => $type === 'showroom'
                            ? 'Mutasi Showroom - DIAMBIL'
                            : 'Mutasi Gudang - DIAMBIL',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'to_selesai' => $newStatus === 2
            ]);

        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}