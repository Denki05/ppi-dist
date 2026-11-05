<?php

namespace App\Http\Controllers\Api;

use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductPack;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Warehouse;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $search    = trim((string) $request->query('search'));
        $brand     = trim((string) $request->query('brand'));
        $packaging = (int) $request->query('packaging');

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

        if ($brand !== '') {
            $query->whereHas('product_pack.product', function ($product) use ($brand) {
                $product->where('brand_name', $brand);
            });
        }

        if ($packaging > 0) {
            $query->whereHas('product_pack', function ($pack) use ($packaging) {
                $pack->where('packaging_id', $packaging);
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
                'no'         => ++$no,
                'encoded_id' => $this->encodePack($pack->id),
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
                'brand'        => $brand,
                'packaging'    => $packaging ?: null,
                'brands'       => DB::table('master_brand_lokal')->orderBy('brand_name')->pluck('brand_name'),
                'packaging_options' => DB::table('master_packaging')->orderBy('pack_name')->get(['id', 'pack_name']),
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

    /**
     * Kartu stock (ledger) satu produk untuk satu bulan.
     */
    public function detail(Request $request, $warehouseId, $encoded)
    {
        $context = $this->ledgerContext($warehouseId, $encoded, $request->query('month'));

        if (isset($context['error'])) {
            return $this->fail($context['error'], $context['code']);
        }

        $warehouse = $context['warehouse'];
        $pack      = $context['pack'];
        $month     = $context['month'];
        $startDate = $context['start'];
        $endDate   = $context['end'];

        $collect = [];
        $balance = 0;

        // Sama seperti menu Trans: baris OPENING di bulan ini ditampilkan sekali.
        $openingInject = StockMove::where('warehouse_id', $warehouse->id)
            ->where('product_packaging_id', $pack->id)
            ->where('code_transaction', 'like', 'OPENING-%')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($openingInject && $openingInject->created_at->format('Y-m') === $month) {
            $balance += ((float) $openingInject->stock_in - (float) $openingInject->stock_out);
            $collect[] = [
                'date'        => $openingInject->created_at->format('d/m/Y H:i'),
                'transaction' => $openingInject->code_transaction,
                'in'          => (float) $openingInject->stock_in,
                'out'         => null,
                'balance'     => $balance,
                'description' => $openingInject->note,
            ];
        } else {
            $openingPrev = StockMove::where('warehouse_id', $warehouse->id)
                ->where('product_packaging_id', $pack->id)
                ->where('created_at', '<', $startDate)
                ->selectRaw('SUM(stock_in - stock_out) as saldo')
                ->value('saldo');

            if ((float) $openingPrev > 0) {
                $balance += (float) $openingPrev;
                $collect[] = [
                    'date'        => '-',
                    'transaction' => 'Saldo Bulan Lalu',
                    'in'          => null,
                    'out'         => null,
                    'balance'     => $balance,
                    'description' => 'Saldo berjalan dari bulan sebelumnya',
                ];
            }
        }

        $openingBalance = $balance;

        $moves = StockMove::where('warehouse_id', $warehouse->id)
            ->where('product_packaging_id', $pack->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($moves as $move) {
            if ($openingInject && $move->id === $openingInject->id) {
                continue;
            }

            $balance += ((float) $move->stock_in - (float) $move->stock_out);
            $collect[] = [
                'date'        => $move->created_at->format('d/m/Y H:i'),
                'transaction' => $move->code_transaction,
                'in'          => $move->stock_in ? (float) $move->stock_in : null,
                'out'         => $move->stock_out ? (float) $move->stock_out : null,
                'balance'     => $balance,
                'description' => $move->note,
            ];
        }

        $current = ProductMinStock::where('warehouse_id', $warehouse->id)
            ->where('product_packaging_id', $pack->id)
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'warehouse' => [
                    'id'   => (int) $warehouse->id,
                    'name' => $warehouse->name,
                ],
                'product' => [
                    'id'        => $pack->id,
                    'encoded'   => $this->encodePack($pack->id),
                    'code'      => $pack->code,
                    'name'      => optional($pack->product)->name,
                    'brand'     => optional($pack->product)->brand_name ?: '-',
                    'packaging' => optional($pack->packaging)->pack_name ?: '-',
                ],
                'month'           => $month,
                'opening_balance' => (float) $openingBalance,
                'real_balance'    => (float) ($current->quantity ?? 0),
                'rows'            => $collect,
            ],
        ]);
    }

    /**
     * Cetak Kartu Stock (PDF) untuk kombinasi gudang + produk + bulan.
     */
    public function pdf(Request $request, $warehouseId, $encoded)
    {
        $context = $this->ledgerContext($warehouseId, $encoded, $request->query('month'));

        if (isset($context['error'])) {
            return $this->fail($context['error'], $context['code']);
        }

        $warehouse = $context['warehouse'];
        $pack      = $context['pack'];
        $startDate = $context['start'];
        $endDate   = $context['end'];

        $baseQuery = StockMove::where('warehouse_id', $warehouse->id)
            ->where('product_packaging_id', $pack->id);

        $openingBalance = (clone $baseQuery)
            ->where('created_at', '<', $startDate)
            ->selectRaw('SUM(stock_in - stock_out) as saldo')
            ->value('saldo') ?? 0;

        $moves = $baseQuery
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $balance = (float) $openingBalance;
        $collect = [];

        foreach ($moves as $move) {
            $balance += ((float) $move->stock_in - (float) $move->stock_out);
            $collect[] = [
                'date'        => $move->created_at->format('d/m/Y H:i'),
                'transaction' => $move->code_transaction,
                'in'          => (float) $move->stock_in,
                'out'         => (float) $move->stock_out,
                'balance'     => $balance,
                'description' => $move->note,
            ];
        }

        $pdf = \PDF::loadView('superuser.gudang.stock.print', [
            'product'        => $pack,
            'warehouse'      => $warehouse,
            'collects'       => $collect,
            'openingBalance' => (float) $openingBalance,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
        ])->setPaper('A5', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Kartu-Stock-' . $pack->code . '.pdf"',
        ]);
    }

    /**
     * Validasi gudang, produk, dan bulan; dipakai detail + cetak PDF.
     */
    private function ledgerContext($warehouseId, $encoded, $month)
    {
        $warehouse = Warehouse::whereIn('name', ['Gudang Araya', 'Gudang QC'])
            ->find((int) $warehouseId);

        if (!$warehouse) {
            return ['error' => 'Gudang tidak ditemukan.', 'code' => 404];
        }

        $packId = $this->decodePack((string) $encoded);
        $pack   = $packId !== null ? ProductPack::with(['product', 'packaging'])->find($packId) : null;

        if (!$pack) {
            return ['error' => 'Produk tidak ditemukan.', 'code' => 404];
        }

        $month = (string) $month;
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }

        $date = Carbon::createFromFormat('Y-m-d', $month . '-01');

        return [
            'warehouse' => $warehouse,
            'pack'      => $pack,
            'month'     => $month,
            'start'     => $date->copy()->startOfMonth(),
            'end'       => $date->copy()->endOfMonth(),
        ];
    }

    private function fail($message, $code)
    {
        return response()->json(['status' => 'error', 'message' => $message], $code);
    }

    /**
     * Base64 URL-safe agar ID kemasan (string, bisa mengandung '-'/'/') aman di URL.
     */
    private function encodePack($id)
    {
        return rtrim(strtr(base64_encode((string) $id), '+/', '-_'), '=');
    }

    private function decodePack($encoded)
    {
        $base64 = strtr($encoded, '-_', '+/');
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);
        $decoded = base64_decode($base64, true);

        return $decoded === false || $decoded === '' ? null : $decoded;
    }
}
