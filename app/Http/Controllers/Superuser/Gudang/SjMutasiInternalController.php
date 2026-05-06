<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Gudang\MutasiShowroom;
use App\Entities\Gudang\MutasiOut;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Warehouse;
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
        $data['customers'] = CustomerOtherAddress::with('store')
            ->whereHas('store', function($query) {
                $query->where('status', 1);
            })
            ->orderBy('name')
            ->get();

        
        $data['warehouses'] = Warehouse::orderBy('name')->where('status', 1)->get();

        return view($this->view . 'index', $data);
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

        if (!$type) {
            abort(404, 'Type tidak ditemukan.');
        }

        if ($type === 'showroom') {

            $mutasi = MutasiShowroom::with('details.product_packaging')
                ->findOrFail($id);

        } elseif ($type === 'gudang') {

            $mutasi = MutasiOut::with('mutasiOutDetails.product_pack')
                ->findOrFail($id);

            $mutasi->details = $mutasi->mutasiOutDetails;

        } else {
            abort(404);
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

                    $qty = $type === 'showroom'
                        ? (float)($detail->qty ?? 0)
                        : (float)($detail->quantity ?? 0);
                    
                    $warehouseId = $type === 'showroom'
                        ? $mutasi->warehouse_from_id
                        : $mutasi->warehouse_from;

                    if ($qty > 0 && $productId) {
                        // Memanggil Stock Service untuk memotong stok fisik tanpa mengganggu reserved
                        $stockService = app(\App\Services\StockService::class);
                        $stockService->deductPhysicalStock($warehouseId, $productId, $qty);
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
            $type = $request->type ?? 'showroom';
    
            if ($type === 'showroom') {
                $mutasi = MutasiShowroom::with('details')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);
    
                $details = $mutasi->details;
                $warehouseId = $mutasi->warehouse_from_id;
            } else {
                $mutasi = MutasiOut::with('mutasiOutDetails')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);
    
                $details = $mutasi->mutasiOutDetails;
                $warehouseId = $mutasi->warehouse_from;
            }
    
            // hanya boleh cancel jika sudah di-check dan belum diambil
            if ($mutasi->status_checked != 1) {
                throw new \Exception('Tidak bisa cancel');
            }
    
            foreach ($details as $detail) {
                if ($detail->is_checked == 1) {
    
                    $quantity = $type === 'showroom'
                        ? (float)($detail->qty ?? 0)
                        : (float)($detail->quantity ?? 0);
    
                    if ($quantity > 0) {
                        // Memanggil StockService untuk mengembalikan kuantiti fisik
                        $stockService = app(\App\Services\StockService::class);
                        $stockService->undoDeductPhysicalStock($warehouseId, $detail->product_packaging_id, $quantity);
                    }
    
                    // reset checklist
                    $detail->is_checked = 0;
                    $detail->save();
                }
            }
    
            $mutasi->status_checked = 0;
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
                'html'    => $html
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
            $type = $request->type ?? 'showroom'; // default showroom

            if ($type === 'showroom') {
                $mutasi = MutasiShowroom::with('details')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->details;
                $warehouseId = $mutasi->warehouse_from_id;
            } else { // gudang
                $mutasi = MutasiOut::with('mutasiOutDetails')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->mutasiOutDetails;
                $warehouseId = $mutasi->warehouse_from;
            }

            if ($mutasi->status_checked != 1) {
                throw new \Exception('Status tidak valid');
            }

            foreach ($details as $detail) {
                if ($detail->is_checked) {
                    $productId = $detail->product_packaging_id;

                    if ($type === 'showroom') {
                        $qty = (float) ($detail->qty ?? 0);
                    } else { // gudang
                        $qty = (float) ($detail->quantity ?? 0);
                    }

                    // Menggunakan StockService untuk mengembalikan fisik
                    $stockService = app(\App\Services\StockService::class);
                    $stockService->undoDeductPhysicalStock($warehouseId, $productId, $qty);

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
             * VALIDASI 3 (OPSIONAL â€“ JAGA FLOW)
             * Status mutasi masih aktif
             */
            if ($mutasi->status_barang == 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mutasi sudah selesai diproses'
                ], 422);
            }
    
            /**
             * LOLOS â†’ BOLEH LANJUT STEP 3
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
            // ==============================
            // VALIDASI AWAL
            // ==============================
            $request->validate([
                'mutasi_id'     => 'required|integer',
                'status_barang' => 'required|in:1,2',
                'type'          => 'nullable|string'
            ]);

            $type = $request->type ?? 'showroom';

            // ==============================
            // LOAD MODEL
            // ==============================
            if ($type === 'showroom') {
                $mutasi = MutasiShowroom::with('details')
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);

                $details = $mutasi->details;
            } else {
                $mutasi = MutasiOut::with(['mutasiOutDetails', 'warehouse_to_attribute'])
                    ->lockForUpdate()
                    ->findOrFail($request->mutasi_id);
                
                $details = $mutasi->mutasiOutDetails;
            }

            $oldStatus = (int) $mutasi->status_barang;
            $newStatus = (int) $request->status_barang;

            // ==============================
            // VALIDASI TRANSISI
            // ==============================
            if ($oldStatus === 2) {
                throw new \Exception('Mutasi sudah DIAMBIL dan tidak bisa diubah.');
            }

            if ($oldStatus === 1 && $newStatus !== 2) {
                throw new \Exception('Status hanya bisa diubah ke DIAMBIL.');
            }

            // ==============================
            // VALIDASI IMAGE JIKA DIAMBIL
            // ==============================
            if ($newStatus === 2) {
                $request->validate([
                    'image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
                ]);
            }

            // ==============================
            // SIMPAN IMAGE
            // ==============================
            if ($newStatus === 2 && $oldStatus !== 2 && $request->hasFile('image')) {
                $folder = $type === 'showroom' ? 'mutasi_showroom' : 'mutasi_out';
                $destinationPath = storage_path('app/public/' . $folder);

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file = $request->file('image');
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);

                $mutasi->image = $folder . '/' . $filename;
                $mutasi->taken_at = now();
            }

            // ==============================
            // UPDATE STATUS
            // ==============================
            $mutasi->status_barang = $newStatus;

            if ($type === 'gudang') {
                if ($newStatus === 1) {
                    $mutasi->status = 2;
                }
                if ($newStatus === 2) {
                    $mutasi->status = 3;
                }
            }

            $mutasi->save();

            // ==============================
            // STOCK MOVE
            // ==============================
            if ($newStatus === 2 && $oldStatus !== 2) {
                foreach ($details as $detail) {
                    $productId = $detail->product_packaging_id;
                    
                    $warehouseId = $type === 'showroom'
                        ? $mutasi->warehouse_from_id
                        : $mutasi->warehouse_from;
                    
                    $quantity = $type === 'showroom'
                        ? $detail->qty
                        : $detail->quantity;
                    
                    $transactionCode = $type === 'showroom' ? $mutasi->kode : $mutasi->code;
                    $note = $type === 'showroom'
                        ? 'Mutasi Showroom - DIAMBIL'
                        : 'Mutasi Out - ' . ($mutasi->warehouse_to_attribute->name ?? '-') . ' - DIAMBIL';

                    // 🔒 Ambil Opening Balance atau data saat ini
                    $lastMove = \App\Entities\Gudang\StockMove::where('warehouse_id', $warehouseId)
                        ->where('product_packaging_id', $productId)
                        ->orderByDesc('id')
                        ->lockForUpdate()
                        ->first();

                    if (!$lastMove) {
                        $currentStock = DB::table('master_product_min_stocks')
                            ->where('warehouse_id', $warehouseId)
                            ->where('product_packaging_id', $productId)
                            ->lockForUpdate()
                            ->first();

                        $openingQty = $currentStock ? $currentStock->quantity : 0;

                        \App\Entities\Gudang\StockMove::create([
                            'code_transaction'     => 'OPENING',
                            'warehouse_id'         => $warehouseId,
                            'product_packaging_id' => $productId,
                            'stock_in'             => $openingQty,
                            'stock_out'            => 0,
                            'stock_balance'        => $openingQty,
                            'note'                 => 'Auto Opening Balance',
                            'created_by'           => Auth::id(),
                        ]);
                    }

                    $stockService = app(\App\Services\StockService::class);
                    $stockService->recordAdministrativeLog(
                        $warehouseId,
                        $productId,
                        $quantity,
                        $transactionCode,
                        $note
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success'    => true,
                'to_selesai' => $newStatus === 2
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function filterSelesai(Request $request)
    {
        if ($request->type === 'showroom') {

            $query = MutasiShowroom::query();

            $query->where('status_barang', 2); // sesuaikan dengan status Anda

            if ($request->kode) {
                $query->where('kode', 'like', '%'.$request->kode.'%');
            }

            if ($request->date_from && $request->date_to) {
                $query->whereBetween('tanggal', [
                    $request->date_from,
                    $request->date_to
                ]);
            }

            if ($request->customer_id) {
                $query->where('customer_other_address_id', $request->customer_id);
            }

            if ($request->type_mutasi) {
                $query->where('type', $request->type_mutasi);
            }

            $rows = $query->latest('tanggal')->paginate(10);

            return view('superuser.gudang.sj_mutasi_internal.partials._table_showroom_rows', [
                'rows' => $rows,
                'muted' => false
            ]);
        }


        if ($request->type === 'gudang') {

            $query = MutasiOut::query();

            $query->where('status_barang', 2);

            if ($request->kode) {
                $query->where('code', 'like', '%'.$request->kode.'%');
            }

            if ($request->date_from && $request->date_to) {
                $query->whereBetween('date', [
                    $request->date_from,
                    $request->date_to
                ]);
            }

            if ($request->warehouse_to) {
                $query->where('warehouse_to', $request->warehouse_to);
            }

            $rows = $query->latest('date')->paginate(10);

            return view('superuser.gudang.sj_mutasi_internal.partials._table_gudang_rows', [
                'rows' => $rows,
                'muted' => false
            ]);
        }
    }
    
    public function viewFile($path)
    {
        $filePath = storage_path('app/public/' . $path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    }
}