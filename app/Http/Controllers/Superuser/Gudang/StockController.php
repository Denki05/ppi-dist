<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Warehouse;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\DeliveryOrderMutationItem;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\CanvasingItem;
use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Gudang\ReceivingDetailColly;
use App\Entities\Gudang\StockMove;
use App\Entities\Gudang\StockAdjustment;
use App\Entities\Gudang\MonthEndBalance;
use App\Entities\Penjualan\SalesOrder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Gudang\StockTransactionExport;
use App\Exports\Gudang\StockExport;
use App\Exports\Gudang\StockImportTemplate;
use App\Imports\Gudang\StockImport;
use Illuminate\Support\Facades\Artisan;
use App\Entities\Setting\UserMenu;
use Validator;
use Carbon\Carbon;
use DB;
use Auth;

class StockController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.stock.";
        $this->route = "superuser.gudang.stock";
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

    public function json(Request $request)
    {
        $warehouse = $request->warehouse_id;
        if (!$warehouse) return ['data' => ''];

        $rows = ProductMinStock::with(['product_pack.product','product_pack.packaging'])
            ->where('warehouse_id', $warehouse)
            ->get();

        $totalQty       = 0;
        $totalReserved  = 0;
        $totalAvailable = 0;

        $dataRows = [];

        foreach ($rows as $row) {

            $pack = $row->product_pack;
            if (!$pack) continue;

            $quantity  = (float) $row->quantity;
            $reserved  = (float) ($row->reserved_quantity ?? 0);
            $available = $quantity - $reserved;

            $totalQty       += $quantity;
            $totalReserved  += $reserved;
            $totalAvailable += $available;

            $dataRows[] = [
                '<a href="'.route('superuser.gudang.stock.detail', [$warehouse, base64_encode($pack->id)]).'" target="_blank">'.$pack->code.'</a>',
                $pack->name,
                $pack->product->brand_name ?? '-',
                $pack->packaging->pack_name ?? '-',
                number_format($quantity, 2),
                number_format($reserved, 2),
                number_format($available, 2),
            ];
        }

        return [
            'data'            => $dataRows,
            'total_stock'     => number_format($totalQty, 2),
            'total_reserved'  => number_format($totalReserved, 2),
            'total_available' => number_format($totalAvailable, 2),
        ];
    }
    
    // public function json(Request $request)
    // {
    //     $warehouse = $request->warehouse_id;
    //     if (!$warehouse) return ['data' => ''];
    
    //     $rows = ProductMinStock::with(['product_pack.product', 'product_pack.packaging'])
    //         ->where('warehouse_id', $warehouse)
    //         ->get();
    
    //     $totalQty       = 0;
    //     $totalReserved  = 0;
    //     $totalAvailable = 0;
    
    //     $dataRows = [];
    
    //     foreach ($rows as $row) {
    
    //         $pack = $row->product_pack;
    
    //         if (!$pack) continue;
    
    //         $quantity  = (float) $row->quantity;
    //         $reserved  = (float) ($row->reserved_quantity ?? 0);
    //         $available = $quantity - $reserved;
    
    //         $totalQty       += $quantity;
    //         $totalReserved  += $reserved;
    //         $totalAvailable += $available;
    
    //         $dataRows[] = [
    //             '<a href="'.route('superuser.gudang.stock.detail', [$warehouse, base64_encode($pack->id)]).'" target="_blank">'.$pack->code.'</a>',
    //             $pack->name,
    //             $pack->product->brand_name ?? '-',
    //             $pack->packaging->pack_name ?? '-',
    //             number_format($quantity, 2),
    //             number_format($reserved, 2),
    //             number_format($available, 2),
    //         ];
    //     }
    
    //     return [
    //         'data'            => $dataRows,
    //         'total_stock'     => number_format($totalQty, 2),
    //         'total_reserved'  => number_format($totalReserved, 2),
    //         'total_available' => number_format($totalAvailable, 2),
    //     ];
    // }

    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouses'] = Warehouse::all();

        return view($this->view."index",$data);
    }

    public function date_compare($element1, $element2)
    {
        $datetime1 = strtotime($element1['created_at']);
        $datetime2 = strtotime($element2['created_at']);
        return $datetime1 - $datetime2;
    }

    public function detail($warehouse, $encoded)
    {
        $productId = base64_decode($encoded);

        $warehouse = Warehouse::findOrFail($warehouse);
        $pack      = ProductPack::with(['product','packaging'])->findOrFail($productId);

        $moves = StockMove::where([
                    ['warehouse_id', $warehouse->id],
                    ['product_packaging_id', $productId]
                ])
                ->orderBy('created_at')
                ->get();

        // saldo real saat ini
        $currentStock = ProductMinStock::where([
                            ['warehouse_id', $warehouse->id],
                            ['product_packaging_id', $productId]
                        ])->first();

        $realBalance = $currentStock ? $currentStock->quantity : 0;

        $balance = 0;
        $collect = [];

        foreach ($moves as $m) {

            $balance += ($m->stock_in - $m->stock_out);

            $collect[] = [
                'created_at'  => $m->created_at->format('d/m/Y H:i'),
                'transaction' => $m->code_transaction,
                'in'          => $m->stock_in ?: '',
                'out'         => $m->stock_out ?: '',
                'balance'     => number_format($balance, 2),
                'description' => $m->note,
            ];
        }

        return view('superuser.gudang.stock.detail', [
            'product'     => $pack,
            'warehouse'   => $warehouse,
            'collects'    => $collect,
            'real_balance'=> number_format($realBalance, 2)
        ]);
    }

    public function exportTransactions($warehouse, $startDate, $endDate)
    {
        $warehouse = Warehouse::findOrFail($warehouse);

        // Validate date range
        if ($startDate && $endDate && $startDate > $endDate) {
            return back()->withErrors(['error' => 'Start Date tidak boleh lebih besar dari End Date']);
        }

        // Fetch all products associated with the warehouse
        $products = ProductPack::with(['receiving_detail.collys', 'so_detail', 'stock_adjustments'])
            ->orderBy('name', 'asc')
            ->get();

        $collects = [];

        foreach ($products as $product) {
            // Filter Receivings
            $receivings = Receiving::where('warehouse_id', $warehouse->id)
                ->where('status', Receiving::STATUS['ACC'])
                ->whereHas('details', function ($query) use ($product) {
                    $query->where('product_packaging_id', $product->id);
                });

            if ($startDate && $endDate) {
                $receivings->whereBetween('created_at', [$startDate, $endDate]);
            }

            $receivings = $receivings->get();

            foreach ($receivings as $receiving) {
                foreach ($receiving->details as $detail) {
                    if ($detail->product_packaging_id == $product->id) {
                        foreach ($detail->collys as $colly) {
                            if ($colly->is_reject == 0) {
                                $collects[] = [
                                    'product_id' => $product->id,
                                    'product_code' => $product->code,
                                    'product_name' => $product->name,
                                    'product_pack' => $product->packaging->pack_name,
                                    'brand' => $product->product->brand_name,
                                    'created_at' => $receiving->created_at,
                                    'second_date' => 0,
                                    'transaction' => 'Receiving- ' . $receiving->code,
                                    'in' => $colly->quantity_ri,
                                    'out' => 0,
                                    'description' => $receiving->note,
                                ];
                            }
                        }
                    }
                }
            }

            // Filter Sales Orders
            $sales_orders = SalesOrder::where('origin_warehouse_id', $warehouse->id)
                ->where('status', 4)
                ->whereHas('so_detail', function ($query) use ($product) {
                    $query->where('product_packaging_id', $product->id);
                });

            if ($startDate && $endDate) {
                $sales_orders->whereBetween('created_at', [$startDate, $endDate]);
            }

            $sales_orders = $sales_orders->get();

            foreach ($sales_orders as $sales_order) {
                foreach ($sales_order->so_detail as $detail) {
                    if ($detail->product_packaging_id == $product->id) {
                        $collects[] = [
                            'product_id' => $product->id,
                            'product_code' => $product->code,
                            'product_name' => $product->name,
                            'product_pack' => $product->packaging->pack_name,
                            'brand' => $product->product->brand_name,
                            'created_at' => $detail->created_at,
                            'second_date' => 0,
                            'transaction' => 'Sales Order- ' . $sales_order->code,
                            'in' => 0,
                            'out' => $detail->qty_worked,
                            'description' => $detail->description ?? '',
                        ];
                    }
                }
            }

            // Filter Stock Adjustments
            $stock_adjustments = StockAdjustment::where('warehouse_id', $warehouse->id)
                ->where('product_packaging_id', $product->id);

            if ($startDate && $endDate) {
                $stock_adjustments->whereBetween('created_at', [$startDate, $endDate]);
            }

            $stock_adjustments = $stock_adjustments->get();

            foreach ($stock_adjustments as $stock_adjustment) {
                $collects[] = [
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'product_pack' => $product->packaging->pack_name,
                    'brand' => $product->product->brand_name,
                    'created_at' => $stock_adjustment->created_at,
                    'second_date' => 0,
                    'transaction' => 'Stock Adjustment- ' . $stock_adjustment->code,
                    'in' => $stock_adjustment->plus,
                    'out' => $stock_adjustment->min,
                    'description' => $stock_adjustment->note ?? '',
                ];
            }
        }

        if ($collects) {
            // Fetch Initial Balances (Cutoff at the end of the previous month)
            $initialBalances = MonthEndBalance::where('warehouse_id', $warehouse->id)
                ->whereIn('product_packaging_id', $products->pluck('id'))
                ->where('month', '<=', Carbon::parse($startDate)->subMonth()->endOfMonth())
                ->get()
                ->keyBy('product_packaging_id');

            $grouped = collect($collects)
                ->groupBy('product_id')
                ->map(function ($transactions) {
                    return $transactions->sortBy('created_at');
                });

            $newCollect = [];

            foreach ($grouped as $productId => $productGroup) {
                $balance = 0;

                // Set initial balance if available
                if ($initialBalances->has($productId)) {
                    $balance = $initialBalances->get($productId)->balance;

                    $newCollect[] = [
                        'product_id' => $productId,
                        'product_code' => $products->firstWhere('id', $productId)->code,
                        'product_name' => $products->firstWhere('id', $productId)->name,
                        'product_pack' => $products->firstWhere('id', $productId)->packaging->pack_name,
                        'brand' => $products->firstWhere('id', $productId)->product->brand_name,
                        'created_at' => Carbon::parse($startDate)->startOfDay(),
                        'transaction' => 'Saldo Awal',
                        'in' => 0,
                        'out' => 0,
                        'balance' => $balance,
                        'description' => 'Saldo Awal Bulan',
                    ];
                }

                // Process transactions for the product
                foreach ($productGroup as $transaction) {
                    $transaction['in'] = $transaction['in'] ?? 0;
                    $transaction['out'] = $transaction['out'] ?? 0;

                    $balance += $transaction['in'];
                    $balance -= $transaction['out'];

                    $newCollect[] = array_merge($transaction, ['balance' => $balance]);
                }
            }

            $data['collects'] = $newCollect;
        }

        return Excel::download(new StockTransactionExport($data), $warehouse->name . '-transactions-' . now()->format('YmdHis') . '.xlsx');
    }

    public function backfillMonthEndBalances()
    {
        Artisan::call('stock:calculate-month-end-balances');

        return response()->json([
            'success' => true,
            'message' => 'Month-end balances calculation initiated.',
        ]);
    }

    public function export_stock_db(Request $request)
    {
        $filename = 'Stock-Quantity-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new StockExport, $filename);
    }

    public function import_template(Request $request)
    {
        $filename = 'stock-import-template.xlsx';
        return Excel::download(new StockImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new StockImport();
            Excel::import($import, $request->import_file);

            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }
}