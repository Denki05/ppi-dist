<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\DataTables\Penjualan\SaleReturnTable;
use App\Entities\Master\Product;
use App\Entities\Master\ProductPack;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SaleReturn;
use App\Entities\Penjualan\SaleReturnDetail;
use App\Entities\Penjualan\SaleReturnCost;
use App\Entities\Gudang\QualityControl;
use App\Entities\Gudang\QualityControlDetail;
// use App\Entities\GUdang\ReceivingDetail;
use App\Entities\Master\Warehouse;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use App\Entities\Setting\UserMenu;
use App\Entities\Account\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use PDF;
use setasign\Fpdi\Fpdi;
use iio\libmergepdf\Merger;

class SaleReturnController extends Controller
{
    public function __construct(){
        $this->view = "superuser.penjualan.sale_return.";
        $this->route = "superuser.penjualan.sale_return";
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
    
    public function search_do(Request $request)
    {
        $delivery_orders = PackingOrder::where('do_code', 'LIKE', $request->input('q', '') . '%')
            ->where('status', 6)
            ->get();

        $results = [];

        foreach ($delivery_orders as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->do_code,
            ];
        }

        return ['results' => $results];
    }

    public function get_product(Request $request)
    {
        if ($request->ajax()) {
            $data = [];

            $delivery_order_detail = PackingOrder::find($request->id);
            $sale_return = SaleReturn::where('do_id', $request->id)
                                     ->where('status', SaleReturn::STATUS['ACC'])
                                     ->get();



            foreach ($delivery_order_detail->do_detail as $key => $value) {
                $qty = $value->qty;

                foreach($sale_return as $item){
                    foreach($item->sale_return_details as $val){
                        if($val->product_packaging_id == $value->product_packaging_id){
                            $value->qty -= $val->qty;
                        }
                    }
                }

                // kalkulasi dalam idr
                $price = $value->price;
                $disc_usd = $value->usd_disc;
                $jumlah = (($value->price - $value->usd_disc) * $value->qty) * $delivery_order_detail->idr_rate;

                // DO Details (cost)
                $discount_percent = $delivery_order_detail->do_detail_cost->discount_1 ?? 0;
                $discount_kemasan = $delivery_order_detail->do_detail_cost->discount_2 ?? 0;
                $discount_idr = $delivery_order_detail->do_detail_cost->discount_idr ?? 0;


                if($value->qty > 0){
                    $data[] = [
                        'id' => $value->product_packaging_id,
                        'sku' => $value->product_pack->code,
                        'name' => $value->product_pack->name,
                        'kemasan' => $value->product_pack->packaging->pack_name,
                        'quantity' => $value->qty,
                        'acuan' => $price,
                        'disc_usd' => $disc_usd,
                        'idr_rate' => $delivery_order_detail->idr_rate,
                        'jumlah' => $jumlah,
                        'discount_percent' => $discount_percent,
                        'discount_kemasan' => $discount_kemasan,
                        'discount_idr' => $discount_idr,
                    ];
                }
            }

            return response()->json(['code' => 200, 'data' => $data]);
        }
    }

    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $sales_retun = SaleReturn::get();
        $salesOrder = PackingOrder::with('member')->where('penjualan_do.status', 2)->where('created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString())->get();

        $data = [
            'sales_return' => $sales_retun,
            'salesOrder'   => $salesOrder,
        ];

        return view('superuser.penjualan.sale_return.index', $data);
    }

    public function create()
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_create == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouses'] = Warehouse::get();

        // dd($data);

        return view('superuser.penjualan.sale_return.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:penjualan_retur,code',
                'delivery_order' => 'required',
                'type' => 'required|integer',
                'return_date' => 'nullable',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];

