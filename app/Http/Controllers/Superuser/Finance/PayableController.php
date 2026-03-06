<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\Company;
use App\Entities\Finance\Invoicing;
use App\Entities\Finance\Payable;
use App\Entities\Finance\PayableDetail;
use App\Entities\Finance\PayableHistory;
use App\DataTables\Finance\PayableTable;
use App\DataTables\Report\PaymentReportTable;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use App\Entities\Penjualan\PackingOrder;
use App\Helper\LogActivity;
use App\Notifications\PayableNotification;
use App\Entities\Account\User;
use Illuminate\Support\Facades\Log;
use DB;
use Auth;
use PDF;
use Carbon\Carbon;
use Validator;

class PayableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.finance.payable.";
        $this->route = "superuser.finance.payable";
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

    public function json(Request $request, PayableTable $datatable)
    {
        return $datatable->build($request);
    }

    public function json2(Request $request, PaymentReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function json_done(Request $request)
    {
        // Pastikan pengguna memiliki akses
        if (Auth::user()->is_superuser == 0 && (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0)) {
            return response()->json(['error' => 'Anda tidak punya akses untuk melihat data ini.'], 403);
        }

        $model = Payable::select(
            'finance_payable.id AS id',
            'finance_payable.code AS code',
            'master_customers.name AS customer_name',
            'master_customers.text_kota AS customer_kota',
            'finance_payable.total AS total_pay',
            'finance_payable.pay_date AS tanggal_buat', // Alias kolom
            'finance_payable.status AS status',
            DB::raw('GROUP_CONCAT(DISTINCT finance_invoicing.code) AS invoice_code') // Menggunakan GROUP_CONCAT untuk invoice_code
        )
        ->leftJoin('master_customers', 'master_customers.id', '=', 'finance_payable.customer_id')
        ->leftJoin('finance_payable_detail', 'finance_payable.id', '=', 'finance_payable_detail.payable_id')
        ->leftJoin('finance_invoicing', 'finance_payable_detail.invoice_id', '=', 'finance_invoicing.id')
        ->where('finance_payable.status', Payable::STATUS['ACC'])
        ->groupBy('finance_payable.id') // Penting untuk menghindari duplikasi jika ada beberapa payable_detail
        ->orderBy('finance_payable.pay_date', 'desc'); // Perbaikan: Urutkan berdasarkan alias 'tanggal_buat'

        return Table::of($model)
            ->addIndexColumn()
            ->addColumn('customer_display_name', function($row){
                return $row->customer_name . ' ' . $row->customer_kota . ' ';
            })
            ->addColumn('status_label', function($row){
                // Sesuaikan label status sesuai dengan kebutuhan Anda
                switch ($row->status) {
                    case Payable::STATUS['ACC']:
                        return '<span class="badge badge-success">ACC</span>';
                    case Payable::STATUS['REVISI']:
                        return '<span class="badge badge-warning">Revisi</span>';
                    case Payable::STATUS['DELETED']:
                        return '<span class="badge badge-danger">Dihapus</span>';
                    case Payable::STATUS['ACTIVE']: // Asumsi 'ACTIVE' adalah status default/pending
                        return '<span class="badge badge-info">Pending</span>';
                    default:
                        return '<span class="badge badge-secondary">Unknown</span>';
                }
            })
            ->addColumn('action', function($row){
                $buttons = '<a href="'.route('superuser.finance.payable.detail', $row->id).'" class="btn btn-primary btn-sm btn-square" title="Detail"><i class="si si-eye"></i></a> ';
                // Anda bisa menambahkan tombol lain di sini (edit, print, dll.)
                // if (Auth::user()->is_superuser == 1 || (Auth::user()->is_superuser == 0 && !empty($this->access) && $this->access->can_update == 1)) {
                //     $buttons .= '<a href="'.route('superuser.finance.payable.edit', $row->id).'" class="btn btn-warning btn-sm btn-square" title="Edit"><i class="si si-pencil"></i></a> ';
                // }
                // if (Auth::user()->is_superuser == 1 || (Auth::user()->is_superuser == 0 && !empty($this->access) && $this->access->can_print == 1)) {
                //     $buttons .= '<a href="'.route('superuser.finance.payable.print', $row->id).'" class="btn btn-info btn-sm btn-square" title="Print" target="_blank"><i class="si si-printer"></i></a> ';
                // }
                // if (Auth::user()->is_superuser == 1 || (Auth::user()->is_superuser == 0 && !empty($this->access) && $this->access->can_delete == 1)) {
                //     $buttons .= '<a href="#" data-id="'.$row->id.'" class="btn btn-danger btn-sm btn-square btn-delete" title="Delete"><i class="si si-trash"></i></a>';
                // }
                return $buttons;
            })
            ->rawColumns(['status_label', 'action'])
            ->make(true);
    }

    public function index(Request $request)
    {
        // Pemeriksaan akses
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user)) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Menggunakan eager loading untuk memuat relasi yang diperlukan (customer, payable_details, dan invoice)
        // dalam jumlah query yang minimal. Ini akan sangat meningkatkan performa.
        $data['payable'] = Payable::with(['customer', 'payable_detail.invoice'])
                                    ->orderBy('created_at', 'DESC')
                                    ->get();

        $data['customers'] = CustomerOtherAddress::get();

        // Mengembalikan view dengan data yang sudah dimuat
        return view($this->view . "index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        if(empty($request->input('customer_id'))){
            return redirect()->route('superuser.finance.payable.index')->with('error','Tidak ada customer yang dipilih');
        }
        $customer = Customer::where('id', $request->input('customer_id'))->first();
        if(empty($customer)){
            return redirect()->route('superuser.finance.payable.index')->with('error','Customer tidak ditemukan');
        }
        $data = [
            'customer' => $customer
        ];
        return view($this->view."create", $data);
    }

    public function store(Request $request)
    {
        $post = $request->all();

        DB::beginTransaction();
        try {
            $validator = Validator::make($post, [
                'customer_id' => 'required|exists:master_customers,id',
                'pay_date' => 'required|date',
                'repeater' => 'required|array',
                'repeater.*.invoice_id' => 'required|exists:finance_invoicing,id',
                'repeater.*.payable' => 'required|numeric',
                'repeater.*.is_balanced' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses pembayaran. Periksa kembali data yang diinput.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $totalPayable = 0;

            foreach ($post["repeater"] as $value) {
                if (isset($value["payable"])) {
                    $input_payable = floatval(str_replace(".", "", $value["payable"]));
                    $get_invoice = Invoicing::find($value["invoice_id"]);

                    // VALIDASI CUSTOMER
                    if ($get_invoice->customer_id != $post['customer_id']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Invoice {$get_invoice->code} tidak sesuai customer"
                        ], 400);
                    }

                    $is_balanced = filter_var($value["is_balanced"], FILTER_VALIDATE_BOOLEAN);

                    if (!$get_invoice) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Invoice ID {$value['invoice_id']} tidak ditemukan"
                        ], 404);
                    }

                    $payable_detail = $get_invoice->payable_detail->sum('total');
                    $sisa = $get_invoice->grand_total_idr - $payable_detail;

                    if ($sisa <= 0) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Invoice {$get_invoice->code} sudah lunas"
                        ], 400);
                    }

                    if ($input_payable > $sisa && !$is_balanced) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Jumlah pembayaran melebihi saldo untuk Invoice {$get_invoice->code}"
                        ], 400);
                    }

                    if ($is_balanced) {
                        $input_payable = $sisa;
                    }

                    // 🔹 Buat Payable header baru per invoice
                    $payable = Payable::create([
                        'code' => CodeRepo::generatePayable(),
                        'customer_id' => $post['customer_id'],
                        'pay_date' => Carbon::parse($post['pay_date']),
                        'note' => $post['note'] ?? null,
                        'status' => 2,
                        'created_by' => Auth::id(),
                        'total' => $input_payable,
                    ]);

                    // 🔹 Simpan detail
                    PayableDetail::create([
                        'payable_id' => $payable->id,
                        'invoice_id' => $value["invoice_id"],
                        'total' => $input_payable,
                        'prev_account_receivable' => $sisa,
                        'remaining_account_receivable' => $is_balanced ? 0 : ($sisa - $input_payable),
                        'created_by' => Auth::id(),
                    ]);

                    $totalPayable += $input_payable;

                    // update status sales_order
                    $total_bayar = $get_invoice->payable_detail->sum('total') + $input_payable;

                    $sisa_update = $get_invoice->grand_total_idr - $total_bayar;

                    // Tentukan status pembayaran
                    if ($sisa_update <= 0) {
                        $payment_status = 1; // Lunas
                    } else {
                        $payment_status = 0; // Belum lunas
                    }

                    // Update status pembayaran di SalesOrder
                    if ($get_invoice->do && $get_invoice->do->so_id) {
                        SalesOrder::where('id', $get_invoice->do->so_id)
                            ->update(['payment_status' => $payment_status]);
                    }

                    // 🔹 Insert history
                    PayableHistory::create([
                        'payable_id' => $payable->id,
                        'do_id' => $get_invoice->do_id,
                        'invoice_id' => $get_invoice->id,
                        'invoice_code' => $get_invoice->code,
                        'payable_code' => $payable->code,
                        'customer_other_address_id' => $get_invoice->customer_other_address_id,
                        'acc_by' => Auth::id(),
                        'created_by' => $payable->created_by,
                    ]);
                }
            }

            if ($totalPayable == 0) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => "Tidak bisa melakukan payable. Tidak ada payable yang diinput."
                ], 400);
            }

            $payable->update(['total' => $totalPayable]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Payable berhasil dibuat per invoice"
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Payable creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat Payable. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Payable::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $data = [
            'result' => $result
        ];
        return view($this->view."detail",$data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['result'] = Payable::with('payable_detail.invoice', 'customer')->findOrFail($id);

        return view('superuser.finance.payable.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $payment = Payable::with('payable_detail.invoice')->find($id);

            if ($payment == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'payable_detail' => 'required|array',
                'payable_detail.*' => 'required|integer|exists:finance_payable_detail,id',
                'invoice_id' => 'required|array',
                'invoice_id.*' => 'required|integer|exists:finance_invoicing,id',
                'payable' => 'required|array',
                'payable.*' => 'required|numeric|min:0',
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
                DB::beginTransaction();

                try {

                    $payment->code = $request->code;
                    $payment->pay_date = $request->pay_date;
                    $payment->note = $request->note;

                    // Prepare payable details update
                    $payableDetails = PayableDetail::whereIn('id', $request->payable_detail)->get();
                    $invoices = Invoicing::whereIn('id', $request->invoice_id)->get()->keyBy('id');

                    $totalPayable = 0;

                    foreach ($payableDetails as $key => $detail) {
                        $invoiceId = $request->invoice_id[$key];
                        $payableAmount = $request->payable[$key];
        
                        if (!isset($invoices[$invoiceId])) {
                            // throw new \Exception("Invoice ID $invoiceId not found.");
                            $response['notification'] = [
                                'alert' => 'block',
                                'type' => 'alert-danger',
                                'header' => 'Error',
                                'content' => "Invoice ID not found.",
                            ];
                        }
        
                        $invoice = $invoices[$invoiceId];
        
                        $detail->total = $payableAmount;
                        $detail->prev_account_receivable = $invoice->grand_total_idr - $payableAmount;
                        $detail->updated_by = Auth::id();
                        $detail->save();
        
                        $totalPayable += $payableAmount;
                    }

                    $payment->total = $totalPayable;
                    $payment->updated_by = Auth::id();
                    if ($payment->save()) {
                        DB::commit();
    
                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
    
                        $response['redirect_to'] = route('superuser.finance.payable.index');
    
                        return $this->response(200, $response);
                    }   
                } catch (\Exception $e) {
                    DD($e);
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => "Internal Server Error",
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }

    public function approve($id)
    {
        // Validasi Hak Akses
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        // Cari Payable dengan lock untuk menghindari race condition
        $payable = Payable::lockForUpdate()->find($id);

        if (!$payable) {
            return abort(404, 'Payable tidak ditemukan');
        }

        DB::beginTransaction();
        try {
            // Update status payable
            $payable->status = Payable::STATUS['ACC'];
            $payable->updated_by = Auth::id();

            if (!$payable->save()) {
                throw new \Exception("Gagal menyimpan perubahan pada Payable.");
            }

            // Ambil detail payable
            $details = PayableDetail::where('payable_id', $payable->id)->get();

            foreach ($details as $detail) {
                $invoice = Invoicing::where('id', $detail->invoice_id)->first();
                $total_tagihan = $invoice->grand_total_idr;
                $payment = $invoice->payable_detail->sum('total'); // Total pembayaran hingga saat ini
                $sisa = $total_tagihan - $payment;
            
                // Tentukan status pembayaran
                if ($sisa == 0) {
                    $payment_status = 1; // Lunas
                } elseif ($sisa > 0) {
                    $payment_status = 2; // Belum lunas
                } else {
                    throw new \Exception("Pembayaran melampaui total tagihan untuk Invoice {$invoice->code}");
                }
            
                // Update status pembayaran di SalesOrder
                SalesOrder::where('id', $invoice->do->so_id)->update(['payment_status' => $payment_status]);
            }

            // Insert history
            $get_invoice = Invoicing::where('id', $payable->payable_detail[0]->invoice_id)->first();

            $history_pay = new PayableHistory([
                'payable_id' => $payable->id,
                'do_id' => $get_invoice->do_id,
                'invoice_id' => $get_invoice->id,
                'invoice_code' => $get_invoice->code,
                'payable_code' => $payable->code,
                'customer_other_address_id' => $get_invoice->customer_other_address_id,
                'acc_by' => Auth::id(),
                'created_by' => $payable->created_by,
            ]);

            if (!$history_pay->save()) {
                throw new \Exception("Gagal menyimpan riwayat pembayaran.");
            }

            // Commit transaksi
            DB::commit();

            // log acctivity
            LogActivity::addToLog('Approved Payable: ' . $payable->code . '| Invoice: '. $get_invoice->code);

            // Logging
            Log::info("Payable {$payable->code} approved by user " . Auth::id());

            // add notif
            $userIds = [32, 33];
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                $user->notify(new PayableNotification($payable));
            }

            // Response sukses
            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ];
            $response['redirect_to'] = route('superuser.finance.payable.index');

            return $this->response(200, $response);
        } catch (\Exception $e) {
            dd($e);
            // Rollback jika terjadi error
            DB::rollback();

            // Logging error
            Log::error('Error approving payable: ' . $e->getMessage());

            // Response error
            $response['notification'] = [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => "Internal Server Error: " . $e->getMessage(),
            ];

            return $this->response(400, $response);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Access
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0) {
                abort(405);
            }
        }

        $payable = Payable::findOrFail($id);

        DB::beginTransaction();

        try {
            // Pengecekan dan pembaruan status SalesOrder yang terkait
            foreach ($payable->payable_detail as $detail) { 
                $invoice = $detail->invoice;
                if ($invoice) {
                    $salesOrder = $invoice->sales_order;
                    if ($salesOrder) {
                        // Ubah status SalesOrder
                        $salesOrder->status = SalesOrder::STATUS['COPY']; // Atau status lain yang sesuai
                        $salesOrder->save();
                    }
                }
                
                // Hapus detail setelah status SalesOrder diperbarui
                $detail->delete();
            }
            
            // Selanjutnya, hapus Payable itu sendiri secara permanen
            if ($payable->delete()) {
                DB::commit();
                // LogActivity::addToLog('Hard-delete Payable:' . $payable->code);

                return redirect()->route('superuser.finance.payable.index')->with('success', 'Data berhasil dihapus permanen.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to hard-delete payable: " . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }
    }

    public function print($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = Payable::where('id',$id)->first();
        $company = Company::first();
        if(empty($result)){
            abort(404);
        }

        $data = [
            'result' => $result,
            'company' => $company
        ];

        $pdf = PDF::loadview($this->view."print",$data)->setPaper('a4','potrait');
        return $pdf->stream($result->code ?? '');
    }

    public function cancel_approve(Request $request, $id)
    {
        if ($request->ajax()){
            $failed = "";
            DB::beginTransaction();

            try{
                $payable = Payable::find($id);

                if($payable){
                    if($payable->count_cancel){
                        $failed = 'Limit sudah mencapai batas!';
                    }
                }

                $payable->status = Payable::STATUS['REVISI'];
                $payable->count_cancel = 1;

                if ($failed) {
                    $response['failed'] = $failed;

                    return $this->response(200, $response);
                }

                if($payable->save()){
                    DB::commit();
                    LogActivity::addToLog('Cancel Aprroved Payable:' . $payable->code);
                    $response['redirect_to'] = route('superuser.finance.payable.index');
                    return $this->response(200, $response);
                }

            }catch (\Exception $e) {
                DD($e);
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => "Internal Server Error",
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function cancel_edit(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['result'] = Payable::findOrFail($id);

        return view('superuser.finance.payable.cancel_edit', $data);
    }

    public function update_cancel(Request $request, $id)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            DB::beginTransaction();
            try{
                if(empty($post["payable_header"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "ID Payable tidak boleh kosong";
                    goto ResultData;
                }

                $get_payable = Payable::where('id', $post["payable_header"])->first();

                // update Note payable
                $update_payable = Payable::where('id', $post["payable_header"])->update([
                    'note' => $post["note"], 
                    'status' => Payable::STATUS['ACC']]);

                // update detail Payable
                $total_payment = 0;
                foreach($post["repeater"] as $index => $key){
                    if(!empty($key["payable"])){
                        $input_payment = floatval(str_replace(".", "", $key["payable"]));
                        $get_detail = PayableDetail::where('id', $key["payable_detail_id"])->first();
                        
                        $sisa = $get_detail->prev_account_receivable - $input_payment;

                        $data = [
                            'total' => $input_payment,
                            'prev_account_receivable' => $get_detail->prev_account_receivable,
                            'remaining_account_receivable' => $sisa,
                            'updated_by' => Auth::id(),
                        ];

                        $update_detail = PayableDetail::where('id', $key["payable_detail_id"])->update($data);
                        $total_payment += $input_payment;
                    }
                }

                if($total_payment == 0){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Tidak bisa melakukan payable.Tidak ada payable yang diinput";
                    goto ResultData;
                }

                $update = Payable::where('id', $post["payable_header"])->update([
                    'total' => $total_payment
                ]);

                DB::commit();

                LogActivity::addToLog('Update Cancel Payable:' . $get_payable->code);

                $data_json["IsError"] = FALSE;
                $data_json["Message"] = "Payable berhasil dibuat";
                goto ResultData;

            }catch(\Throwable $e){
                dd($e);
                DB::rollback();
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultData;
            }
        }
        else{
            $data_json["IsError"] = TRUE;
            $data_json["Message"] = "Invalid Method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    public function pageReport(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view($this->view."report");
    }

    public function getDetailInvoice($id)
    {
        $invoice = Invoicing::find($id);

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Example of returned HTML content
        return view('superuser.finance.payable.invoice_details', compact('invoice'))->render();
    }

    // public function unpaidInvoices(Request $request)
    // {
    //     $customer = Customer::with(['do.invoicing.payable_detail'])
    //         ->find($request->customer_id);

    //     if (!$customer) {
    //         return response()->json([]);
    //     }

    //     $rows = [];
    //     foreach ($customer->do as $index => $row) {
    //         $invoice = $row->invoicing;
    //         if (!$invoice) continue;

    //         $year = $invoice->created_at->format('Y');

    //         // hitung sisa tagihan
    //         $paid = $invoice->payable_detail->sum('total');
    //         $remaining = $invoice->grand_total_idr - $paid;

    //         if (!in_array($year, ['2022','2023']) && $remaining > 0 && $invoice->status != Invoicing::STATUS['PENDING']) {
    //             $rows[] = [
    //                 'id'            => $invoice->id,
    //                 'date'          => $invoice->created_at->format('d-m-Y'),
    //                 'code'          => $invoice->code,
    //                 'brand'         => $invoice->do->so->brand_name,
    //                 'tagihan'       => number_format($invoice->grand_total_idr,0,',','.'),
    //                 'sisa_tagihan'  => number_format($remaining,0,',','.'),
    //                 'type_value'    => $invoice->type,
    //                 'type_name'     => $invoice->type(),
    //             ];
    //         }
    //     }

    //     // Urutkan array $rows: 'TT' (1) di atas 'N' (0)
    //     usort($rows, function($a, $b) {
    //         return $b['type_value'] <=> $a['type_value'];
    //     });

    //     return response()->json($rows);
    // }

    public function unpaidInvoices(Request $request)
    {
        $invoices = Invoicing::with(['do.so', 'payable_detail'])
            ->where('customer_id', $request->customer_id)
            ->get();

        $rows = [];

        foreach ($invoices as $invoice) {
            $year = $invoice->created_at->format('Y');
            $paid = $invoice->payable_detail->sum('total');
            $remaining = $invoice->grand_total_idr - $paid;

            if (!in_array($year, ['2022','2023']) && $remaining > 0 && $invoice->status != Invoicing::STATUS['PENDING']) {
                
                // kalau type 0 → normal pakai brand dari DO
                // kalau type 1 → TT, tidak perlu DO (brand bisa -)
                $brand = $invoice->type == 0 
                    ? ($invoice->do->so->brand_name ?? '-') 
                    : '-';

                $rows[] = [
                    'id'            => $invoice->id,
                    'date'          => $invoice->created_at->format('d-m-Y'),
                    'code'          => $invoice->code,
                    'brand'         => $brand,
                    'tagihan'       => number_format($invoice->grand_total_idr, 0, ',', '.'),
                    'sisa_tagihan'  => number_format($remaining, 0, ',', '.'),
                    'type_value'    => $invoice->type,
                    'type_name'     => $invoice->type(), // method type() di model
                ];
            }
        }

        // Urutkan: TT (1) di atas normal (0)
        usort($rows, function($a, $b) {
            // Jika beda type → type 1 lebih tinggi
            if ($a['type_value'] !== $b['type_value']) {
                return $b['type_value'] <=> $a['type_value'];
            }

            // Kalau sama-sama type 0 → urut tanggal ASC
            if ($a['type_value'] == 0) {
                $dateA = \Carbon\Carbon::createFromFormat('d-m-Y', $a['date']);
                $dateB = \Carbon\Carbon::createFromFormat('d-m-Y', $b['date']);
                return $dateA <=> $dateB;
            }

            // Kalau sama-sama type 1 → biarkan urut default
            return 0;
        });

        return response()->json($rows);
    }


    public function customerSearch(Request $request)
    {
        $query = $request->get('query');

        $customers = Customer::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                ->orWhere('text_kota', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id','name','text_kota']);

        // gabungkan nama + kota untuk ditampilkan
        $customers->transform(function($item){
            $item->display_name = $item->name . '  ' . $item->text_kota;
            return $item;
        });

        return response()->json($customers);
    }
}