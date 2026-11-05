<?php

namespace App\Http\Controllers\Api;

use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Warehouse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Daftar stock per gudang untuk modul WMS (read-only).
 * Satu request mengembalikan daftar gudang, baris, dan paginasi.
 */
class WmsStockController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::whereIn('name', ['Gudang Araya', 'Gudang QC'])
            ->orderBy('name')
            ->get(['id', 'name']);

        $warehouseId = (int) $request->query('warehouse_id');
        if (!in_array($warehouseId, $warehouses->pluck('id')->all(), true)) {
            $warehouseId = (int) ($warehouses->first()->id ?? 0);
        }

        $search = trim((string) $request->query('search'));

        $query = ProductMinStock::with([
                'product_pack.product',
                'product_pack.packaging',
                'product_pack.type_product_pack',
            ])
            ->where('warehouse_id', $warehouseId);

        if ($search !== '') {
            $query->whereHas('product_pack', function ($pack) use ($search) {
                $pack->where('code', 'like', '%' . $search . '%')
                    ->orWhereHas('product', function ($product) use ($search) {
                        $product->where('name', 'like', '%' . $search . '%')
                            ->orWhere('brand_name', 'like', '%' . $search . '%');
                    });
            });
        }

        $paginator = $query->orderBy('product_packaging_id')->paginate(25);

        // Saldo KS = stock_balance dari pergerakan terakhir tiap kemasan.
        $packIds = collect($paginator->items())
            ->pluck('product_packaging_id')
            ->filter()
            ->unique()
            ->values();

        $lastMoves = StockMove::where('warehouse_id', $warehouseId)
            ->whereIn('product_packaging_id', $packIds)
            ->orderBy('id', 'desc')
            ->get(['product_packaging_id', 'stock_balance'])
            ->unique('product_packaging_id')
            ->keyBy('product_packaging_id');

        $rows = [];
        $no = ($paginator->currentPage() - 1) * $paginator->perPage();

        foreach ($paginator as $stock) {
            $pack = $stock->product_pack;
            if (!$pack || !$pack->product) {
                continue;
            }

            $rows[] = [
                'no'        => ++$no,
                'code'      => $pack->code,
                'product'   => $pack->product->name,
                'brand'     => $pack->product->brand_name ?: '-',
                'packaging' => $pack->packaging->pack_name ?? '-',
                'type'      => $pack->type_product_pack->name ?? '-',
                'stock'     => (float) $stock->quantity,
                'ks'        => $lastMoves->has($pack->id)
                    ? (float) $lastMoves[$pack->id]->stock_balance
                    : 0,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'warehouses'   => $warehouses,
                'warehouse_id' => $warehouseId,
                'search'       => $search,
                'rows'         => $rows,
                'meta'         => [
                    'page'      => $paginator->currentPage(),
                    'per_page'  => $paginator->perPage(),
                    'total'     => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        ]);
    }
}
