<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Finance\ReturFat;
use App\Entities\Penjualan\SaleReturn;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;
use DB;

class NotaKreditFinanceController extends Controller
{
    public function __construct(){
        $this->route = "superuser.finance.nota_kredit_finance";
        $this->view = "superuser.finance.nota_kredit_finance.";
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['retur'] = SaleReturn::where('status', 2)
                                   ->get();

        return view($this->view."index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $post = $request->all();

        DB::beginTransaction();

        try {
            // Validasi request
            $validator = Validator::make($post, [
                'do_id' => 'required|integer',
                'retur_id' => 'required|integer',
                'jumlah_nota_awal_cell' => 'required',
                'jumlah_nota_kredit_cell' => 'required',
                'total_piutang_cell' => 'required',
                'action_type' => 'required|string|in:next,verify', // Tambahkan validasi untuk action_type
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses pembayaran. Periksa kembali data yang diinput.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Ambil data dari request
            $do_id = $request->do_id;
            $retur_id = $request->retur_id;
            $action_type = $request->action_type;

            // Logika berdasarkan tombol yang diklik
            if ($action_type === 'verify') {
                

            } else if ($action_type === 'next') {
                // Logika untuk tombol VERIFIKASI
                $getRetur = SaleReturn::find($retur_id);

                if ($getRetur) {
                    $getRetur->fat_status = 1; // 1 = diverifikasi kasir
                    $getRetur->updated_by = Auth::user()->id;
                    $getRetur->save();
                }

                // input finance_retur
                $retur_fat = new ReturFat;
                $retur_fat->do_id = $do_id;
                $retur_fat->retur_id = $retur_id;

                // Perbaikan: Bersihkan string nominal dari titik dan koma
                $retur_fat->total_nota = (float)str_replace(['.', ','], '', $request->jumlah_nota_awal_cell);
                $retur_fat->total_retur = (float)str_replace(['.', ','], '', $request->jumlah_nota_kredit_cell);
                $retur_fat->total_final = (float)str_replace(['.', ','], '', $request->total_piutang_cell);

                $retur_fat->type_retur = $getRetur->type;
                $retur_fat->status_retur = $getRetur->payment_status;
                $retur_fat->created_by = Auth::user()->id;
                $retur_fat->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Verifikasi Nota Kredit oleh kasir finance berhasil"
                ]);
            }

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Payable creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat Payable. Silakan coba lagi. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
