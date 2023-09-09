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
use App\Entities\Gudang\StockMove;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Setting\UserMenu;
use DB;
use Auth;

class StockController extends Controller
{
    public function json(Request $request)
    {
        $data = [];
        $warehouse = $request->warehouse_id;

        $collect = [];
        if($warehouse){
            $stocks = ProductMinStock::where('warehouse_id', $warehouse)->get();
            foreach ($stocks as $stock) {
                if (!empty($collect[$stock->product_packaging_id]['in'])) {
                    $collect[$stock->product_packaging_id]['in'] += $stock->quantity;
                } else {
                    $collect[$stock->product_packaging_id]['in'] = $stock->quantity;
                }
            }

            $sales_orders = SalesOrder::select(\DB::raw('penjualan_so_item.product_packaging_id, SUM(penjualan_so_item.qty_worked) as totalquantity'))
                ->leftJoin('penjualan_so_item', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
                ->where('origin_warehouse_id', $warehouse)
                ->where('status', 4)
                ->whereHas('do', function ($query) {
                    $query->where('status', '3');
                })
                ->groupBy('penjualan_so_item.product_packaging_id')
                ->get();

            foreach ($sales_orders as $detail) {
                if (!empty($collect[$detail->product_packaging_id]['out'])) {
                    $collect[$detail->product_packaging_id]['out'] += $detail->totalquantity;
                } else {
                    $collect[$detail->product_packaging_id]['out'] = $detail->totalquantity;
                }
            }

            $sales_orders = SalesOrder::where('origin_warehouse_id', $warehouse)
                ->where('status', 4)
                ->where(function ($query) {
                    $query->whereHas('do', function ($query) {
                        $query->where('status', '2');
                    })->orDoesntHave('do');
                })
                ->select(\DB::raw('penjualan_so_item.product_packaging_id, SUM(penjualan_so_item.qty_worked) as totalquantity'))
                ->leftJoin('penjualan_so_item', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
                ->groupBy('penjualan_so_item.product_packaging_id')
                ->get();

            foreach ($sales_orders as $detail) {
                if (!empty($collect[$detail->product_packaging_id]['sell'])) {
                    $collect[$detail->product_packaging_id]['sell'] += $detail->totalquantity;
                } else {
                    $collect[$detail->product_packaging_id]['sell'] = $detail->totalquantity;
                }
            }

            // COLLECT
            foreach ($collect as $key => $value) {
                $product = ProductPack::find($key);
                $in = !empty($value['in']) ? $value['in'] : 0;
                $out = !empty($value['out']) ? $value['out'] : 0;
                $sell = !empty($value['sell']) ? $value['sell'] : 0;
                $stock = $in - $out;
                $effective = $stock - $sell;
                $data['data'][] = [$product->code, '<a href="' . route('superuser.gudang.stock.detail', [$warehouse, $product->id]) . '" target="_blank">' . $product->name . '</a>', $in, $out, $stock, $sell, $effective];
            }

            if (empty($collect)) {
                $data['data'] = '';
            }
        }else {
            $data['data'] = '';
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

        return view('superuser.gudang.stock.index', $data); 
    }

    public function detail($warehouse, $product)
    {
        $data['product'] = ProductPack::findOrFail($product);
        $data['warehouse'] = Warehouse::findOrFail($warehouse);
        $data['collects'] = [];

        $collect = [];

        $sales_orders = SalesOrder::where('origin_warehouse_id', $warehouse)
            ->where('status', 4)
            ->where('condition', '1')
            ->whereHas('so_detail', function ($query) use ($product) {
                $query->where('product_packaging_id', $product);
            })
            ->get();
        foreach ($sales_orders as $sales_order) {
            $do = PackingOrder::where('so_id', $sales_order->id)->first();
            if ($do->status == 3) {
                foreach ($sales_order->so_detail as $detail) {
                    if ($detail->product_packaging_id == $product) {
                        $collect[] = [
                            'created_at' => $detail->created_at,
                            'second_date' => 0,
                            'transaction' => $sales_order->code,
                            'in' => '',
                            'out' => $detail->qty,
                            'balance' => '',
                            'hpp' => 0,
                            'description' => $detail->description ?? '',
                        ];
                    }
                }
            }
        }

        if ($collect) {
            $balance = 0;
            $newCollect = [];

            $sortedArr = collect($collect)->sortBy('second_date')->sortBy('created_at')->all();
            foreach ($sortedArr as $key => $value) {
                if ($value['in']) {
                    $balance = $balance + $value['in'];
                } else if ($value['out']) {
                    $balance = $balance - $value['out'];
                }
                $newCollect[] = [
                    'created_at' => $value['created_at'],
                    'second_date' => $value['second_date'],
                    'transaction' => $value['transaction'],
                    'in' => ($value['in'] == '') ? '' : number_format($value['in']),
                    'out' => ($value['out'] == '') ? '' : number_format($value['out']),
                    'balance' => $balance,
                    'hpp' => ($value['hpp'] == '') ? '' : number_format($value['hpp'], 2, ',','.'),
                    'description' => $value['description'],
                ];
            }

            $sortedArr = collect($newCollect)->sortKeysDesc()->all();
            $data['collects'] = $sortedArr;
        }

        return view('superuser.gudang.stock.detail', $data);
    }
}