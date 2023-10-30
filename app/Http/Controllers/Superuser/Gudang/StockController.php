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

            $receivings = Receiving::where('warehouse_id', $warehouse)->where('status', Receiving::STATUS['ACC'])->get();
            foreach ($receivings as $receiving) {
                foreach ($receiving->details as $detail) {
                    if (!empty($collect[$detail->product_packaging_id]['in'])) {
                        $collect[$detail->product_packaging_id]['in'] += $detail->total_quantity_ri;
                    } else {
                        $collect[$detail->product_packaging_id]['in'] = $detail->total_quantity_ri;
                    }

                    foreach ($detail->collys as $colly) {
                        if ($colly->status_qc == ReceivingDetailColly::STATUS_QC['USED'] && $colly->quantity_recondition > 0) {
                            if (!empty($collect[$colly->receiving_detail->product_packaging_id]['out'])) {
                                $collect[$colly->receiving_detail->product_packaging_id]['out'] += $colly->quantity_recondition;
                            } else {
                                $collect[$colly->receiving_detail->product_packaging_id]['out'] = $colly->quantity_recondition;
                            }
                        }

                        if ($colly->status_mutation == ReceivingDetailColly::STATUS_MUTATION['USED'] && $colly->quantity_mutation > 0) {
                            $mutation_detail = MutationDetail::where('receiving_detail_colly_id', $colly->id)->groupBy('receiving_detail_colly_id')->get();

                            $mutation_gudang_utama_detail = MutationGudangUtamaDetail::where('receiving_detail_colly_id', $colly->id)->groupBy('receiving_detail_colly_id')->get();

                            foreach($mutation_detail as $item){
                                if ($item && $item->mutation->status == Mutation::STATUS['ACC']) {
                                    if($colly->product_to == 0){
                                        if (!empty($collect[$colly->receiving_detail->product_packaging_id]['out'])) {
                                            $collect[$colly->receiving_detail->product_packaging_id]['out'] += $colly->quantity_mutation;
                                        } else {
                                            $collect[$colly->receiving_detail->product_packaging_id]['out'] = $colly->quantity_mutation;
                                        }
                                    } else {
                                        if (!empty($collect[$colly->product_to]['out'])) {
                                            $collect[$colly->product_to]['out'] += $colly->quantity_mutation;
                                        } else {
                                            $collect[$colly->product_to]['out'] = $colly->quantity_mutation;
                                        }
                                    }
                                }
                            }

                            foreach($mutation_gudang_utama_detail as $item){
                                if ($item && $item->mutation_gudang_utama->status == MutationGudangUtama::STATUS['ACC']) {
                                    if($colly->product_to == 0){
                                        if (!empty($collect[$colly->receiving_detail->product_id]['out'])) {
                                            $collect[$colly->receiving_detail->product_packaging_id]['out'] += $colly->quantity_mutation;
                                        } else {
                                            $collect[$colly->receiving_detail->product_packaging_id]['out'] = $colly->quantity_mutation;
                                        }
                                    } else {
                                        if (!empty($collect[$colly->product_to]['out'])) {
                                            $collect[$colly->product_to]['out'] += $colly->quantity_mutation;
                                        } else {
                                            $collect[$colly->product_to]['out'] = $colly->quantity_mutation;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $sales_orders = SalesOrder::select(\DB::raw('penjualan_so_item.product_packaging_id, SUM(penjualan_so_item.qty_worked) as totalquantity'))
                ->leftJoin('penjualan_so_item', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
                ->where('origin_warehouse_id', $warehouse)
                ->where('status', 4)
                ->whereHas('do', function ($query) {
                    $query->where('status', '>', '2');
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
                        $query->where('status', '>', '2');
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
                $effective = $stock;
                $data['data'][] = ['<a href="' . route('superuser.gudang.stock.detail', [$warehouse, base64_encode($product->id)]) . '" target="_blank">' . $product->code.' - '.$product->name . '</a>', $product->kemasan()->pack_name , $in, $out, $stock, $sell, $effective];
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

    public function date_compare($element1, $element2)
    {
        $datetime1 = strtotime($element1['created_at']);
        $datetime2 = strtotime($element2['created_at']);
        return $datetime1 - $datetime2;
    }

    public function detail($warehouse, $product)
    {
        $decode_product = base64_decode($product);

        $data['product'] = ProductPack::findOrFail($decode_product);
        $data['warehouse'] = Warehouse::findOrFail($warehouse);
        $data['collects'] = [];

        $collect = [];

        $receivings = Receiving::where('warehouse_id', $warehouse)->where('status', Receiving::STATUS['ACC'])
            ->whereHas('details', function ($query) use ($decode_product) {
                $query->where('product_packaging_id', $decode_product);
            })
            ->get();
        foreach ($receivings as $receiving) {
            foreach ($receiving->details as $detail) {
                if ($detail->product_packaging_id == $decode_product) {
                    foreach ($detail->collys as $colly) {
                        if($colly->is_reject == 0){
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

        $sales_orders = SalesOrder::where('origin_warehouse_id', $warehouse)
            ->where('status', 4)
            ->where('condition', '1')
            ->whereHas('so_detail', function ($query) use ($decode_product) {
                $query->where('product_packaging_id', $decode_product);
            })
            ->get();
        foreach ($sales_orders as $sales_order) {
            $do = PackingOrder::where('so_id', $sales_order->id)->first();
            if ($do->status == 3) {
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
                    'in' => ($value['in'] == '') ? '' : $value['in'],
                    'out' => ($value['out'] == '') ? '' : $value['out'],
                    'balance' => $balance,
                    'description' => $value['description'],
                ];
            }

            $sortedArr = collect($newCollect)->sortKeysDesc()->all();
            $data['collects'] = $sortedArr;
        }

        return view('superuser.gudang.stock.detail', $data);
    }
}