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

    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $search = $request->input('search');
        $customer_id = $request->input('customer_id');
        $table = Payable::where(function($query2) use($search){
                            if(!empty($search)){
                                $query2->where('code','like','%'.$search.'%');
                            }
                        })
                        ->where(function($query2) use($customer_id){
                            if(!empty($customer_id)){
                                $query2->where('customer_id',$customer_id);
                            }
                        })
                        ->orderBy('id','DESC')
                        ->paginate(10);
        $customer = Customer::where('status', Customer::STATUS['ACTIVE'])->get();
        $data = [
            'customer' => $customer,
            'table' => $table
        ];
        return view($this->view."index",$data);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $post = $request->all();

        // Start transaction
        DB::beginTransaction();

        try {
            // Validate request
            $validator = Validator::make($post, [
                'customer_id' => 'required|exists:master_customers,id',
                'pay_date' => 'required|date',
                'repeater' => 'required|array',
                'repeater.*.invoice_id' => 'required|exists:finance_invoicing,id',
                'repeater.*.payable' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'IsError' => true,
                    'Message' => $validator->errors()->first()
                ], 400);
            }

            // Create Payable entry
            $payable = Payable::create([
                'code' => CodeRepo::generatePayable(),
                'customer_id' => $post['customer_id'],
                'pay_date' => Carbon::parse($post['pay_date']),
                'note' => $post['note'] ?? null,
                'status' => 1,
                'created_by' => Auth::id(),
                'total' => 0,
            ]);

            $totalPayable = 0;

            // Process each repeater item
            foreach ($post["repeater"] as $value) {
                if (!empty($value["payable"])) {
                    $input_payable = floatval(str_replace(".", "", $value["payable"]));
                    $get_invoice = Invoicing::find($value["invoice_id"]);
            
                    if ($get_invoice) {
                        $payable_detail = $get_invoice->payable_detail->sum('total'); // Total pembayaran sebelumnya
                        $sisa = $get_invoice->grand_total_idr - $payable_detail; // Sisa tagihan
            
                        // Validasi sisa tagihan
                        if ($sisa <= 0) {
                            throw new \Exception("Invoice {$get_invoice->code} sudah lunas");
                        }
            
                        if ($input_payable > $sisa) {
                            throw new \Exception("Jumlah pembayaran melebihi saldo sisa untuk Invoice {$get_invoice->code}");
                        }
            
                        // Data untuk PayableDetail
                        $remaining_pay = $sisa - $input_payable; // Saldo sisa setelah pembayaran baru
                        $data = [
                            'payable_id' => $payable->id,
                            'invoice_id' => $value["invoice_id"],
                            'total' => $input_payable,
                            'prev_account_receivable' => $sisa, // Saldo sebelum pembayaran
                            'remaining_account_receivable' => $remaining_pay, // Saldo setelah pembayaran
                            'created_by' => Auth::id(),
                        ];
            
                        // Simpan detail pembayaran
                        PayableDetail::create($data);
            
                        $totalPayable += $input_payable;
                    } else {
                        throw new \Exception("Invoice ID {$value['invoice_id']} tidak ditemukan");
                    }
                }
            }

            if ($totalPayable == 0) {
                throw new \Exception("Tidak bisa melakukan payable. Tidak ada payable yang diinput.");
            }

            // Update the Payable entry with the total payable amount
            $payable->update(['total' => $totalPayable]);

            // Commit transaction
            DB::commit();

            LogActivity::addToLog('Create Payable:' . $payable->code);

            // Logging
            Log::info("Payable {$payable->code} Created by user " . Auth::id());

            // add notif
            $user = User::find(36);
            $user->notify(new PayableNotification($payable));

            return response()->json([
                'IsError' => false,
                'Message' => "Payable berhasil dibuat"
            ], 200);

        } catch (\Throwable $e) {
            // dd($e);
            // Rollback transaction and log error
            DB::rollback();
            Log::error('Payable creation failed: ' . $e->getMessage());

            return response()->json([
                'IsError' => true,
                'Message' => 'An error occurred while processing your request. Please try again.'
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
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }

        if ($request->ajax()) {
            $result = Payable::findOrFail($id);

            DB::beginTransaction();

            try {

                $result->status = Payable::STATUS['DELETED'];

                if ($result->save()) {
                    DB::commit();
                    LogActivity::addToLog('Destory Payable:' . $result->code);
                    $response['redirect_to'] = route('superuser.finance.payable.index');
                    return $this->response(200, $response);
                }

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to delete payable: " . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to delete payable. Please try again.');
            }
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
}