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
use PDF;

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
        if (!$warehouse) return ['data' => []];

        $brand = $request->brand;
        $packaging = $request->packaging;
        $productName = $request->product_name;

        $rows = ProductMinStock::with([
            'product_pack.product',
            'product_pack.packaging'
        ])->where('warehouse_id', $warehouse)
        ->whereHas('product_pack.product')
        ->when($brand, function($q) use ($brand) {
            $q->whereHas('product_pack.product', function($q2) use ($brand) {
                $q2->where('brand_name', $brand);
            });
        })
        ->when($packaging, function($q) use ($packaging) {
            $q->whereHas('product_pack.packaging', function($q2) use ($packaging) {
                $q2->where('packaging_id', $packaging);
            });
        })
        ->when($productName, function($q) use ($productName) {
            $q->whereHas('product_pack.product', function($q2) use ($productName) {
                $q2->where('name', 'like', "%$productName%");
            });
        })
        ->get();

        $data = [];
        $no = 1;

        foreach ($rows as $row) {
            $pack = $row->product_pack;
            if (!$pack || !$pack->product) continue;
            $product = $pack->product;

            $stockQuery = StockMove::where('warehouse_id', $warehouse)
                ->where('product_packaging_id', $pack->id);

            $in  = (float) $stockQuery->sum('stock_in');
            $out = (float) $stockQuery->sum('stock_out');
            $stock = $in - $out;

            $lastMove = (clone $stockQuery)->orderBy('created_at', 'desc')->first();
            $ks = $lastMove ? (float) $lastMove->stock_balance : 0;
            $minStock = (float) ($row->min_stock ?? 10);

            $stockFormatted = number_format($stock,2);
            if ($stock < 0) $stockFormatted = '<span class="text-danger-strong">'.$stockFormatted.'</span>';
            elseif ($stock <= $minStock) $stockFormatted = '<span class="text-warning-strong">'.$stockFormatted.'</span>';

            $ksFormatted = number_format($ks,2);
            if ($ks < 0) $ksFormatted = '<span class="text-danger-strong">'.$ksFormatted.'</span>';
            elseif ($ks <= $minStock) $ksFormatted = '<span class="text-warning-strong">'.$ksFormatted.'</span>';

            $data[] = [
                'no' => $no++,
                'product_pack_id' => $pack->id,
                'encoded_id' => base64_encode($pack->id), // <-- di sini
                'product_name' => $pack->code.' - '.$product->name,
                'brand_name' => $product->brand_name ?? '-',
                'pack_name' => $pack->packaging->pack_name ?? '-',
                'stock' => $stockFormatted,
                'ks' => $ksFormatted,
            ];
        }

        return ['data' => $data];
    }
    
    // public function json(Request $request)
    // {
    //     $warehouse = $request->warehouse_id;
    //     if (!$warehouse) return ['data' => ''];
    
    //     $rows = ProductMinStock::with(['product_pack.product','product_pack.packaging'])
    //         ->where('warehouse_id', $warehouse)
    //         ->get();
    
    //     $totalIn  = 0;
    //     $totalOut = 0;
    //     $totalStk = 0;
    //     $dataRows = [];
    
    //     foreach ($rows as $row) {
    //         $pack = $row->product_pack;
    //         if (!$pack) continue;
    
    //         // Hitung total in dan out dari StockMove
    //         $in  = StockMove::where('warehouse_id', $warehouse)
    //                 ->where('product_packaging_id', $pack->id)
    //                 ->sum('stock_in');
    
    //         $out = StockMove::where('warehouse_id', $warehouse)
    //                 ->where('product_packaging_id', $pack->id)
    //                 ->sum('stock_out');
    
    //         $stock = $in - $out; // saldo real saat ini
    
    //         $totalIn  += $in;
    //         $totalOut += $out;
    //         $totalStk += $stock;
    
    //         $dataRows[] = [
    //             '<a href="'.route('superuser.gudang.stock.detail', [$warehouse, base64_encode($pack->id)]).'" target="_blank">'.$pack->code.'</a>',
    //             $pack->name,
    //             $pack->product->brand_name ?? '-',
    //             $pack->packaging->pack_name ?? '-',
    //             number_format($in, 2),
    //             number_format($out, 2),
    //             number_format($stock, 2),
    //         ];
    //     }
    
    //     return [
    //         'data'        => $dataRows,
    //         'total_in'    => number_format($totalIn, 2),
    //         'total_out'   => number_format($totalOut, 2),
    //         'total_stock' => number_format($totalStk, 2),
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
        $data['brands'] = DB::table('master_brand_lokal')->orderBy('brand_name')->get();
        $data['packaging'] = DB::table('master_packaging')->orderBy('pack_name')->get();

        return view($this->view."index",$data);
    }

    public function date_compare($element1, $element2)
    {
        $datetime1 = strtotime($element1['created_at']);
        $datetime2 = strtotime($element2['created_at']);
        return $datetime1 - $datetime2;
    }

    public function detail(Request $request, $warehouse, $encoded)
{
    try {
        $productId = base64_decode($encoded);
        $month     = $request->month ?? now()->format('Y-m');

        // Pastikan format YYYY-MM
        if(!preg_match('/^\d{4}-\d{2}$/', $month)){
            $month = now()->format('Y-m');
        }

        $warehouse = Warehouse::findOrFail($warehouse);
        $pack      = ProductPack::with(['product','packaging'])->findOrFail($productId);

        $date     = \Carbon\Carbon::parse($month . '-01');
        $year     = $date->year;
        $monthNum = $date->month;

        $moves = StockMove::where('warehouse_id', $warehouse->id)
                    ->where('product_packaging_id', $productId)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $monthNum)
                    ->orderBy('created_at', 'asc')
                    ->get();

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
                'in'          => $m->stock_in ?: '-',
                'out'         => $m->stock_out ?: '-',
                'balance'     => number_format($balance, 2),
                'description' => $m->note,
            ];
        }

        // Selalu kembalikan view, bahkan jika $collect kosong
        return view('superuser.gudang.stock.detail_modal', [
            'product'      => $pack,
            'warehouse'    => $warehouse,
            'collects'     => $collect,
            'real_balance' => number_format($realBalance, 2),
            'month'        => $month,
        ]);

    } catch (\Exception $e) {
        dd($e);
        // Tangani error gracefully
        return view('superuser.gudang.stock.detail_modal', [
            'product'      => $pack ?? null,
            'warehouse'    => $warehouse ?? null,
            'collects'     => [],
            'real_balance' => 0,
            'month'        => $month ?? now()->format('Y-m'),
            'error_msg'    => 'Tidak ada data untuk bulan ini atau terjadi kesalahan'
        ]);
    }
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

    public function import_template()
    {
        return Excel::download(
            new StockImportTemplate,
            'stock-opening-template.xlsx'
        );
    }

    public function import(Request $request)
    {
        // Access
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || $this->access->can_create == 0) {
                return redirect()
                    ->route('superuser.index')
                    ->with('error', 'Anda tidak punya akses');
            }
        }
    
        $request->validate([
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);
    
        // $import = new StockOpeningImport(
        //     now()->format('Y-m-d'),
        //     'GO LIVE OPENING STOCK'
        // );
    
        // Excel::import($import, $request->file('import_file'));
        
        $import = new StockImport(
            $request->warehouse_id,
            now()
        );
        
        Excel::import($import, $request->file('import_file'));
    
        return redirect()->back()->with([
            'collect_success' => $import->success,
            'collect_error'   => $import->error,
        ]);
    }
    
    public function printPdf(Request $request, $warehouse, $encoded)
    {
        $productId = base64_decode($encoded);
    
        $warehouse = Warehouse::findOrFail($warehouse);
        $pack      = ProductPack::with(['product','packaging'])->findOrFail($productId);
    
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : null;
    
        $endDate   = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : null;
    
        $query = StockMove::where([
                    ['warehouse_id', $warehouse->id],
                    ['product_packaging_id', $productId]
                ])
                ->orderBy('created_at');
    
        // saldo awal sebelum tanggal filter
        $openingBalance = 0;
    
        if ($startDate) {
            $openingBalance = StockMove::where([
                    ['warehouse_id', $warehouse->id],
                    ['product_packaging_id', $productId]
                ])
                ->where('created_at', '<', $startDate)
                ->selectRaw('SUM(stock_in - stock_out) as saldo')
                ->value('saldo') ?? 0;
    
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        $moves = $query->get();
    
        $balance = $openingBalance;
        $collect = [];
    
        foreach ($moves as $m) {
    
            $balance += ($m->stock_in - $m->stock_out);
    
            $collect[] = [
                'date'        => $m->created_at->format('d/m/Y H:i'),
                'transaction' => $m->code_transaction,
                'in'          => $m->stock_in ?: '',
                'out'         => $m->stock_out ?: '',
                'balance'     => number_format($balance, 2),
                'description' => $m->note,
            ];
        }
    
        $pdf = PDF::loadView('superuser.gudang.stock.print', [
            'product'        => $pack,
            'warehouse'      => $warehouse,
            'collects'       => $collect,
            'openingBalance' => $openingBalance,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
        ])->setPaper('A5', 'landscape');
    
        return $pdf->stream('Kartu-Stock-'.$pack->code.'.pdf');
    }
}