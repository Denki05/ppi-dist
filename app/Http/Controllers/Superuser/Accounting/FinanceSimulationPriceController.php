<?php

namespace App\Http\Controllers\Superuser\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Accounting\PackingOrderUv;
use App\Entities\Accounting\PackingOrderDetailUv;
use App\Entities\Accounting\PackingOrderItemUv;
use App\Entities\Accounting\InvoiceTaxUv;
use App\Entities\Accounting\InvoiceTax;
use App\Entities\Accounting\InvoiceTaxDetailUv;
use App\Entities\Accounting\InvoiceTaxDetail;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Master\ProductFinance;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Finance\CashbackUv;
use App\Entities\Finance\CashbackItemUv;
use App\Entities\Master\Mitra;
use App\Entities\Master\MitraDetail;
use App\Entities\Master\MitraSetting;
use App\Entities\Reports\CustomerTypeBrandReportsLog;
use App\Entities\Setting\UserMenu;
use Validator;
use Carbon\Carbon;
use Auth;
use PDF;
use DB;

class FinanceSimulationPriceController extends Controller
{
    public function __construct()
    {
        $this->view = "superuser.accounting.finance_simulation.";
        $this->route = "superuser.accounting.finance_simulation";
        $this->user_menu = new UserMenu;
        $this->access = null;

        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $this->access = $this->user_menu->where('user_id', $user->id)
                ->whereHas('menu', function ($query) {
                    $query->where('route_name', $this->route);
                })
                ->first();

            return $next($request);
        });
    }

    public function getInvoices(Request $request)
    {
        $year = $request->year;
        $month = $request->month;

        // Pastikan parameter dikirim
        if (!$year || !$month) {
            return response()->json(['error' => 'Tahun dan bulan diperlukan'], 400);
        }

        $invoices = PackingOrder::where('status', 6)
            ->where('uv_araya', 0)
            ->where('uv_unifra', 0)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get(['id', 'do_code']);

        return response()->json($invoices);
    }
    
    // Araya
    public function index_araya(Request $request)
    {
        // Akses kontrol
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Ambil bulan dan tahun dari request, jika kosong pakai bulan & tahun berjalan
        $year = $request->has('year') ? intval($request->year) : date('Y');
        $month = $request->has('month') ? intval($request->month) : date('m');

        $list = DB::table('penjualan_do')
            ->where('penjualan_do.status', 6)
            ->whereMonth('penjualan_do.created_at', $month)
            ->whereYear('penjualan_do.created_at', $year)
            ->where('master_customers.has_ppn', 0)
            ->leftJoin('penjualan_do_uv', 'penjualan_do.id', '=', 'penjualan_do_uv.do_id')
            ->leftJoin('finance_cashback_uv', 'penjualan_do_uv.id', '=', 'finance_cashback_uv.do_uv_id')
            ->leftJoin('finance_cashback_detail_uv', 'finance_cashback_uv.id', '=', 'finance_cashback_detail_uv.cashback_uv_id')
            ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
            ->select([
                'penjualan_do.id as id',
                'penjualan_do.do_code as invoice_code',
                'penjualan_do.type_transaction as invoice_type',
                'penjualan_do.uv_araya as status_uv_araya',
                'penjualan_do_uv.id as invoice_uv_id',
                'penjualan_do_uv.code as invoice_code_uv',
                'finance_cashback_uv.code as cashback_code',
                'master_customer_other_addresses.name as customer_name',
                'master_customer_other_addresses.text_kota as customer_city',
                DB::raw('COALESCE(SUM(finance_cashback_detail_uv.subtotal_item_idr), 0) as total_idr'),
                DB::raw('COALESCE(SUM(finance_cashback_detail_uv.amount_cashback_idr), 0) as total_cashback_idr')
            ])
            ->groupBy('penjualan_do.id', 'penjualan_do.do_code', 'penjualan_do.type_transaction', 'penjualan_do.uv_araya',
                    'penjualan_do_uv.id', 'penjualan_do_uv.code', 'finance_cashback_uv.code',
                    'master_customer_other_addresses.name', 'master_customer_other_addresses.text_kota')
            ->get();

        $data = [
            'list' => $list,
            'year' => $year,
            'month' => $month,
        ];

        return view($this->view . 'index_araya', $data);
    }


    public function create_araya(Request $request, $id)
    {
        // Access
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_create == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $invoice = PackingOrder::find($id);

        $get_product = PackingOrder::where('penjualan_do.id', $id)
            ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
            ->leftJoin('penjualan_do_item', 'penjualan_do.id', '=', 'penjualan_do_item.do_id')
            ->leftJoin('master_products_packaging', 'penjualan_do_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_product_category_types', 'master_products_packaging.id', '=', 'master_product_category_types.product_packaging_id')
            ->leftJoin('master_product_finance', 'master_products_packaging.id', '=', 'master_product_finance.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
            ->leftJoin('penjualan_so_item', function($join) {
                $join->on('penjualan_so.id', '=', 'penjualan_so_item.so_id')
                    ->on('penjualan_do_item.product_packaging_id', '=', 'penjualan_so_item.product_packaging_id'); // Menghindari duplikasi dengan kondisi tambahan
            })
            ->select([
                'master_products_packaging.id as id_produk',
                'master_products_packaging.code as kode_produk',
                'master_products_packaging.name as nama_produk',
                'master_products_packaging.price as harga_produk',
                'master_packaging.pack_name as kemasan',
                'master_product_category_types.fee as cashback',
                'master_product_finance.selling_price_usd_unit as harga_jual_tax',
                'master_product_finance.buying_price_usd_unit as harga_beli_tax',
                'penjualan_do_item.qty as qty',
                'penjualan_do_item.usd_disc as usd_disc',
                'penjualan_do_details.discount_1 as discount_1',
                'penjualan_do_details.discount_2 as discount_2',
                'penjualan_do_details.discount_1_idr as discount_1_idr',
                'penjualan_do_details.discount_2_idr as discount_2_idr',
                'penjualan_do_details.discount_idr as discount_idr',
                'penjualan_do_details.voucher_idr as voucher_idr',
                'penjualan_do_details.ppn_percent as ppn_percent',
                'penjualan_do_details.ppn_idr as ppn_idr',
                'penjualan_do.idr_rate as kurs',
                'penjualan_so_item.free_product as free_product',
            ])
            ->groupBy('master_products_packaging.id') // Mengelompokkan agar tidak duplikasi
            ->distinct() // Memastikan hasil unik
            ->get();

        // check payment
        $pembayaran = DB::table('penjualan_do')
            ->select([
                'penjualan_do.do_code as do_code',
                'penjualan_do.type_transaction as tipe_transaksi',
                'finance_invoicing.code as invoice_code',
                'finance_invoicing.created_at AS tanggal_invoice',
                'finance_invoicing.grand_total_idr as total_tagihan',
                'finance_payable.code as kode_pembayaran',
                'finance_payable.pay_date AS tangal_pembayaran',
                'finance_payable.updated_by AS proses_by',
                DB::raw('COALESCE(SUM(finance_payable_detail.total), 0) as total_pembayaran'),
                DB::raw("
                    CASE 
                        WHEN finance_invoicing.grand_total_idr = COALESCE(SUM(finance_payable_detail.total), 0) 
                        THEN 'LUNAS' 
                        ELSE 'BELUM LUNAS' 
                    END as status_pembayaran
                ")
            ])
            ->leftJoin('finance_invoicing', 'penjualan_do.id', '=', 'finance_invoicing.do_id')
            ->leftJoin('finance_payable_detail', 'finance_invoicing.id', '=', 'finance_payable_detail.invoice_id')
            ->leftJoin('finance_payable', 'finance_payable_detail.payable_id', '=', 'finance_payable.id')
            ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
            ->where('penjualan_do.id', $id)
            ->groupBy(
                'penjualan_do.code',
                'penjualan_do.type_transaction',
                'finance_invoicing.code',
                'finance_invoicing.grand_total_idr',
                'finance_payable.code'
            )
            ->first();

        // dd($pembayaran);

        $data = [
            'invoice' => $invoice,
            'get_product' => $get_product,
            'pembayaran' => $pembayaran,
        ];


        return view($this->view . 'create_araya', $data);
    }

    public function store_araya(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'invoice_id' => 'required|exists:penjualan_do,id',
                'code_jual' => 'required|string',
                'type_transaction' => 'required|string',
                'product_jual' => 'nullable|array',
                'product_jual.*' => 'exists:master_products_packaging,id',
                'item_qty_jual.*' => 'numeric|min:0.1',
                'item_price_nett_jual.*' => 'string|min:0',
                'disc_usd_jual.*' => 'nullable|numeric|min:0',
                'item_purchase_total_jual.*' => 'numeric|min:0',
                'product_beli' => 'nullable|array',
                'product_beli.*' => 'exists:master_products_packaging,id',
                'item_qty_beli.*' => 'numeric|min:0.1',
                'item_price_beli.*' => 'numeric|min:0',
                'cashback_beli.*' => 'nullable|numeric|min:0',
                'item_grand_total_beli.*' => 'string|min:0',
            ]);

            if ($validator->fails()) {
                return $this->response(400, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $validator->errors()->all(),
                    ]
                ]);
            }

            $invoice_real = PackingOrder::find($request->invoice_id);
            if (!$invoice_real) {
                return $this->response(404, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Invoice tidak ditemukan',
                    ]
                ]);
            }

            DB::beginTransaction();
            try {
                // dd($request->code_jual);
                $do_uv = new PackingOrderUv();
                $do_uv->id_uv = $invoice_real->id;
                $do_uv->code = $request->code_jual;
                $do_uv->customer_other_address_id = $invoice_real->customer_other_address_id;
                $do_uv->do_id = $invoice_real->id;
                $do_uv->idr_rate = $invoice_real->idr_rate;
                $do_uv->transaksi = $request->type_transaction;
                $do_uv->proses_by = Auth::user()->id;
                $do_uv->proses_at = Carbon::now();
                $do_uv->payment_status = $request->payable_status;
                $do_uv->payment_date = $request->payable_date ?? null;
                $do_uv->payment_proses = $request->payable_proses ?? null;
                $do_uv->status = 1;
                $do_uv->invoice_date = $request->invoice_date ?? null;
                $do_uv->save();

                // kembalikan ke type decimal
                $grand_total_jual = str_replace(',', '.', str_replace('.', '', $request->grand_total_jual));

                // dd($request->disc_kemasan_idr_jual);

                $do_uv_detail = new PackingOrderDetailUv();
                $do_uv_detail->do_uv_id = $do_uv->id;
                $do_uv_detail->disc_1 = $request->disc_percent_jual;
                $do_uv_detail->disc_2 = $request->disc_kemasan_jual;
                $do_uv_detail->disc_1_idr = $request->disc_percent_idr_jual;
                $do_uv_detail->disc_2_idr = $request->disc_kemasan_idr_jual;
                $do_uv_detail->disc_idr = $request->disc_tambahan_jual;
                $do_uv_detail->voucher_idr = $request->voucher_jual;
                $do_uv_detail->grand_total_idr = $grand_total_jual;
                $do_uv_detail->save();

                if ($request->product_jual) {
                    foreach ($request->product_jual as $key => $value) {
                        // kembalikan ke type decimal
                        $item_price_nett_jual = str_replace(',', '.', str_replace('.', '', $request->item_price_nett_jual[$key]));

                        $do_uv_item = new PackingOrderItemUv();
                        $do_uv_item->do_uv_id = $do_uv->id;
                        $do_uv_item->product_packaging_id = $value;
                        $do_uv_item->qty = $request->item_qty_jual[$key];
                        $do_uv_item->free = $request->free_jual[$key];
                        $do_uv_item->price_jual = $request->item_price_jual[$key];
                        $do_uv_item->price_beli = $request->item_price_beli[$key];
                        $do_uv_item->usd_disc = $request->disc_usd_jual[$key];
                        $do_uv_item->total = $item_price_nett_jual;
                        $do_uv_item->save();
                    }
                }

                $cashback_uv = new CashbackUv();
                $cashback_uv->code = $request->code_beli;
                $cashback_uv->customer_other_address_id = $invoice_real->customer_other_address_id;
                $cashback_uv->do_uv_id = $do_uv->id;
                $cashback_uv->idr_rate = $invoice_real->idr_rate;
                $cashback_uv->note = $request->note_beli;
                $cashback_uv->status = 1;
                $cashback_uv->save();

                if ($request->product_beli) {
                    foreach ($request->product_beli as $key => $value) {
                        // kembalikan ke type decimal
                        $item_grand_total_beli = str_replace(',', '.', str_replace('.', '', $request->item_grand_total_beli[$key]));
                        $item_price_nett_jual_2 = str_replace(',', '.', str_replace('.', '', $request->item_price_nett_jual[$key]));

                        $cashback_uv_detail = new CashbackItemUv();
                        $cashback_uv_detail->cashback_uv_id = $cashback_uv->id;
                        $cashback_uv_detail->product_packaging_id = $value;
                        $cashback_uv_detail->price = $request->item_price_beli[$key];
                        $cashback_uv_detail->price_cashback = $request->cashback_beli[$key];
                        $cashback_uv_detail->qty = $request->item_qty_beli[$key];
                        $cashback_uv_detail->subtotal_item_idr = $item_price_nett_jual_2 ?? 0;
                        $cashback_uv_detail->amount_cashback_idr = $item_grand_total_beli;
                        $cashback_uv_detail->save();
                    }
                }

                PackingOrder::where('id', $request->invoice_id)->update(['uv_araya' => 1]);

                DB::commit();
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.accounting.finance_simulation.index_araya');
                return $this->response(200, $response);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->response(500, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    ]
                ]);
            }
        }
    }

    public function show_araya($id) 
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $invoice_uv = PackingOrderUv::where('id', $id)->first();

        $data = [
            'invoice' => $invoice_uv,
        ];

        return view($this->view . 'show_araya', $data);
    }

    public function destroy_araya($id)
    {
        if (!Auth::check() || Auth::user()->is_superuser == 0) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak'], 403);
        }

        $invoice = PackingOrderUv::find($id);

        if (!$invoice) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            // Update status sebelum menghapus
            $invoice->update([
                'status' => 0,
                'deleted_by' => Auth::user()->id
            ]);

            // Hapus data terkait sebelum menghapus invoice utama
            $invoice->simulation_detail()->delete();
            $invoice->simulation_item()->delete();
            $invoice->delete();

            // Update packing order terkait
            PackingOrder::where('id', $invoice->id_uv)->update(['uv_araya' => 0]);

            // Hapus cashback jika ada
            $cashback = CashbackUv::where('do_uv_id', $id)->first();
            if ($cashback) {
                $cashback->detail()->delete();
                $cashback->delete();
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    public function print_jual($id)
    {
        if (empty($id) || !is_numeric($id)) {
            abort(404, 'Invoice ID tidak valid.');
        }

        $result = PackingOrderUv::find($id);
        if (!$result) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.accounting.finance_simulation.araya_jual', $data)
                ->setPaper('a5', 'landscape');

        $generate = false; // Ubah sesuai logika bisnis.

        if ($generate) {
            return $pdf->download("{$result->code}.pdf");
        }

        return $pdf->stream("{$result->code}.pdf");
    }

    public function print_beli($id)
    {
        if (empty($id) || !is_numeric($id)) {
            abort(404, 'Invoice ID tidak valid.');
        }

        $result = CashbackUv::where('do_uv_id', $id)->first();
        if (!$result) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.accounting.finance_simulation.araya_beli', $data)
                ->setPaper('a5', 'landscape');

        $generate = false; // Ubah sesuai logika bisnis.

        if ($generate) {
            return $pdf->download("{$result->code}.pdf");
        }

        return $pdf->stream("{$result->code}.pdf");
    }

    // Mitra
    public function index_mitra(Request $request)
    {
        // Cek akses pengguna
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Ambil bulan dan tahun dari request, jika kosong pakai bulan & tahun berjalan
        $year = $request->has('year') ? intval($request->year) : date('Y');
        $month = $request->has('month') ? intval($request->month) : date('m');

        // ========== Query Dasar untuk Mitra ==========
        $query = DB::table('penjualan_do')
        ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
        ->leftJoin('master_mitra_detail', 'master_customer_other_addresses.id', '=', 'master_mitra_detail.customer_other_address_id')
        ->leftJoin('master_mitra', 'master_mitra_detail.mitra_id', '=', 'master_mitra.id')
        ->select(
            'penjualan_do.id as do_id',
            'penjualan_do.do_code as uv_code',
            'penjualan_do.type_transaction as transaksi',
            'master_customer_other_addresses.name as customer_name',
            'master_customer_other_addresses.text_kota as customer_kota',
            'master_mitra.id as id_mitra',
            'master_mitra.name as mitra_nama',
            'penjualan_do.uv_unifra'
        )
        ->whereMonth('penjualan_do.created_at', $month)
        ->whereYear('penjualan_do.created_at', $year)
        ->where('penjualan_do.cashback_status', 1)
        ->whereNull('penjualan_do.deleted_at');

        // ========== Data Non-Mitra ==========
        $nonMitra = (clone $query)
        ->whereNull('master_mitra.id')
        ->get();

        // ========== Data Mitra Belum Unifra (uv_unifra = 0) ==========
        $mitraBelumUnifra = (clone $query)
        ->where('penjualan_do.uv_unifra', 0)
        ->whereNotNull('master_mitra.id')
        ->get();

        // ========== Data Mitra Sudah Unifra (uv_unifra = 1) ==========
        $done = DB::table('penjualan_do')
        ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
        ->leftJoin('master_mitra_detail', 'master_customer_other_addresses.id', '=', 'master_mitra_detail.customer_other_address_id')
        ->leftJoin('master_mitra', 'master_mitra_detail.mitra_id', '=', 'master_mitra.id')
        ->select(
            'penjualan_do.id as do_id',
            'penjualan_do.do_code as uv_code',
            'penjualan_do.type_transaction as transaksi',
            'master_customer_other_addresses.name as customer_name',
            'master_customer_other_addresses.text_kota as customer_kota',
            'master_mitra.id as id_mitra',
            'master_mitra.name as mitra_nama',
            'penjualan_do.uv_unifra'
        )
        ->whereMonth('penjualan_do.created_at', $month)
        ->whereYear('penjualan_do.created_at', $year)
        ->where('penjualan_do.uv_unifra', 1)
        ->whereNull('penjualan_do.deleted_at')
        ->get();

        $mitra = $mitraBelumUnifra;

        return view($this->view . 'index_mitra', compact('year', 'month', 'mitra', 'nonMitra', 'done'));
    }



    public function create_mitra(Request $request, $id, $mitra)
    {
        // Accses
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_create == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $do_uv = PackingOrder::find($id);
        $mitra = Mitra::find($mitra);

        // dd($do_uv);

        $data = [
            'do_uv' => $do_uv,
            'mitra' => $mitra,
        ];

        return view($this->view . 'create_mitra', $data);
    }

    public function create_non_mitra(Request $request, $id)
    {
        // Accses
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_create == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $do_uv = PackingOrderUv::find($id);

        $data = [
            'do_uv' => $do_uv,
        ];

        return view($this->view . 'create_non_mitra', $data);
    }

    public function store_mitra(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'do_uv_id' => 'required|exists:penjualan_do,id',
            ]);

            if ($validator->fails()) {
                return $this->response(400, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $validator->errors()->all(),
                    ]
                ]);
            }

            $invoice_uv = PackingOrder::find($request->do_uv_id);
            // dd($invoice_uv);
            if (!$invoice_uv) {
                return $this->response(404, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Invoice tidak ditemukan',
                    ]
                ]);
            }

            DB::beginTransaction();
            try {
                // cek setting mitra
                $bulan_berjalan = Carbon::now()->month;

                $cek_saldo = MitraSetting::where('mitra_id', $request->mitra_id)
                    ->where('bulan', $bulan_berjalan)
                    ->first();

                if (!$cek_saldo) {
                    return $this->response(404, [
                        'notification' => [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => 'Tidak ada saldo yang tersedia untuk bulan ini!',
                        ]
                    ]);
                }

                if ($cek_saldo->saldo > $cek_saldo->batas_bawah || $cek_saldo->saldo > $cek_saldo->batas_atas){

                    return $this->response(404, [
                        'notification' => [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => 'Batas sudah tercapai!',
                        ]
                    ]);
                } else {
                    // Create invoice Jual

                    // parse nominal
                    $sub_total_jual = str_replace(',', '.', str_replace('.', '', $request->grand_total_jual));
                    $grand_total_jual = str_replace(',', '.', str_replace('.', '', $request->all_grand_total_jual));
                    $ppn_jual_idr = str_replace(',', '.', str_replace('.', '', $request->ppn_idr_jual));

                    $invoice_mitra_jual = new InvoiceTax;
                    $invoice_mitra_jual->code = "TS-" . $invoice_uv->do_code . "-JUAL";
                    $invoice_mitra_jual->do_id = $invoice_uv->id;
                    $invoice_mitra_jual->mitra_id = $request->mitra_id ?? null;
                    $invoice_mitra_jual->type = 1;
                    $invoice_mitra_jual->date = Carbon::now();
                    $invoice_mitra_jual->note = $request->note ?? null;
                    $invoice_mitra_jual->ppn_percent = $request->ppn_percent_jual ?? 0;
                    $invoice_mitra_jual->ppn_idr = $ppn_jual_idr ?? 0;
                    $invoice_mitra_jual->sub_total = $sub_total_jual ?? 0;
                    $invoice_mitra_jual->grand_total = $grand_total_jual ?? 0;
                    $invoice_mitra_jual->status = 1;
                    $invoice_mitra_jual->save();

                    if ($request->product_name_jual) {
                        foreach ($request->product_name_jual as $row => $key) {
                            $invoice_mitra_jual_detail = new InvoiceTaxDetail;
                            $invoice_mitra_jual_detail->invoice_tax_id = $invoice_mitra_jual->id;
                            $invoice_mitra_jual_detail->product_finance_id = $request->product_name_jual[$row];
                            $invoice_mitra_jual_detail->price = $request->price_jual[$row] ?? 0;
                            $invoice_mitra_jual_detail->qty = $request->qty_jual[$row] ?? 1;
                            $invoice_mitra_jual_detail->sub_total = $request->subtotal_item_jual[$row] ?? 0;
                            $invoice_mitra_jual_detail->save();
                        }
                    }

                    // Create invoice Beli

                    // parse nominal
                    $sub_total_beli = str_replace(',', '.', str_replace('.', '', $request->grand_total_beli));
                    $grand_total_beli = str_replace(',', '.', str_replace('.', '', $request->all_grand_total_beli));
                    $ppn_beli_idr = str_replace(',', '.', str_replace('.', '', $request->ppn_idr_beli));

                    $invoice_mitra_beli = new InvoiceTax;
                    $invoice_mitra_beli->code = "TS-" . $invoice_uv->do_code . "-BELI";
                    $invoice_mitra_beli->do_id = $invoice_uv->id;
                    $invoice_mitra_beli->mitra_id = $request->mitra_id ?? null;
                    $invoice_mitra_beli->type = 2;
                    $invoice_mitra_beli->date = Carbon::now();
                    $invoice_mitra_beli->note = $request->note ?? null;
                    $invoice_mitra_beli->ppn_percent = $request->ppn_percent_beli ?? 0;
                    $invoice_mitra_beli->ppn_idr = $ppn_beli_idr ?? 0;
                    $invoice_mitra_beli->sub_total = $sub_total_beli ?? 0;
                    $invoice_mitra_beli->grand_total = $grand_total_beli ?? 0;
                    $invoice_mitra_beli->status = 1;
                    $invoice_mitra_beli->save();

                    if ($request->product_name_beli) {
                        foreach ($request->product_name_beli as $row => $key) {
                            // parse nominal
                            $subtotal_item = str_replace(',', '.', str_replace('.', '', $request->subtotal_item_beli[$row]));

                            $invoice_mitra_beli_detail = new InvoiceTaxDetail;
                            $invoice_mitra_beli_detail->invoice_tax_id = $invoice_mitra_beli->id;
                            $invoice_mitra_beli_detail->product_finance_id = $request->product_name_beli[$row];
                            $invoice_mitra_beli_detail->price = $request->price_beli[$row] ?? 0;
                            $invoice_mitra_beli_detail->qty = $request->qty_beli[$row] ?? 1;
                            $invoice_mitra_beli_detail->sub_total = $subtotal_item ?? 0;
                            $invoice_mitra_beli_detail->save();
                        }
                    }

                    // update invoice real
                    $invoice_real = PackingOrder::where('id', $invoice_uv->id)->update(['uv_unifra' => 1]);

                     // Tambahkan saldo mitra
                    $cek_saldo->saldo += $grand_total_jual;
                    $cek_saldo->save();
                }
                DB::commit();
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'header' => 'Success',
                    'content' => 'Data Berhasil Terbuat!',
                ];

                $response['redirect_to'] = route('superuser.accounting.finance_simulation.index_mitra');
                return $this->response(200, $response);
            } catch (\Exception $e) {
                dd($e);
                Log::error('Error saat menyimpan invoice mitra: ' . $e->getMessage());
                DB::rollBack();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $e->getMessage(),
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function store_non_mitra(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'do_uv_id' => 'required|exists:penjualan_do_uv,id',
            ]);

            if ($validator->fails()) {
                return $this->response(400, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $validator->errors()->all(),
                    ]
                ]);
            }

            $invoice_uv = PackingOrder::find($request->do_uv_id);
            if (!$invoice_uv) {
                return $this->response(404, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Invoice tidak ditemukan',
                    ]
                ]);
            }

            DB::beginTransaction();
            try {
                    // Create invoice Jual

                    // parse nominal
                    $sub_total_jual = str_replace(',', '.', str_replace('.', '', $request->grand_total_jual));
                    $grand_total_jual = str_replace(',', '.', str_replace('.', '', $request->all_grand_total_jual));
                    $ppn_jual_idr = str_replace(',', '.', str_replace('.', '', $request->ppn_idr_jual));

                    $invoice_mitra_jual = new InvoiceTax;
                    $invoice_mitra_jual->code = "TS-" . $invoice_uv->code . "-JUAL";
                    $invoice_mitra_jual->do_id = $invoice_uv->id;
                    $invoice_mitra_jual->mitra_id = $request->mitra_id ?? null;
                    $invoice_mitra_jual->type = 1;
                    $invoice_mitra_jual->date = Carbon::now();
                    $invoice_mitra_jual->note = $request->note ?? null;
                    $invoice_mitra_jual->ppn_percent = $request->ppn_percent_jual ?? 0;
                    $invoice_mitra_jual->ppn_idr = $ppn_jual_idr ?? 0;
                    $invoice_mitra_jual->sub_total = $sub_total_jual ?? 0;
                    $invoice_mitra_jual->grand_total = $grand_total_jual ?? 0;
                    $invoice_mitra_jual->status = 1;
                    $invoice_mitra_jual->save();

                    if ($request->product_name_jual) {
                        foreach ($request->product_name_jual as $row => $key) {
                            $invoice_mitra_jual_detail = new InvoiceTaxDetail;
                            $invoice_mitra_jual_detail->invoice_tax_id = $invoice_mitra_jual->id;
                            $invoice_mitra_jual_detail->product_finance_id = $request->product_name_jual[$row];
                            $invoice_mitra_jual_detail->price = $request->price_jual[$row] ?? 0;
                            $invoice_mitra_jual_detail->qty = $request->qty_jual[$row] ?? 1;
                            $invoice_mitra_jual_detail->sub_total = $request->subtotal_item_jual[$row] ?? 0;
                            $invoice_mitra_jual_detail->save();
                        }
                    }

                    // Create invoice Beli

                    // parse nominal
                    $sub_total_beli = str_replace(',', '.', str_replace('.', '', $request->grand_total_beli));
                    $grand_total_beli = str_replace(',', '.', str_replace('.', '', $request->all_grand_total_beli));
                    $ppn_beli_idr = str_replace(',', '.', str_replace('.', '', $request->ppn_idr_beli));

                    $invoice_mitra_beli = new InvoiceTax;
                    $invoice_mitra_beli->code = "TS-" . $invoice_uv->code . "-BELI";
                    $invoice_mitra_beli->do_id = $invoice_uv->id;
                    $invoice_mitra_beli->mitra_id = $request->mitra_id ?? null;
                    $invoice_mitra_beli->type = 2;
                    $invoice_mitra_beli->date = Carbon::now();
                    $invoice_mitra_beli->note = $request->note ?? null;
                    $invoice_mitra_beli->ppn_percent = $request->ppn_percent_beli ?? 0;
                    $invoice_mitra_beli->ppn_idr = $ppn_beli_idr ?? 0;
                    $invoice_mitra_beli->sub_total = $sub_total_beli ?? 0;
                    $invoice_mitra_beli->grand_total = $grand_total_beli ?? 0;
                    $invoice_mitra_beli->status = 1;
                    $invoice_mitra_beli->save();

                    if ($request->product_name_beli) {
                        foreach ($request->product_name_beli as $row => $key) {
                            // parse nominal
                            $subtotal_item = str_replace(',', '.', str_replace('.', '', $request->subtotal_item_beli[$row]));

                            $invoice_mitra_beli_detail = new InvoiceTaxDetail;
                            $invoice_mitra_beli_detail->invoice_tax_id = $invoice_mitra_beli->id;
                            $invoice_mitra_beli_detail->product_finance_id = $request->product_name_beli[$row];
                            $invoice_mitra_beli_detail->price = $request->price_beli[$row] ?? 0;
                            $invoice_mitra_beli_detail->qty = $request->qty_beli[$row] ?? 1;
                            $invoice_mitra_beli_detail->sub_total = $subtotal_item ?? 0;
                            $invoice_mitra_beli_detail->save();
                        }
                    }
                DB::commit();
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'header' => 'Success',
                    'content' => 'Data Berhasil Terbuat!',
                ];

                $response['redirect_to'] = route('superuser.accounting.finance_simulation.index_mitra');
                return $this->response(200, $response);
            } catch (\Exception $e) {
                // dd($e);
                Log::error('Error saat menyimpan invoice mitra: ' . $e->getMessage());
                DB::rollBack();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $errors,
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function show_mitra($id)
    {

    }

    public function print_beli_mitra($id)
    {
        if (empty($id) || !is_numeric($id)) {
            abort(404, 'Invoice ID tidak valid.');
        }

        $result = InvoiceTax::where('do_id', $id)->where('type', 2)->first();
        if (!$result) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.accounting.finance_simulation.mitra_beli', $data)
                ->setPaper('a5', 'landscape');

        $generate = false; // Ubah sesuai logika bisnis.

        if ($generate) {
            return $pdf->download("{$result->code}.pdf");
        }

        return $pdf->stream("{$result->code}.pdf");
    }

    public function print_jual_mitra($id)
    {
        if (empty($id) || !is_numeric($id)) {
            abort(404, 'Invoice ID tidak valid.');
        }

        $result = InvoiceTax::where('do_id', $id)->where('type', 1)->first();
        if (!$result) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $data = [
            'result' => $result,
        ];

        $pdf = PDF::loadView('superuser.accounting.finance_simulation.mitra_jual', $data)
                ->setPaper('a5', 'landscape');

        $generate = false; // Ubah sesuai logika bisnis.

        if ($generate) {
            return $pdf->download("{$result->code}.pdf");
        }

        return $pdf->stream("{$result->code}.pdf");
    }

    public function generate_last_year(Request $request)
    {
            DB::beginTransaction();
            try {
                // Ambil data invoice real
                $invoice_real = DB::table('penjualan_do')
                    ->where('penjualan_do.status', 6)
                    ->whereYear('penjualan_so.so_date', 2024)
                    ->where('penjualan_so.type_so', 'nonppn')
                    ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
                    ->leftJoin('penjualan_do_item', 'penjualan_do.id', '=', 'penjualan_do_item.do_id')
                    ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
                    ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                    ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
                    ->leftJoin('master_customer_categories', 'master_customers.category_id', '=', 'master_customer_categories.id')
                    ->select(
                        'penjualan_do.id as id_invoice_real',
                        'penjualan_do.do_code as code_invoice_real',
                        'penjualan_do.idr_rate as invoice_kurs',
                        'penjualan_do.type_transaction as invoice_transaksi',
                        'penjualan_do.updated_by as invoice_proses',
                        'penjualan_do.updated_at as invoice_date_update',
                        'penjualan_so.so_date as invoice_date',
                        'penjualan_so.brand_name as invoice_brand',
                        'penjualan_so.type_so as invoice_type',
                        'penjualan_do_details.discount_1 as invoice_disc_1',
                        'penjualan_do_details.discount_2 as invoice_disc_2',
                        'penjualan_do_details.ppn_idr as invoice_ppn_idr',
                        'penjualan_do_details.discount_idr as invoice_disc_idr',
                        'penjualan_do_details.voucher_idr as invoice_voucher_idr',
                        'penjualan_do_details.delivery_cost_idr as invoice_delivery_cost_idr',
                        'penjualan_do_item.product_packaging_id as invoice_product',
                        'penjualan_do_item.qty as invoice_qty',
                        'penjualan_do_item.usd_disc as invoice_usd_disc_item',
                        'master_customers.id as id_customer',
                        'master_customer_other_addresses.id as id_member',
                        'master_customer_other_addresses.name as member_name',
                        'master_customer_categories.name as member_kategori',
                        'master_customer_other_addresses.text_kota as member_kota',
                        'master_customer_other_addresses.text_provinsi as member_provinsi',
                        'master_customer_other_addresses.zone as member_zone'
                    )
                    ->get();

                // dd($invoice_real);

                // Kelompokkan data berdasarkan invoice_code
                $groupedInvoices = $invoice_real->groupBy('code_invoice_real');
                $skippedInvoices = [];

                foreach ($groupedInvoices as $invoice_code => $invoice_items) {
                    $total_item_idr = 0;
                    $total_qty = 0;

                    // cek pembayaran
                    $pembayaran = DB::table('penjualan_do')
                    ->select([
                        'penjualan_do.do_code as do_code',
                        'penjualan_do.type_transaction as tipe_transaksi',
                        'finance_invoicing.code as invoice_code',
                        'finance_invoicing.created_at AS tanggal_invoice',
                        'finance_invoicing.grand_total_idr as total_tagihan',
                        'finance_payable.code as kode_pembayaran',
                        'finance_payable.pay_date AS tangal_pembayaran',
                        'finance_payable.updated_by AS proses_by',
                        DB::raw('COALESCE(SUM(finance_payable_detail.total), 0) as total_pembayaran'),
                        DB::raw("
                            CASE 
                                WHEN finance_invoicing.grand_total_idr = COALESCE(SUM(finance_payable_detail.total), 0) 
                                THEN 'LUNAS' 
                                ELSE 'BELUM LUNAS' 
                            END as status_pembayaran
                        ")
                    ])
                    ->leftJoin('finance_invoicing', 'penjualan_do.id', '=', 'finance_invoicing.do_id')
                    ->leftJoin('finance_payable_detail', 'finance_invoicing.id', '=', 'finance_payable_detail.invoice_id')
                    ->leftJoin('finance_payable', 'finance_payable_detail.payable_id', '=', 'finance_payable.id')
                    ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
                    ->where('penjualan_do.id', $invoice_items->first()->id_invoice_real)
                    ->groupBy(
                        'penjualan_do.do_code',
                        'penjualan_do.type_transaction',
                        'finance_invoicing.code',
                        'finance_invoicing.grand_total_idr',
                        'finance_payable.code'
                    )
                    ->first();

                    // input packing order uv
                    $do_uv = new PackingOrderUv;
                    $do_uv->id_uv = $invoice_items->first()->id_invoice_real;
                    $do_uv->customer_other_address_id  = $invoice_items->first()->id_member;
                    $do_uv->do_id = $invoice_items->first()->id_invoice_real;
                    $do_uv->code = "UV-" . $invoice_items->first()->code_invoice_real . "-2024";
                    $do_uv->idr_rate = 15500;
                    $do_uv->transaksi = $invoice_items->first()->invoice_transaksi;
                    $do_uv->proses_by = $invoice_items->first()->invoice_proses;
                    $do_uv->proses_at = $invoice_items->first()->invoice_date_update;
                    $do_uv->payment_status = $pembayaran->status_pembayaran;
                    $do_uv->payment_date = $pembayaran->tangal_pembayaran;
                    $do_uv->payment_proses = $pembayaran->proses_by;
                    $do_uv->count_kpi = 0;
                    $do_uv->invoice_date = $pembayaran->tanggal_invoice;
                    $do_uv->status = 1;
                    $do_uv->created_by = Auth::id();
                    $do_uv->save();
                    
                    foreach ($invoice_items as $row) {
                        // dd($row->invoice_kurs);
                        $get_data = DB::table('master_product_finance')
                            ->where('id', $row->invoice_product)
                            ->first();
                        
                        if (!$get_data || $get_data->selling_price_usd_unit == 0) {
                            $skippedInvoices[] = [
                                'invoice_code' => $invoice_code,
                                'product_id' => $row->invoice_product
                            ];
                            continue 2; // Skip invoice ini jika ada produk dengan harga 0
                        }
                        
                        $subtotal_item_idr = ($get_data->selling_price_usd_unit - (15500 * ($row->invoice_usd_disc_item ?? 0))) * $row->invoice_qty;
                        $total_qty += $row->invoice_qty;
                        $total_item_idr += $subtotal_item_idr;

                         // input do item uv
                        $do_item_uv = new PackingOrderItemUv;
                        $do_item_uv->do_uv_id = $do_uv->id;
                        $do_item_uv->product_packaging_id  = $row->invoice_product;
                        $do_item_uv->qty  = $row->invoice_qty;
                        $do_item_uv->free  = null;
                        $do_item_uv->price_jual  = $get_data->selling_price_usd_unit;
                        $do_item_uv->price_beli  = $get_data->buying_price_usd_unit;
                        $do_item_uv->usd_disc  = $row->invoice_usd_disc_item;
                        $do_item_uv->total  = $subtotal_item_idr;
                        $do_item_uv->save();
                    }
                    
                    // Lakukan perhitungan diskon dan total
                    $disc_1 = $total_item_idr * (($invoice_items->first()->invoice_disc_1 ?? 0) / 100);
                    $disc_2 = ($total_item_idr - $disc_1) * (($invoice_items->first()->invoice_disc_2 ?? 0) / 100);
                    $purchas_total = $total_item_idr - $disc_1 - $disc_2 - ($invoice_items->first()->invoice_disc_idr ?? 0) - ($invoice_items->first()->invoice_voucher_idr ?? 0) - ($invoice_items->first()->invoice_ppn_idr ?? 0);
                    $grand_total = $purchas_total + ($invoice_items->first()->invoice_delivery_cost_idr ?? 0);

                    // input do detils uv
                    $do_detail_uv = new PackingOrderDetailUv;
                    $do_detail_uv->do_uv_id = $do_uv->id;
                    $do_detail_uv->disc_1 = $invoice_items->first()->invoice_disc_1 ?? 0;
                    $do_detail_uv->disc_2 = $invoice_items->first()->invoice_disc_2  ?? 0;
                    $do_detail_uv->disc_1_idr = $disc_1 ?? 0;
                    $do_detail_uv->disc_2_idr = $disc_2 ?? 0;
                    $do_detail_uv->disc_idr = $invoice_items->first()->invoice_disc_idr ?? 0;
                    $do_detail_uv->voucher_idr = $invoice_items->first()->invoice_disc_idr ?? 0;
                    $do_detail_uv->ppn_percent = 0;
                    $do_detail_uv->ppn_idr = $invoice_items->first()->ppn_idr ?? 0;
                    $do_detail_uv->delivery_cost_idr = $invoice_items->first()->invoice_delivery_cost_idr ?? 0;
                    $do_detail_uv->grand_total_idr = $grand_total;
                    $do_detail_uv->created_by = Auth::id();
                    $do_detail_uv->save();

                    // Simpan atau update data
                    CustomerTypeBrandReportsLog::updateOrCreate(
                        ['invoice_code' => $invoice_code],
                        [
                            'customer_id' => $invoice_items->first()->id_customer, 
                            'other_address_id' => $invoice_items->first()->id_member,
                            'customer_name' => $invoice_items->first()->member_name, 
                            'customer_type' => $invoice_items->first()->member_kategori,
                            'customer_kota' => $invoice_items->first()->member_kota, 
                            'customer_provinsi' => $invoice_items->first()->member_provinsi,
                            'customer_zone' => $invoice_items->first()->member_zone, 
                            'invoice_code' => $invoice_code,
                            'invoice_date' => $invoice_items->first()->invoice_date, 
                            'invoice_brand' => $invoice_items->first()->invoice_brand,
                            'invoice_type' => $invoice_items->first()->invoice_type, 
                            'invoice_qty' => $total_qty,
                            'invoice_purchase' => $purchas_total,
                            'invoice_delivery_order_cost' => $invoice_items->first()->invoice_delivery_cost_idr ?? 0,
                            'created_at' => now()
                        ]
                    );
                }
                // Simpan log invoice yang tidak terproses
                if (!empty($skippedInvoices)) {
                    foreach ($skippedInvoices as $skippedInvoice) {
                        DB::table('failed_invoices_log')->insert([
                            'invoice_code' => $skippedInvoice['invoice_code'],
                            'product_id' => $skippedInvoice['product_id'],
                            'reason' => 'Selling price USD unit is 0 for this product',
                            'created_at' => now(),
                            'created_by' => Auth::id(),
                        ]);
                    }
                }

                DB::commit();
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'header' => 'Success',
                    'content' => 'Data Berhasil Terbuat!',
                ];

                // $response['redirect_to'] = route('superuser.accounting.finance_simulation.index_araya');
                return redirect()->back()->with('success', 'Data Berhasil Terbuat!');
            } catch (\Exception $e) {
                // dd($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ];

                return $this->response(400, $response);
            }
        
    }

    public function delete_data(Request $request)
    {
        $tahun = 2024; // Tahun langsung didefinisikan

        try {
            DB::beginTransaction(); // Mulai transaksi

            // Ambil semua ID dari penjualan_do_uv yang memiliki proses_at di tahun tertentu
            $do_uv_ids = DB::table('penjualan_do_uv')
                ->whereYear('proses_at', $tahun)
                ->pluck('id')
                ->toArray();

            if (!empty($do_uv_ids)) {
                // Hapus detail berdasarkan ID yang telah ditemukan
                DB::table('penjualan_do_detail_uv')->whereIn('do_uv_id', $do_uv_ids)->delete();
                DB::table('penjualan_do_item_uv')->whereIn('do_uv_id', $do_uv_ids)->delete();
                DB::table('penjualan_do_uv')->whereIn('id', $do_uv_ids)->delete();
            }

            // Truncate tabel lainnya
            DB::table('report_customer_type_brand_history')->truncate();
            DB::table('failed_invoices_log')->truncate();

            DB::commit(); // Konfirmasi transaksi jika berhasil

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'header' => 'Success',
                'content' => "Data Berhasil Dihapus!",
            ];

            $response['redirect_to'] = route('superuser.accounting.finance_simulation.index_araya');
            return $this->response(200, $response);

        } catch (Exception $e) {
            DB::rollBack(); // Batalkan transaksi jika terjadi error

            $response['notification'] = [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ];

            return $this->response(400, $response);
        }
    }

    public function page_report(Request $request)
    {
        // Authorization check
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }
    
        // Filter bulan dan tahun dari request
        $selectedBulan = $request->get('bulan', null);
        $selectedTahun = $request->get('tahun', null);
    
        // Ambil semua bulan dan tahun yang tersedia di database
        $availableMonths = FinanceSimulationPrice::query()
            ->selectRaw('MONTH(created_at) as bulan, YEAR(created_at) as tahun')
            ->groupBy('bulan', 'tahun')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'asc')
            ->get();
    
        $bulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
    
        $query = FinanceSimulationPrice::query()
            ->where('status', FinanceSimulationPrice::STATUS['ACTIVE']);

        if ($selectedBulan && $selectedTahun) {
            $query->whereMonth('created_at', $selectedBulan)
                ->whereYear('created_at', $selectedTahun);
            $simulation = $query->get();
        } else {
            $simulation = collect(); // Mengembalikan koleksi kosong jika bulan/tahun tidak dipilih
        }
    
        $simulation = $query->get();
    
        $data = [
            'simulation' => $simulation,
            'availableMonths' => $availableMonths,
            'bulan' => $bulan,
            'selectedBulan' => $selectedBulan,
            'selectedTahun' => $selectedTahun,
        ];
    
        return view($this->view . "report", $data);
    }
}