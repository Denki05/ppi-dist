<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\DataTables\Gudang\QualityControlTable; 
use App\Entities\Master\Warehouse;
use App\Entities\Gudang\QualityControl2;
use App\Entities\Gudang\QualityControlDetail2;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use App\Repositories\CodeRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Entities\Setting\UserMenu;
use Auth;
use Excel;
use Carbon\Carbon;
use DomPDF;
use PDF;
use Validator;
use DB;

class QualityControl2Controller extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.quality_control_2.";
        $this->route = "superuser.gudang.quality_control_2";
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

    public function index()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')
                    ->with('error','Anda tidak punya akses');
            }
        }

        return view($this->view."index", [
            'warehouses' => Warehouse::where('name', 'Gudang QC')->get(),
            'customers' => CustomerOtherAddress::leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
                ->select(
                    'master_customer_other_addresses.id as customer_id',
                    DB::raw("CONCAT(master_customers.name, '  ', master_customer_other_addresses.text_kota) AS full_name")
                )
                ->where('master_customers.status', 1)
                ->get(),
            'code' => CodeRepo::generateQualityControl2Code(),
            'komplain' => QualityControl2::get()
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || $this->access->can_create == 0) {
                return redirect()->back()
                    ->with('error', 'Anda tidak punya akses menyimpan data');
            }
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'warehouse_id' => 'required|exists:master_warehouses,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {

            /** =========================
             * HEADER
             * ========================= */
            $qc = new QualityControl2();
            $qc->code = $request->code;
            $qc->warehouse_id = $request->warehouse_id;
            $qc->customer_id = $request->customer_id;
            $qc->tanggal = $request->date;
            $qc->note = $request->note;
            $qc->status = QualityControl2::STATUS['ACTIVE'];
            $qc->created_by = Auth::id();
            $qc->save();

            /** =========================
             * DETAIL
             * ========================= */
            foreach ($request->items as $item) {

                $detail = new QualityControlDetail2();
                $detail->receving_komplain_id = $qc->id;
                $detail->product_packaging_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->save();
            }

            DB::commit();

            return redirect()
                ->route('superuser.gudang.quality_control_2.index')
                ->with('success', 'Receiving komplain berhasil disimpan');

        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function getBrands()
    {
        $brands = DB::table('master_brand_lokal')
            ->select('brand_name')
            ->orderBy('brand_name')
            ->get();

        return response()->json($brands);
    }

    public function getProductsByBrand(Request $request)
    {
        $request->validate([
            'brand_name' => 'required'
        ]);

        $products = DB::table('master_products')
            ->join('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->join('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->select('master_products_packaging.id', 'master_products_packaging.code', 'master_products_packaging.name', 'master_packaging.pack_name as packaging_name')
            ->where('master_products.brand_name', $request->brand_name)
            ->orderBy('master_products_packaging.name')
            ->get();

        return response()->json($products);
    }   

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if (Auth::user()->is_superuser == 0) {
                if (empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Anda tidak punya akses untuk membuka menu terkait'
                    ]);
                }
            }

            DB::beginTransaction();
            try {

                $qc = QualityControl2::with('details')->findOrFail($id);

                if ($qc->status == QualityControl2::STATUS['ACC']) {
                    throw new \Exception('Data sudah di ACC');
                }

                foreach ($qc->details as $detail) {

                    $qty       = floatval($detail->qty);
                    $productId = $detail->product_packaging_id;

                    /**
                     * =====================================================
                     * 1. IN ke Gudang Araya (warehouse_id = 2)
                     * =====================================================
                     */
                    $stockAraya = ProductMinStock::where('product_packaging_id', $productId)
                        ->where('warehouse_id', 2)
                        ->lockForUpdate()
                        ->first();

                    if (!$stockAraya) {
                        throw new \Exception('Stok Gudang Araya tidak ditemukan');
                    }

                    $stockAraya->quantity += $qty;
                    $stockAraya->save();

                    StockMove::create([
                        'code_transaction'     => $qc->code,
                        'warehouse_id'         => 2,
                        'product_packaging_id' => $productId,
                        'stock_in'             => $qty,
                        'stock_out'            => 0,
                        'stock_balance'        => $stockAraya->quantity,
                        'note'                 => 'Receiving komplain - masuk gudang Araya',
                        'created_by'           => Auth::id(),
                    ]);

                    /**
                     * =====================================================
                     * 2. OUT dari Gudang Araya (transfer ke QC)
                     * =====================================================
                     */
                    $stockAraya->quantity -= $qty;
                    $stockAraya->save();

                    StockMove::create([
                        'code_transaction'     => $qc->code,
                        'warehouse_id'         => 2, // tetap Araya
                        'product_packaging_id' => $productId,
                        'stock_in'             => 0,
                        'stock_out'            => $qty,
                        'stock_balance'        => $stockAraya->quantity,
                        'note'                 => 'Transfer ke gudang QC',
                        'created_by'           => Auth::id(),
                    ]);

                    /**
                     * =====================================================
                     * 3. IN ke Gudang QC (warehouse_id = 3)
                     * =====================================================
                     */
                    $stockQc = ProductMinStock::where('product_packaging_id', $productId)
                        ->where('warehouse_id', $qc->warehouse_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stockQc) {
                        $stockQc = ProductMinStock::create([
                            'product_packaging_id' => $productId,
                            'warehouse_id'         => $qc->warehouse_id,
                            'unit_id'              => $stockAraya->unit_id ?? 1,
                            'quantity'             => 0,
                            'selling_price'        => 0,
                        ]);
                    }

                    $stockQc->quantity += $qty;
                    $stockQc->save();

                    StockMove::create([
                        'code_transaction'     => $qc->code,
                        'warehouse_id'         => $qc->warehouse_id,
                        'product_packaging_id' => $productId,
                        'stock_in'             => $qty,
                        'stock_out'            => 0,
                        'stock_balance'        => $stockQc->quantity,
                        'note'                 => 'Receiving komplain - masuk gudang QC',
                        'created_by'           => Auth::id(),
                    ]);
                }

                /**
                 * =====================================================
                 * Update status QC
                 * =====================================================
                 */
                $qc->update([
                    'status' => QualityControl2::STATUS['ACC']
                ]);

                DB::commit();
                $response['redirect_to'] = route('superuser.gudang.quality_control_2.index');
                return $this->response(200, $response);
                
            } catch (\Exception $e) {
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $failed,
                ];
    
                return $this->response(400, $response);
            }
        }
    }

    public function pdf_sj_komplain($id)
    {
        $result = QualityControl2::find($id);
        if (!$result) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.gudang.quality_control_2.pdf_sj_komplain', $data)
                ->setPaper('a5', 'landscape');

        $generate = false; // Ubah sesuai logika bisnis.

        if ($generate) {
            return $pdf->download("{$result->code}.pdf");
        }

        return $pdf->stream("{$result->code}.pdf");
    }
}