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
use App\DataTables\Finance\PayableTable;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use App\Entities\Penjualan\PackingOrder;
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

    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $other_address = CustomerOtherAddress::get();

        $payable = Payable::where('finance_payable.status', Payable::STATUS['ACTIVE'])
                        ->leftJoin('finance_payable_detail', 'finance_payable_detail.payable_id', '=', 'finance_payable.id')
                        ->leftJoin('finance_invoicing', 'finance_invoicing.id', '=', 'finance_payable_detail.invoice_id')
                        ->leftJoin('master_customer_other_addresses', 'master_customer_other_addresses.id', '=', 'finance_payable.customer_other_address_id')
                        ->selectRaw('
                            finance_payable.code as payableCode,
                            finance_payable.status as status, 
                            finance_payable_detail.total as totalPayable, 
                            master_customer_other_addresses.name as customer, 
                            finance_invoicing.code as invoiceCode
                        ')
                        ->get();

        $data = [
            'other_address' => $other_address,
            'payable' => $payable,
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

        $other_address = CustomerOtherAddress::get();
        $invoice = Invoicing::get();

        $data = [
            'other_address' => $other_address,
            'invoice' => $invoice,
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

    }

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $member = CustomerOtherAddress::where('id', $id)->first();

        $result = Payable::where('customer_other_address_id', $member->id)->whereRaw('Date(created_at) = CURDATE()')->get();
        
        $data = [
            'result' => $result,
            'member' => $member,
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['result'] = Payable::findOrFail($id);

        return view('superuser.finance.payable.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $payment = Payable::find($id);

            if ($payment == null){
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
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

            if($payment->status == 2) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Payment harus status aktiv',
                ];
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try{

                    $payment->code = $request->code;

                    if ($payment->save()) {
                        if($request->payable_detail){
                            $total_payable = 0;
                            foreach($request->payable_detail as $key => $value){
                                if($request->payable_detail[$key]) {
                                    $get_invoice = Invoicing::find($request->invoice_id[$key]);
                                    
                                    $payable_detail = PayableDetail::find($request->payable_detail[$key]);
                                    $payable_detail->total = $request->payable[$key];
                                    $payable_detail->prev_account_receivable = $get_invoice->grand_total_idr - $request->payable[$key];
                                    $payable_detail->updated_by = Auth::id();
                                    $payable_detail->save();

                                    $total_payable += $request->payable[$key];
                                }
                            }
                        }

                        // Update header payable
                        $update_payable = Payable::where('id', $payment->id)->update([
                            'total' => $total_payable,
                            'updated_by' => Auth::id(),
                        ]);

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.finance.payable.detail', $payment->id);

                        return $this->response(200, $response);
                    }

                }catch (\Exception $e) {
                    // dd($e);
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => "Internal Server Error!",
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }

    public function approve($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
           if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
               return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
           }
        }

        $payable = Payable::findOrFail($id);

        if($payable == null){
            abort(404);
        }

        DB::beginTransaction();
        try{

            $payable->status = Payable::STATUS['APPROVE'];
            $payable->updated_by = Auth::id();
        
            if ($payable->save()){
                $get_detail = PayableDetail::where('payable_id', $payable->id)->first();
                $get_invoice = Invoicing::where('id', $get_detail->invoice_id)->first();

                $so = SalesOrder::where('id', $get_invoice->do->so_id)->first();

                if ($so->type_transaction == 'CASH'){
                    $update_so_payment = SalesOrder::where('id', $so->id)->update(['payment_status' => 1]);
                }elseif($so->type_transaction == 'TEMPO'){
                    $update_so_payment = SalesOrder::where('id', $so->id)->update(['payment_status' => 2]);
                }

                DB::commit();
                return redirect()->back()->with('success','<a href="'.route('superuser.finance.payable.index').'">'.$payable->code.'</a> : Payment has been successfully processed!');
            }
        }catch (\Exception $e) {
            dd($e);
            DB::rollback();
            $response['notification'] = [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => "Internal Server Error!",
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
    public function destroy($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
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
}