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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['mutasi_out'] = MutasiOut::get();

        return view($this->view."index", $data);
    }

    public function create(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouse_to'] = Warehouse::pluck('name', 'id');
        $data['warehouse_from'] = Warehouse::pluck('name', 'id');
        $data['brand'] = DB::table('master_brand_lokal')->get();

        return view($this->view."create", $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'warehouse_from' => 'required|integer',
                'warehouse_to'   => 'required|integer|different:warehouse_from',
            ]);

            if ($validator->fails()) {
                return $this->response(400, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'header'  => 'Error',
                        'content' => $validator->errors()->all(),
                    ]
                ]);
            }

            DB::beginTransaction();
            try {
                $mutation_out = new MutasiOut;

                // ✅ Generate kode otomatis sesuai warehouse_to
                $mutation_out->code = $request->code;

                $mutation_out->warehouse_from = $request->warehouse_from;
                $mutation_out->warehouse_to   = $request->warehouse_to;
                $mutation_out->spk_id         = $request->spk_code ?? null;
                $mutation_out->created_by     = Auth::id();
                $mutation_out->status         = MutasiOut::STATUS['ACTIVE'];
                $mutation_out->save();

                if ($request->sku) {
                    foreach ($request->sku as $key => $value) {
                        if ($value) {
                            $mutation_detail = new MutasiOutDetail;
                            $mutation_detail->mutasi_out_id       = $mutation_out->id;
                            $mutation_detail->product_packaging_id = $value;
                            $mutation_detail->quantity            = $request->qty[$key];
                            $mutation_detail->note                = $request->description[$key];
                            $mutation_detail->save();
                        }
                    }
                }

                DB::commit();

                return $this->response(200, [
                    'notification' => [
                        'alert'   => 'notify',
                        'type'    => 'success',
                        'content' => 'Success',
                    ],
                    'redirect_to' => route('superuser.gudang.mutasi_out.index')
                ]);

            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                return $this->response(400, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'header'  => 'Error',
                        'content' => "Internal Server Error!",
                    ]
                ]);
            }
        }
    }

    public function show($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

         $data['mutasi_out'] = MutasiOut::findOrFail($id);

        return view($this->view."show", $data);
    }

    public function acc(Request $request, $id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $mutation_out = MutasiOut::findOrFail($id);

        if ($mutation_out === null) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            $failed = '';
            $get_stok = 0;

            foreach ($mutation_out->mutasiOutDetails as $item) {
                $base_product_packaging_id = preg_replace('/_\d+$/', '', $item->product_packaging_id);
                $stock = ProductMinStock::where('warehouse_id', $mutation_out->warehouse_from)
                                    ->where('product_packaging_id', $base_product_packaging_id) // Base ID
                                    ->first();

                if ($stock) {
                    $get_stock = $stock->quantity;

                    if ($get_stock <= $item->quantity) {
                        $failed = 'Stock ' . $item->productPackaging->code . ' - '. $item->productPackaging->name . ' tidak mencukupi. ';
                        break;
                    }

                    // Kurangi stok
                    $stock->quantity = $get_stock - $item->quantity;
                    $stock->save();

                    // Hitung pergerakan stok
                    $move = StockMove::where('product_packaging_id', $base_product_packaging_id) // Base ID
                                    ->where('warehouse_id', $mutation_out->warehouse_from)
                                    ->get();

                    $move_in = $move->sum('stock_in');
                    $move_out = $move->sum('stock_out');

                    // Sisa stok setelah transaksi
                    $sisa = $get_stock + $move_in - $move_out - $item->quantity;

                    // Out Move (wajib dibuat)
                    StockMove::create([
                        'code_transaction'     => $mutation_out->code,
                        'warehouse_id'         => $mutation_out->warehouse_from,
                        'product_packaging_id' => $base_product_packaging_id,
                        'stock_out'            => $item->quantity,
                        'stock_balance'        => $sisa,
                        'created_by'           => Auth::id(),
                    ]);

                    // In Move (hanya jika warehouse_to bukan 5 atau 6)
                    if (!in_array($mutation_out->warehouse_to, [5, 6])) {
                        StockMove::create([
                            'warehouse_id'         => $mutation_out->warehouse_to,
                            'product_packaging_id' => $base_product_packaging_id,
                            'code_transaction'     => $mutation_out->code,
                            'stock_in'             => $item->quantity,
                            'stock_out'            => 0,
                            'stock_balance'        => $stock->quantity,
                            'created_by'           => Auth::id(),
                        ]);
                    }
                }
            }

            $mutation_out->status = MutasiOut::STATUS['ACC'];
            $mutation_out->acc_by = Auth::id();
            $mutation_out->acc_date = Carbon::now()->toDateTimeString();
            $mutation_out->save();

            DB::commit();

            $response['notification'] = [
                'alert'   => 'notify',
                'type'    => 'success',
                'content' => 'Success',
            ];

            $response['redirect_to'] = route('superuser.gudang.mutasi_out.index');

            return $this->response(200, $response);
        } catch (\Exception $e) {
            DB::rollback();

            $response['notification'] = [
                'alert'   => 'block',
                'type'    => 'alert-danger',
                'header'  => 'Error',
                'content' => "Internal Server Error!",
            ];

            return $this->response(400, $response);
        }
    }

    public function searchSpk(Request $request)
    {
        // Validate the input data
        $validatedData = $request->validate([
            'q' => 'nullable|string|max:255',
        ]);

        try {
            
            $spk = PurchaseOrder::from('purchase_order as po')
                ->leftJoin('purchase_order_detail as pod', 'po.id', '=', 'pod.po_id')
                ->where('po.type', PurchaseOrder::TYPE['SPK'])
                ->where('po.status', PurchaseOrder::STATUS['SENT'])
                ->when(!empty($validatedData['q']), function ($query) use ($validatedData) {
                    $query->where('po.code', 'LIKE', '%' . $validatedData['q'] . '%');
                })
                ->select(
                    'po.id',
                    'po.code as po_code',
                    'pod.note_repack'
                )
                ->get();

                $results = $spk->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'text' => trim("{$row->po_code} - {$row->note_repack}"),
                    ];
                });
            return response()->json(['results' => $results], 200);
        } catch (\Exception $e) {
            // Catch unexpected errors and respond with a 500 error code
            return response()->json([
                'message' => 'An error occurred while fetching the data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}