                return $this->response(400, $response);
            }

            DB::beginTransaction();
            try {
                $checkPayment = DB::table('finance_invoicing')
                    ->select(
                        DB::raw("
                            CASE
                                WHEN SUM(finance_payable_detail.total) IS NULL THEN 'BELUM'
                                WHEN SUM(finance_payable_detail.total) >= finance_invoicing.grand_total_idr THEN 'LUNAS'
                                ELSE 'SEBAGIAN'
                            END AS status_pembayaran
                        ")
                    )
                    ->leftJoin('finance_payable_detail', 'finance_invoicing.id', '=', 'finance_payable_detail.invoice_id')
                    ->leftJoin('finance_payable', 'finance_payable_detail.payable_id', '=', 'finance_payable.id')
                    ->leftJoin('penjualan_do', 'finance_invoicing.do_id', '=', 'penjualan_do.id')
                    ->where('penjualan_do.id', $request->delivery_order)
                    ->groupBy('finance_invoicing.id', 'finance_invoicing.grand_total_idr')
                    ->first();

                // Jika tidak ada data pembayaran, asumsikan BELUM bayar
                $paymentStatus = $checkPayment->status_pembayaran ?? 'BELUM';

                $getDo = PackingOrder::find($request->delivery_order);

                $sale_return = new SaleReturn;

                $sale_return->code = $request->code;
                $sale_return->do_id = $request->delivery_order;
                $sale_return->idr_rate = $getDo->idr_rate;
                $sale_return->type = $request->type;
                $sale_return->payment_status = $paymentStatus === 'LUNAS'
                    ? SaleReturn::PAYMENT_STATUS['LUNAS']
                    : SaleReturn::PAYMENT_STATUS['BELUM LUNAS'];
                $sale_return->customer_other_address_id = $getDo->customer_other_address_id;
                $sale_return->status = SaleReturn::STATUS['ACTIVE'];

                if ($sale_return->save()) {
                    if ($request->sku) {
                        foreach ($request->sku as $key => $value) {
                            if (!empty($request->sku[$key]) && !empty($request->quantity[$key])) {
                                $sale_return_detail = new SaleReturnDetail;
                                $sale_return_detail->retur_id = $sale_return->id;
                                $sale_return_detail->product_packaging_id = $request->sku[$key];
                                $sale_return_detail->qty = $request->quantity[$key];
                                $sale_return_detail->price = $request->acuan[$key];
                                $sale_return_detail->disc_usd = $request->disc_usd[$key];
                                $sale_return_detail->note = $request->description[$key] ?? null;
                                $sale_return_detail->save();
                            }
                        }
                    }

                    // tambah retur cost
                    $sale_return_cost = new SaleReturnCost;
                    $sale_return_cost->retur_id = $sale_return->id;
                    $sale_return_cost->discount_1 = $request->disc_amount_1 ?? 0;
                    $sale_return_cost->discount_2 = $request->disc_amount_2 ?? 0;
                    $sale_return_cost->discount_idr = $request->disc_idr ?? 0;
                    $sale_return_cost->purchase_total_idr  = $request->grand_total ?? 0;
                    $sale_return_cost->save();

                    // tambahakan langsung receiving
                    $receiving = new QualityControl;

                    $receiving->code = $sale_return->code;
                    $receiving->type = QualityControl::TYPE['RETURN'];
                    $receiving->warehouse_id = 2;
                    $receiving->status = QualityControl::STATUS['QC'];
                    $receiving->save();

                    // tambahkan detail receiving
                    foreach ($sale_return->sale_return_details as $detail) {
                        $receiving_detail = new QualityControlDetail;
                        $receiving_detail->receiving_id = $receiving->id;
                        $receiving_detail->po_id = $sale_return->id;
                        $receiving_detail->product_packaging_id = $detail->product_packaging_id;
                        $receiving_detail->quantity_po = $detail->qty;
                        $receiving_detail->note = $detail->note;
                        $receiving_detail->save();
                    }

                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.penjualan.sale_return.index');

                    return $this->response(200, $response);
                } else {
                    DB::rollBack();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => ['Gagal menyimpan data retur penjualan.'],
                    ];
                    return $this->response(400, $response);
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => [$e->getMessage()],
                ];
                return $this->response(500, $response);
            }
        }
    }

    public function show($id)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_show == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['sale_return'] = SaleReturn::findOrFail($id);

        return view('superuser.penjualan.sale_return.show', $data);
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('sale return-edit')) {
            return abort(403);
        }

        $data['sale_return'] = SaleReturn::findOrFail($id);

        return view('superuser.penjualan.sale_return.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $sale_return = SaleReturn::find($id);

            if ($sale_return == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:penjualan_retur,code,'. $sale_return->id,
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];

                return $this->response(400, $response);
            }

            if ($validator->passes()) {

                $sale_return->code = $request->code;

                if ($sale_return->save()) {
                    SaleReturnDetail::where('retur_id', $sale_return->id)->delete();
                    if ($request->sku) {
                        foreach ($request->sku as $key => $value) {
                            if ($request->sku[$key] && $request->quantity[$key]) {

                                $sale_return_detail = new SaleReturnDetail;
                                $sale_return_detail->retur_id = $sale_return->id;
                                $sale_return_detail->product_packaging_id = $request->sku[$key];
                                $sale_return_detail->qty = $request->quantity[$key];
                                $sale_return_detail->note = $request->description[$key];
                                $sale_return_detail->save();
                            }
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.penjualan.sale_return.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function acc($id)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $sale_return = SaleReturn::find($id);

        if ($sale_return === null) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            // check apakah sudah acc untuk QC nya?
            $getReceivingDetail = QualityControlDetail::where('po_id', $sale_return->id)->first();
            if ($getReceivingDetail) {
                if ($getReceivingDetail->receiving->status != QualityControl::STATUS['ACC']) {
                    return redirect()->route('superuser.penjualan.sale_return.index')
                        ->with('error', 'Proses QC sedang berlangsung, tidak bisa melanjutkan proses');
                }
            }

            $sale_return->retur_date = now();
            $sale_return->status = SaleReturn::STATUS['ACC'];
            $sale_return->fat_status = SaleReturn::FAT_STATUS['NONE'];
            $sale_return->save();

            DB::commit();
            return redirect()->route('superuser.penjualan.sale_return.index')
                 ->with('success', 'Data retur berhasil di Acc.');
        } catch (\Throwable $e) {
            dd($e);
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat acc data');
        }
    }

    public function proses($id)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $sale_return = SaleReturn::find($id);

        if ($sale_return === null) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            $sale_return->status = SaleReturn::STATUS['PROSES'];
            $sale_return->save();

            DB::commit();
            return redirect()->route('superuser.penjualan.sale_return.index')
                 ->with('success', 'Retur Berhasil di Proses.');
        } catch (\Throwable $e) {
            dd($e);
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat acc data');
        }
    }

    public function destroy(Request $request, $id)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $sale_return = SaleReturn::find($id);

        if ($sale_return === null) {
            abort(404);
        }

        // check proses QC jika sudah ACC atau sudah dibuat receiving
        $getReceivingDetail = QualityControlDetail::where('po_id', $sale_return->id)->first();
        if ($getReceivingDetail) {
            if ($getReceivingDetail->receiving->status === QualityControl::STATUS['ACTIVE']) {
                return redirect()->route('superuser.penjualan.sale_return.index')
                    ->with('error', 'Proses QC sedang berlangsung, tidak bisa menghapus data ini');
            }
        }

        DB::beginTransaction();
        try {

            $sale_return->delete();

            $sale_retur_cost = SaleReturnCost::where('retur_id', $sale_return->id)->delete();

            // Delete related SaleReturnDetails
            foreach ($sale_return->sale_return_details as $detail) {
                $detail->delete();
            }

            // back status invoice
            DB::table('finance_invoicing')->where('do_id', $sale_return->do_id)
                        ->update(['status' => 1]);

            DB::commit();
            return redirect()->route('superuser.penjualan.sale_return.index')
                 ->with('success', 'Data retur berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data');
        }
    }

    public function pdf($id, $returnBinary = false)
    {
        $result = SaleReturn::find($id);
        if (!$result) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.penjualan.sale_return.pdf', $data)
                ->setPaper('a5', 'landscape');

        if ($returnBinary) {
            return $pdf->output(); // hasil biner PDF
        }

        return $pdf->stream("{$result->code}.pdf");
    }

    public function pdf_tt($id, $returnBinary = false)
    {
        $result = SaleReturn::find($id);
        if (!$result) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $data = [
            'result' => $result,
            'watermark' => $result->invoice->so->payment_status == '1' ? 'PAID' : 'COPY',
        ];

        $pdf = PDF::loadView('superuser.penjualan.sale_return.pdf_tt', $data)
                ->setPaper('a5', 'landscape');

        if ($returnBinary) {
            return $pdf->output(); // hasil biner PDF
        }

        return $pdf->stream("{$result->code}.pdf");
    }

    public function pdf_sj($id)
    {
        $result = SaleReturn::find($id);
        if (!$result) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.penjualan.sale_return.pdf_sj', $data)
                ->setPaper('a5', 'landscape');

        $generate = false; // Ubah sesuai logika bisnis.

        if ($generate) {
            return $pdf->download("{$result->code}.pdf");
        }

        return $pdf->stream("{$result->code}.pdf");
    }

    public function pdf_tt_fat($id)
    {
        $result = SaleReturn::find($id);
        if (!$result) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.penjualan.sale_return.nota_kredit_fat', $data)
                ->setPaper('a5', 'landscape');

        $generate = false; // Ubah sesuai logika bisnis.

        if ($generate) {
            return $pdf->download("{$result->code}.pdf");
        }

        return $pdf->stream("{$result->code}.pdf");
    }

    public function pdf_refund($id)
    {
        $result = SaleReturn::find($id);
        if (!$result) {
            abort(404, 'Tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.penjualan.sale_return.print_nota_refund', $data)
                ->setPaper('a5', 'landscape');

        $generate = false;

        if ($generate) {
            return $pdf->download("{$result->code}.pdf");
        }

        return $pdf->stream("{$result->code}.pdf");
    }

    public function mergePdf($invoice, $retur)
    {
        // 1. panggil controller nota tt balde
        $notaTT = app(\App\Http\Controllers\Superuser\Penjualan\SaleReturnController::class)->pdf_tt($retur, true);

        // 2. Panggil controller invoicing untuk generate PDF invoice awal
        $invoicePdf = app(\App\Http\Controllers\Superuser\Finance\InvoicingController::class)->download_invoice_merge($invoice, true);

        // 3. Panggil controller retur untuk generate PDF nota kredit
        $returPdf = app(\App\Http\Controllers\Superuser\Penjualan\SaleReturnController::class)->pdf($retur, true);

        // 4. Merge
        $merger = new Merger;
        $merger->addRaw($notaTT); // data biner PDF
        $merger->addRaw($invoicePdf); // data biner PDF
        $merger->addRaw($returPdf);

        $createdPdf = $merger->merge();

        // 4. Stream ke browser (tanpa simpan file)
        return response($createdPdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="nota-tt.pdf"');
    }
}