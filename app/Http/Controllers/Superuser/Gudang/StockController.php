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
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Setting\UserMenu;
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
        $data = [];
        $warehouse = $request->warehouse_id;
        $totalStock = 0; // Initialize total stock
        $totalIn = 0;
        $totalOut = 0;
        $totalSell = 0;

        if (!$warehouse) {
            return ['data' => ''];
        }

        $collect = [];
        $receivings = Receiving::where('warehouse_id', $warehouse)->where('status', Receiving::STATUS['ACC'])->get();

        foreach ($receivings as $receiving) {
            foreach ($receiving->details as $detail) {
                $productPackagingId = $detail->product_packaging_id;

                $collect[$productPackagingId]['in'] = ($collect[$productPackagingId]['in'] ?? 0) + $detail->total_quantity_ri;

                foreach ($detail->collys as $colly) {
                    if ($colly->status_qc == ReceivingDetailColly::STATUS_QC['USED'] && $colly->quantity_recondition > 0) {
                        $collect[$productPackagingId]['out'] = ($collect[$productPackagingId]['out'] ?? 0) + $colly->quantity_recondition;
                    }

                    if ($colly->status_mutation == ReceivingDetailColly::STATUS_MUTATION['USED'] && $colly->quantity_mutation > 0) {
                        $mutationDetails = MutationDetail::where('receiving_detail_colly_id', $colly->id)
                            ->groupBy('receiving_detail_colly_id')
                            ->get();

                        foreach ($mutationDetails as $item) {
                            if ($item && $item->mutation->status == Mutation::STATUS['ACC']) {
                                $productTo = $colly->product_to ?: $productPackagingId;
                                $collect[$productTo]['out'] = ($collect[$productTo]['out'] ?? 0) + $colly->quantity_mutation;
                            }
                        }
                    }
                }
            }
        }

        $salesOrders = SalesOrder::select(\DB::raw('penjualan_so_item.product_packaging_id, SUM(penjualan_so_item.qty_worked) as totalquantity'))
            ->leftJoin('penjualan_so_item', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
            ->where('penjualan_so.origin_warehouse_id', $warehouse)
            ->where('penjualan_so.status', 4)
            ->whereHas('do', function ($query) {
                $query->where('status', '>', '2');
            })
            ->groupBy('penjualan_so_item.product_packaging_id')
            ->get();

        foreach ($salesOrders as $detail) {
            $collect[$detail->product_packaging_id]['out'] = ($collect[$detail->product_packaging_id]['out'] ?? 0) + $detail->totalquantity;
        }

        $salesOrders = SalesOrder::where('origin_warehouse_id', $warehouse)
            ->where('penjualan_so.status', 4)
            ->where(function ($query) {
                $query->whereHas('do', function ($query) {
                    $query->where('status', '>', '2');
                })->orDoesntHave('do');
            })
            ->select(\DB::raw('penjualan_so_item.product_packaging_id, SUM(penjualan_so_item.qty_worked) as totalquantity'))
            ->leftJoin('penjualan_so_item', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
            ->groupBy('penjualan_so_item.product_packaging_id')
            ->get();

        foreach ($salesOrders as $detail) {
            $collect[$detail->product_packaging_id]['sell'] = ($collect[$detail->product_packaging_id]['sell'] ?? 0) + $detail->totalquantity;
        }

        $stockAdjustments = StockAdjustment::where('warehouse_id', $warehouse)->get();

        foreach ($stockAdjustments as $stockAdjustment) {
            $productPackagingId = $stockAdjustment->product_packaging_id;
            if ($stockAdjustment->min == '0') {
                $collect[$productPackagingId]['in'] = ($collect[$productPackagingId]['in'] ?? 0) + $stockAdjustment->plus;
            } else {
                $collect[$productPackagingId]['out'] = ($collect[$productPackagingId]['out'] ?? 0) + $stockAdjustment->min;
            }
        }

        foreach ($collect as $key => $value) {
            $productPack = ProductPack::find($key);
            $in = $value['in'] ?? 0;
            $out = $value['out'] ?? 0;
            $sell = $value['sell'] ?? 0;
            $stock = $in - $out;
            $effective = $stock;

            // Sum up the stock
            $totalStock += $stock;
            $totalIn += $in;
            $totalOut += $out;
            $totalSell += $sell;

            $data['data'][] = [
                '<a href="' . route('superuser.gudang.stock.detail', [$warehouse, base64_encode($productPack->id)]) . '" target="_blank">' . $productPack->code . '</a>',
                $productPack->name,
                $productPack->product->brand_name,
                $productPack->packaging->pack_name,
                $in,
                $out,
                $stock,
                $sell,
                $effective
            ];
        }

        if (empty($collect)) {
            $data['data'] = '';
        } else {
            $data['total_stock'] = $totalStock; // Include total stock in the response
            $data['total_in'] = $totalIn;
            $data['total_out'] = $totalOut;
            $data['total_sell'] = $totalSell;
        }

        return $data;
    }

    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouses'] = Warehouse::get();

        return view($this->view."index",$data);
    }

    public function date_compare($element1, $element2)
    {
        $datetime1 = strtotime($element1['created_at']);
        $datetime2 = strtotime($element2['created_at']);
        return $datetime1 - $datetime2;
    }

    public function detail($warehouse, $product)
    {
        if(Auth::user()->is_superuser == 0) {
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $decode_product = base64_decode($product);

        $data['product'] = ProductPack::findOrFail($decode_product);
        $data['warehouse'] = Warehouse::findOrFail($warehouse);
        $data['collects'] = [];

        $collect = [];

        // Collect receivings
        $receivings = Receiving::where('warehouse_id', $warehouse)
            ->where('status', Receiving::STATUS['ACC'])
            ->whereHas('details', function ($query) use ($decode_product) {
                $query->where('product_packaging_id', $decode_product);
            })
            ->get();
            
        foreach ($receivings as $receiving) {
            foreach ($receiving->details as $detail) {
                if ($detail->product_packaging_id == $decode_product) {
                    foreach ($detail->collys as $colly) {
                        if($colly->is_reject == 0) {
                            $collect[] = [
                                'created_at' => $receiving->created_at,
                                'second_date' => 0,
                                'transaction' => $receiving->code,
                                'in' => $colly->quantity_ri,
                                'out' => '',
                                'balance' => '',
                                'description' => $receiving->note,
                            ];
                        }
                    }
                }
            }
        }

        // Collect sales orders
        $sales_orders = SalesOrder::where('origin_warehouse_id', $warehouse)
            ->where('status', 4)
            ->where('condition', '1')
            ->whereHas('so_detail', function ($query) use ($decode_product) {
                $query->where('product_packaging_id', $decode_product);
            })
            ->get();

        foreach ($sales_orders as $sales_order) {
            foreach ($sales_order->so_detail as $detail) {
                if ($detail->product_packaging_id == $decode_product) {
                    $collect[] = [
                        'created_at' => $detail->created_at,
                        'second_date' => 0,
                        'transaction' => $sales_order->code,
                        'in' => '',
                        'out' => $detail->qty,
                        'balance' => '',
                        'description' => $detail->description ?? '',
                    ];
                }
            }
        }

        // Collect stock adjustments
        $stock_adjusments = StockAdjustment::where('warehouse_id', $warehouse)->get();

        foreach ($stock_adjusments as $stock_adjusment) {
            if ($stock_adjusment->product_packaging_id == $decode_product) {
                $collect[] = [
                    'created_at' => $stock_adjusment->created_at,
                    'second_date' => 0,
                    'transaction' => $stock_adjusment->code,
                    'in' => $stock_adjusment->plus,
                    'out' => $stock_adjusment->min ?? '',
                    'balance' => '',
                    'description' => $stock_adjusment->note ?? '',
                ];
            }
        }

        if ($collect) {
            $balance = 0;
            $newCollect = [];

            $sortedArr = collect($collect)->sortBy(['created_at', 'second_date'])->all();

            foreach ($sortedArr as $value) {
                if ($value['in']) {
                    $balance += $value['in'];
                } elseif ($value['out']) {
                    $balance -= $value['out'];
                }
                $newCollect[] = [
                    'created_at' => $value['created_at'],
                    'second_date' => $value['second_date'],
                    'transaction' => $value['transaction'],
                    'in' => $value['in'] ?: '',
                    'out' => $value['out'] ?: '',
                    'balance' => $balance,
                    'description' => $value['description'],
                ];
            }

            $data['collects'] = collect($newCollect)->sortKeysDesc()->all();
        }

        return view($this->view . "detail", $data);
    }
}