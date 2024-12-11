<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Entities\Master\Warehouse;
use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Gudang\ReceivingDetailColly;
use App\Entities\Master\ProductPack;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Gudang\StockAdjustment;
use App\Entities\Gudang\MonthEndBalance;
use Carbon\Carbon;

class CalculateMonthEndBalances extends Command
{
    protected $signature = 'stock:calculate-month-end-balances';
    protected $description = 'Calculate month-end balances for all warehouses and products';

    public function handle()
    {
        $warehouses = Warehouse::all();
        $products = ProductPack::with(['receiving_detail.collys', 'so_detail', 'stock_adjustments'])->get();

        foreach ($warehouses as $warehouse) {
            $collects = [];

            foreach ($products as $product) {
                $receivings = Receiving::where('warehouse_id', $warehouse->id)
                    ->where('status', Receiving::STATUS['ACC'])
                    ->whereHas('details', function ($query) use ($product) {
                        $query->where('product_packaging_id', $product->id);
                    })
                    ->get();

                foreach ($receivings as $receiving) {
                    foreach ($receiving->details as $detail) {
                        if ($detail->product_packaging_id == $product->id) {
                            foreach ($detail->collys as $colly) {
                                if ($colly->is_reject == 0) {
                                    $collects[] = [
                                        'product_id' => $product->id,
                                        'created_at' => $receiving->created_at,
                                        'transaction' => 'Receiving- ' . $receiving->code,
                                        'in' => $colly->quantity_ri,
                                        'out' => 0,
                                    ];
                                }
                            }
                        }
                    }
                }

                $salesOrders = SalesOrder::where('origin_warehouse_id', $warehouse->id)
                    ->where('status', 4)
                    ->whereHas('so_detail', function ($query) use ($product) {
                        $query->where('product_packaging_id', $product->id);
                    })
                    ->get();

                foreach ($salesOrders as $salesOrder) {
                    foreach ($salesOrder->so_detail as $detail) {
                        if ($detail->product_packaging_id == $product->id) {
                            $collects[] = [
                                'product_id' => $product->id,
                                'created_at' => $salesOrder->created_at,
                                'transaction' => 'Sales Order- ' . $salesOrder->code,
                                'in' => 0,
                                'out' => $detail->qty_worked,
                            ];
                        }
                    }
                }

                $stockAdjustments = StockAdjustment::where('warehouse_id', $warehouse->id)
                    ->where('product_packaging_id', $product->id)
                    ->get();

                foreach ($stockAdjustments as $stockAdjustment) {
                    $collects[] = [
                        'product_id' => $product->id,
                        'created_at' => $stockAdjustment->created_at,
                        'transaction' => 'Stock Adjustment- ' . $stockAdjustment->code,
                        'in' => $stockAdjustment->plus,
                        'out' => $stockAdjustment->min,
                    ];
                }
            }

            if ($collects) {
                $grouped = collect($collects)
                    ->groupBy('product_id')
                    ->map(function ($transactions) {
                        return $transactions->sortBy('created_at');
                    });

                foreach ($grouped as $productId => $productGroup) {
                    $balance = 0;

                    foreach ($productGroup as $transaction) {
                        $balance += $transaction['in'] - $transaction['out'];

                        MonthEndBalance::updateOrCreate(
                            [
                                'warehouse_id' => $warehouse->id,
                                'product_packaging_id' => $productId,
                                'month' => Carbon::parse($transaction['created_at'])->endOfMonth(),
                            ],
                            [
                                'balance' => $balance,
                            ]
                        );
                    }
                }
            }
        }

        $this->info('Month-end balances calculation complete.');
    }
}