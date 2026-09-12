<?php

namespace App\Http\Controllers\Superuser\Accounting;

use App\DataTables\Accounting\ProductFinanceTable;
use App\Exports\Accounting\ProductFinanceImportTemplate;
use App\Exports\Finance\ProductFinanceExport;
use App\Http\Controllers\Controller;
use App\Imports\Accounting\ProductFinanceImport;
use App\Services\Accounting\ProductFinanceService;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Excel;

class ProductFinanceController extends Controller
{
    protected $view = 'superuser.accounting.product_finance.';
    protected $route = 'superuser.accounting.product_finance';
    protected $service;
    protected $access;

    public function __construct(ProductFinanceService $service)
    {
        $this->service = $service;
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $this->access = \App\Entities\Setting\UserMenu::where('user_id', $user->id)
                ->whereHas('menu', function ($q) {
                    $q->where('route_name', $this->route);
                })
                ->first();

            return $next($request);
        });
    }

    private function needAccess(string $ability)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->{$ability} == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return null;
    }

    public function json(Request $request, ProductFinanceTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if ($deny = $this->needAccess('can_read')) {
            return $deny;
        }

        return view($this->view . 'index', $this->service->indexData());
    }

    public function create()
    {
        if ($deny = $this->needAccess('can_create')) {
            return $deny;
        }

        return view($this->view . 'create', $this->service->createData());
    }

    public function store(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'brand' => 'required|string|max:225',
            'product' => 'required|string|max:50|exists:master_products_packaging,id',
            'packaging_code' => 'required|integer|exists:master_packaging,id',
            'mitra_id' => 'required|integer|exists:master_mitra,id',
            'harga_beli_satuan' => 'required|numeric|min:0|max:999999999999.99',
            'harga_jual_satuan' => 'required|numeric|min:0|max:999999999999.99',
        ]);

        if ($validator->fails()) {
            return $this->response(400, [
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ],
            ]);
        }

        $result = $this->service->store($request->only([
            'brand', 'product', 'packaging_code', 'mitra_id', 'harga_beli_satuan', 'harga_jual_satuan',
        ]));

        if ($result['status'] !== 'ok') {
            $message = $result['errors'][0] ?? 'Gagal menyimpan data.';

            if (in_array($result['status'], ['not_found', 'exists'])) {
                return response()->json(['status' => 400, 'errors' => $message], 400);
            }

            return $this->response(400, [
                'notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $message],
            ]);
        }

        return $this->response(200, [
            'notification' => ['alert' => 'notify', 'type' => 'success', 'content' => 'Success'],
            'redirect_to' => route('superuser.accounting.product_finance.index'),
        ]);
    }

    public function show()
    {
    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }

    public function export(Request $request)
    {
        if ($deny = $this->needAccess('can_read')) {
            return $deny;
        }

        $mitraId = $request->input('mitra_id');
        if ($mitraId !== null && ! ctype_digit((string) $mitraId)) {
            abort(400, 'Mitra tidak valid.');
        }

        $suffix = $mitraId ? '-mitra-' . (int) $mitraId : '';

        return Excel::download(
            new ProductFinanceExport($mitraId),
            'master-product-finance' . $suffix . '-' . date('d-m-Y_H-i-s') . '.xlsx'
        );
    }

    public function import_template()
    {
        return Excel::download(new ProductFinanceImportTemplate, 'product-finance-import-template.xlsx');
    }

    public function import(Request $request)
    {
        if ($deny = $this->needAccess('can_create')) {
            return $deny;
        }

        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        $import = new ProductFinanceImport();
        Excel::import($import, $request->file('import_file'));

        return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
    }

    public function get_product(Request $request)
    {
        $products = $this->service->productsByBrand($request->input('brand_name'));

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found for this brand'], 404);
        }

        return response()->json($products);
    }

    public function updatePrice(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|max:50|regex:/^[A-Za-z0-9\/\-.]+$/|exists:master_product_finance,id',
            'buying_price' => 'required|numeric|min:0|max:999999999999.99',
            'selling_price' => 'required|numeric|min:0|max:999999999999.99',
        ]);

        $result = $this->service->updatePrice(
            $validated['id'],
            $validated['buying_price'],
            $validated['selling_price'],
            Auth::id()
        );

        $http = $result['status'] === 'ok' ? 200 : ($result['status'] === 'not_found' ? 404 : 500);

        return response()->json([
            'status' => $http,
            'message' => $result['message'],
        ], $http);
    }

    public function history($id)
    {
        if ($deny = $this->needAccess('can_read')) {
            return $deny;
        }

        if (! is_string($id) || strlen($id) > 50 || ! preg_match('/^[A-Za-z0-9\/\-.]+$/', $id)) {
            abort(400, 'ID tidak valid.');
        }

        $result = $this->service->priceHistory($id);

        if ($result['status'] === 'not_found') {
            return response()->json(['status' => 404, 'message' => 'Data tidak ditemukan.'], 404);
        }

        return response()->json(['status' => 200, 'data' => $result]);
    }

    public function toggleStatus(Request $request)
    {
        if ($deny = $this->needAccess('can_create')) {
            return $deny;
        }

        $validated = $request->validate([
            'id' => 'required|string|max:50|regex:/^[A-Za-z0-9\/\-.]+$/|exists:master_product_finance,id',
        ]);

        $result = $this->service->toggleStatus($validated['id']);

        if ($result['status'] === 'not_found') {
            return response()->json(['status' => 404, 'message' => 'Data tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => $result['active'] ? 'Produk diaktifkan.' : 'Produk dinonaktifkan.',
            'active' => $result['active'],
        ]);
    }
}
