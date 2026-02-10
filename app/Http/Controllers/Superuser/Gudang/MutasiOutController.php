<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use App\Entities\Gudang\MutasiOut;
use App\Entities\Gudang\MutasiOutDetail;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Master\ProductPack;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Warehouse;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\CodeRepo;
use Validator;
use Carbon\Carbon;
use Auth;
use DB;

class MutasiOutController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.mutasi_out.";
        $this->route = "superuser.gudang.mutasi_out";
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

    public function search_sku(Request $request)
    {
        if (!$request->warehouse || !$request->brand_name) {
            return ['results' => []];
        }

        $products = ProductPack::where('master_products_packaging.name', 'LIKE', '%' . $request->input('q', '') . '%')
            ->join('master_product_min_stocks', function ($join) use ($request) {
                $join->on('master_products_packaging.id', '=', 'master_product_min_stocks.product_packaging_id')
                    ->where('master_product_min_stocks.warehouse_id', $request->warehouse)
                    ->where('master_product_min_stocks.quantity', '>', 0);
            })
            ->join('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->join('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->where('master_products.brand_name', $request->brand_name) // filter brand
            ->get([
                'master_products_packaging.id    as id',
                'master_products_packaging.code  as code',
                'master_products_packaging.name  as name',
                'master_packaging.pack_name      as pack',
                'master_product_min_stocks.quantity as stock',
            ])
            ->map(function ($row) {
                $row->text = "{$row->code} – {$row->name} ({$row->pack})";
                return $row;
            });

        return ['results' => $products];
    }

    public function index(Request $request)
    {
        // 1. Cek Akses
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses.');
            }
        }

        // 2. Data untuk Dropdown (Modal Create)
        $data['warehouses'] = Warehouse::where('status', Warehouse::STATUS['ACTIVE'])
            ->forStockMutation()
            ->get();

        $data['brands'] = DB::table('master_products')
            ->select('brand_name')
            ->distinct()
            ->orderBy('brand_name', 'asc')
            ->get();

        // TAB AKTIF
        $data['mutasiAktif'] = MutasiOut::where('status', 1)
            ->orderBy('date', 'desc')
            ->paginate(10, ['*'], 'page_aktif', request('page_aktif'));

        // TAB PROSES LOG
        $data['mutasiProses'] = MutasiOut::where('status', 2)
            ->orderBy('date', 'desc')
            ->paginate(10, ['*'], 'page_proses', request('page_proses'));

        // TAB SELESAI
        $data['mutasiSelesai'] = MutasiOut::where('status', 3)
            ->orderBy('date', 'desc')
            ->paginate(10, ['*'], 'page_selesai', request('page_selesai'));

        return view($this->view . "index", $data);
    }

    public function refreshCounts()
    {
        $aktif = MutasiOut::where('status', 1)
            ->count();

        $proses = MutasiOut::where('status', 2)
            ->count();

        $selesai = MutasiOut::where('status', 3)
            ->count();

        return response()->json([
            'aktif'   => $aktif,
            'proses'  => $proses,
            'selesai' => $selesai,
        ]);
    }

    public function detail($id)
    {
        $data['mutasi'] = MutasiOut::findOrFail($id);

        // Bisa sesuaikan view dengan tampilan Frame B
        return view('superuser.gudang.mutasi_out.partials._detail_popup', $data);
    }

    public function create(Request $request)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || $this->access->can_create == 0) {
                return redirect()
                    ->route('superuser.gudang.mutasi_out.index')
                    ->with('error', 'Anda tidak punya akses membuat mutasi');
            }
        }

        $data['warehouses'] = Warehouse::where('status', 1)->get();

        $data['brands'] = DB::table('master_products')
            ->select('brand_name')
            ->distinct()
            ->orderBy('brand_name', 'asc')
            ->get();

        // =========================
        // DATA HEADER DARI POPUP
        // =========================
        $data['warehouseFrom'] = Warehouse::find($request->warehouse_from);
        $data['warehouseTo']   = Warehouse::find($request->warehouse_to);
        $data['brandSelected'] = $request->brand_name;
        $data['note']          = $request->note;

        return view('superuser.gudang.mutasi_out.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_from' => 'required',
            'warehouse_to'   => 'required|different:warehouse_from',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.qty'        => 'required|numeric|min:0.1',
        ]);

        DB::beginTransaction();
        try {

            $mutation = MutasiOut::create([
                'code'              => CodeRepo::generateMutasiGudangutamaCode(),
                'date'              => Carbon::now(),
                'warehouse_from'    => $request->warehouse_from,
                'warehouse_to'      => $request->warehouse_to,
                'note'              => $request->note,
                'status'            => MutasiOut::STATUS['ACTIVE'], // ⬅️ default ACTIVE
                'created_by'        => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                MutasiOutDetail::create([
                    'mutasi_out_id'             => $mutation->id,
                    'product_packaging_id'  => $item['product_id'],
                    'quantity'              => $item['qty'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('superuser.gudang.mutasi_out.index')
                ->with('success', 'Mutasi berhasil dibuat');

        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function publish($id)
    {
        $mutasi = MutasiOut::findOrFail($id);

        if ($mutasi->status != MutasiOut::STATUS['ACTIVE']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak dapat dipublish karena status bukan Aktif'
            ], 400);
        }

        $mutasi->status = MutasiOut::STATUS['PUBLISH'];
        $mutasi->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Mutasi berhasil dipublish'
        ]);
    }

    public function reloadTab($tab)
    {
        switch($tab){
            case 'aktif':
                $mutasi = MutasiOut::where('status', MutasiOut::STATUS['ACTIVE'])->paginate(10);
                break;
            case 'proses':
                $mutasi = MutasiOut::where('status', MutasiOut::STATUS['PROCESS'])->paginate(10);
                break;
            case 'selesai':
                $mutasi = MutasiOut::where('status', MutasiOut::STATUS['FINISH'])->paginate(10);
                break;
            default:
                abort(404);
        }

        return view('superuser.gudang.mutasi_out._table_tab', compact('mutasi', 'tab'));
    }

}