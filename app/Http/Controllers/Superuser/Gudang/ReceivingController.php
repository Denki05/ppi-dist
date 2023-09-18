<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\DataTables\Gudang\ReceivingTable;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\Hpp;
use App\Entities\Master\Company;
use App\Entities\Master\SupplierCoa;
use App\Entities\Master\Warehouse;
use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Gudang\ReceivingDetailColly;
use App\Exports\Gudang\ReceivingDetailImportTemplate;
use App\Imports\Gudang\ReceivingDetailImport;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderDetail;
use App\Entities\Finance\SettingFinance;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Excel;
use Carbon\Carbon;
use DomPDF;
use Validator;
use DB;

class ReceivingController extends Controller
{
    public function json(Request $request, ReceivingTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('receiving-manage')) {
            return abort(403);
        }

        return view('superuser.gudang.receiving.index');
    }

    public function create()
    {
        if (!Auth::guard('superuser')->user()->can('receiving-create')) {
            return abort(403);
        }

        $data['warehouses'] = Warehouse::get();

        //$data['warehouses_disp'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.gudang.receiving.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code'              => 'required|string|unique:receiving,code',
                'warehouse'         => 'required|integer',
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
                $receiving = new Receiving;

                $receiving->code = $request->code;
                $receiving->warehouse_id = $request->warehouse;
                $receiving->pbm_date = $request->pbm_date;
                $receiving->note = $request->note;

                $receiving->status = Receiving::STATUS['ACTIVE'];

                if ($receiving->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.step', ['id' => $receiving->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-edit')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::find($id);

        return view('superuser.purchasing.receiving.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $receiving = Receiving::find($id);

            if ($receiving == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:receiving,code,' . $receiving->id,
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
                $receiving->code = $request->code;
                $receiving->pbm_date = $request->pbm_date;
                $receiving->description = $request->note;

                if ($receiving->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.gudang.receiving.step', ['id' => $receiving->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function step($id)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-edit')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.gudang.receiving.step', $data);
    }

    public function publish(Request $request, $id)
    {
        return $this->save_acc($request, $id, 'publish');
    }

    public function acc(Request $request, $id)
    {
        return $this->save_acc($request, $id, 'acc');
    }

    private function save_acc(Request $request, $id, $button_type)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('receiving-acc')) {
                return abort(403);
            }

            $receiving = Receiving::find($id);

            if ($receiving == null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                $superuser = Auth::guard('superuser')->user();
                $collect = [];
                
                $setting_tax = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'receiving_tax')->first();

                if ($setting_tax == null or $setting_tax->coa_id == null) {
                    $response['failed'] = 'Tax Setting is not set, please contact your Administrator!';
                    return $this->response(200, $response);
                } else {
                    $total_persediaan = 0;
                    $total_tax = 0;
                    $deliv_cost = 0;

                    foreach ($receiving->details as $detail) {
                        if(($detail->total_quantity_ri == 0) && ($detail->total_reject_ri($detail->id) == 0)){
                            $response['failed'] = 'Quantity RI and Quantity Reject is 0, cant save';
                            DB::rollback();

                            return $this->response(200, $response);
                        }
                        
                        $harga_satuan = $detail->ppb_detail->total_price_idr / $detail->ppb_detail->quantity;
                        $reject_idr = 0;
                        if ($detail->total_reject_ri($detail->id)) {
                            $reject_idr = $detail->total_reject_ri($detail->id) * $harga_satuan;

                            if (!empty($collect[$detail->ppb_id])) {
                                $collect[$detail->ppb_id] += $reject_idr;
                            } else {
                                $collect[$detail->ppb_id] = $reject_idr;
                            }
                        }
                        
                        $delivery_cost = 0;

                        if($detail->total_quantity_ri > 0) {
                            $delivery_cost = $detail->delivery_cost / $detail->total_quantity_ri;
                        }

                        foreach($detail->collys as $colly){
                            $receiving_detail_colly = ReceivingDetailColly::find($colly->id);

                            if($detail->total_quantity_ri != 0 ){
                                $receiving_detail_colly->hpp = ($detail->ppb_detail->total_price_idr / $detail->ppb_detail->quantity) +
                                                            ($detail->delivery_cost / $detail->total_quantity_ri);
                            } else {
                                $receiving_detail_colly->hpp = 0;
                            }

                            $receiving_detail_colly->save();
                        }

                        // HANDLE RECEIVING COA
                        $qty_receive = ReceivingDetailColly::where('receiving_detail_id', $detail->id)->sum('quantity_ri');
                        $total_persediaan_item = $detail->total_quantity_ri * ($harga_satuan + $delivery_cost);
                        $total_persediaan = $total_persediaan + $total_persediaan_item;

                        // SUM TAX
                        $tax_satuan = 0;
                        if ($detail->ppb_detail->total_tax > 0) {
                            $tax_satuan = $detail->ppb_detail->total_tax / $detail->ppb_detail->quantity;
                        }
                        $total_tax_item = $qty_receive * $tax_satuan;
                        $total_tax = $total_tax + $total_tax_item;

                        //SUM Delivery Cost
                        $deliv_cost += $detail->delivery_cost;
                    }

                    if($receiving->transaction_type == Receiving::TRANSACTION_TYPE['Tunai']) {
                        $setting_receiving_acc = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'receiving_debet')->first();

                        $setting_finance = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'ppb_tunai_debet')->first();

                        $setting_finance_cost = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'receiving_cost')->first();
                        if($setting_finance == null OR $setting_finance->coa_id == null) {
                            $failed = 'Finance Setting is not set, please contact your Administrator!';
                        } else {
                            // ADD JOURNAL
                            $journal = new Journal;
                            $journal->coa_id = $setting_receiving_acc->coa_id;
                            $journal->name = Journal::PREJOURNAL['RI_ACC'] . $receiving->code;
                            $journal->debet = $total_persediaan - $total_tax;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();
        
                            $journal = new Journal;
                            $journal->coa_id = $setting_finance->coa_id;
                            $journal->name = Journal::PREJOURNAL['RI_ACC'] . $receiving->code;
                            $journal->credit = $total_persediaan - $deliv_cost;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();
        
                            $journal = new Journal;
                            $journal->coa_id = $setting_finance_cost->coa_id;
                            $journal->name = Journal::PREJOURNAL['RI_ACC'] . $receiving->code;
                            $journal->credit = $deliv_cost;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();

                            if ($total_tax > 0) {
                                $journal = new Journal;
                                $journal->coa_id = $setting_tax->coa_id;
                                $journal->name = Journal::PREJOURNAL['RI_TAX'] . $receiving->code;
                                $journal->debet = $total_tax;
                                $journal->status = Journal::STATUS['UNPOST'];
                                $journal->save();
                            }
        
                            if ($collect) {
                                foreach ($collect as $key => $value) {
                                    $ppb = PurchaseOrder::find($key);
        
                                    // ADD JOURNAL
                                    $journal = new Journal;
                                    $journal->coa_id = $ppb->coa_id;
                                    $journal->name = Journal::PREJOURNAL['RI_REJECT'] . $ppb->code;
                                    $journal->debet = $value;
                                    $journal->status = Journal::STATUS['UNPOST'];
                                    $journal->save();
        
                                    $journal = new Journal;
                                    $journal->coa_id = $setting_finance->coa_id;
                                    $journal->name = Journal::PREJOURNAL['RI_REJECT'] . $ppb->code;
                                    $journal->credit = $value;
                                    $journal->status = Journal::STATUS['UNPOST'];
                                    $journal->save();
                                }
                            }
                        }
                    }else{
                        $setting_receiving_acc = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'receiving_debet')->first();

                        $setting_finance_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'ppb_non_tunai_debet')->first();

                        $setting_finance_cost = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'receiving_cost')->first();

                        if($receiving->transaction_type == Receiving::TRANSACTION_TYPE['Non Tunai']){
                            // ADD JOURNAL
                            $journal = new Journal;
                            $journal->coa_id = $setting_receiving_acc->coa_id;
                            $journal->name = Journal::PREJOURNAL['RI_ACC'] . $receiving->code;
                            $journal->debet = $total_persediaan - $total_tax;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();
        
                            $journal = new Journal;
                            $journal->coa_id = $setting_finance_credit->coa_id;
                            $journal->name = Journal::PREJOURNAL['RI_ACC'] . $receiving->code;
                            $journal->credit = $total_persediaan - $deliv_cost;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();
        
                            $journal = new Journal;
                            $journal->coa_id = $setting_finance_cost->coa_id;
                            $journal->name = Journal::PREJOURNAL['RI_ACC'] . $receiving->code;
                            $journal->credit = $deliv_cost;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();
                        }
                    }

                    $receiving->acc_by = Auth::guard('superuser')->id();
                    $receiving->acc_at = Carbon::now()->toDateTimeString();
                    $receiving->status = Receiving::STATUS['ACC'];

                    if ($receiving->save()) {
                        DB::commit();

                        if ($button_type == 'publish') {
                            $response['redirect_to'] = route('superuser.purchasing.receiving.index');
                        } else {
                            $response['redirect_to'] = '#datatable';
                        }

                        return $this->response(200, $response);
                    }
                }
            } catch (\Exception $e) {
                DB::rollback();
                // DD($e);
                $response['failed'] = 'Internal Server Error!';

                return $this->response(200, $response);
            }
        }
    }
    
    public function cancel_approve(Request $request, $id)
    {
        if ($request->ajax()) {
            $failed = "";

            DB::beginTransaction();

            try{
                $receiving = Receiving::find($id);
                
                $superuser = Auth::guard('superuser')->user();
                $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

                if($journal_periode) {
                    $min_date = Carbon::parse( $journal_periode->to_date );
                    if($receiving->acc_at <= $min_date ) {
                        $response['failed'] = 'Transaksi sudah terposting';
                        return $this->response(200, $response);
                    }
                }
                
                foreach($receiving->details as $detail){
                    foreach($detail->collys as $colly){
                        if($colly->status_mutation == 1){
                            $failed = "Ada SKU yang sudah dimutasi";
                            break;
                        }

                        if($colly->status_recondition == 1){
                            $failed = "Ada SKU yang sudah direkondisi";
                            break;
                        }
                    }
                }

                if ($failed) {
                    $response['failed'] = $failed;
                    DB::rollback();

                    return $this->response(200, $response);
                }

                //Delete jurnal ACC
                $jurnals = Journal::where('transaction_type', Journal::TRANSACTION_TYPE['RECEIVING'])
                                  ->where('transaction_id', $receiving->id)
                                  ->get();

                if($jurnals->isEmpty()){
                    //Delete jurnal ACC
                    $jurnals = Journal::where('name', Journal::PREJOURNAL['RI_ACC'] . $receiving->code)->get();

                    foreach($jurnals as $jurnal){
                        $data = Journal::find($jurnal->id);

                        $data->delete();
                    }

                    //Delete jurnal TAX
                    $jurnals = Journal::where('name', Journal::PREJOURNAL['RI_TAX'] . $receiving->code)->get();

                    foreach($jurnals as $jurnal){
                        $data = Journal::find($jurnal->id);

                        $data->delete();
                    }

                    foreach($receiving->details as $detail){
                        $jurnals = Journal::where('name', Journal::PREJOURNAL['RI_REJECT'] . $detail->purchase_order->code);

                        foreach($jurnals as $jurnal){
                            $data = Journal::find($jurnal->id);

                            $data->delete();
                        }
                    }
                } else {
                    foreach($jurnals as $jurnal){
                        $data = Journal::find($jurnal->id);

                        $data->delete();
                    }
                }

                $receiving->status = Receiving::STATUS['ACTIVE'];
                $receiving->acc_by = null;
                $receiving->acc_at = null;

                if($receiving->save()){

                    DB::commit();
                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                // DD($e);
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

    public function show($id)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-show')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.purchasing.receiving.show', $data);
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['company'] = Company::find(1);
        $data['receiving'] = Receiving::findOrFail($id);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView('superuser.purchasing.receiving.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function print_barcode($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['receiving'] = Receiving::findOrFail($id);

        $pdf = DomPDF::loadView('superuser.purchasing.receiving.print_barcode', $data);

        // 50mm x 70mm
        $customPaper = array(0, 0, 198.10, 141.50);
        $pdf->setPaper($customPaper, 'portrait');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function print_barcodeWithCode($code)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-print')) {
            return abort(403);
        }

        $code = str_replace('\\', '/', $code);

        $data['receiving'] = Receiving::where('code', $code)->first();

        $pdf = DomPDF::loadView('superuser.purchasing.receiving.print_barcode', $data);

        // 50mm x 70mm
        $customPaper = array(0, 0, 198.10, 141.50);
        $pdf->setPaper($customPaper, 'portrait');

        return $pdf->stream();
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    abort(405);
                }
            }

            $receiving = Receiving::find($id);

            if ($receiving === null) {
                abort(404);
            }

            $receiving->status = Receiving::STATUS['DELETED'];

            if ($receiving->save()) {

                $response['redirect_to'] = route('superuser.gudang.receiving.index');
                return $this->response(200, $response);
            }
        }
    }
    
    public function import_template()
    {
        $filename = 'receiving-detail-import-template.xlsx';
        return Excel::download(new ReceivingDetailImportTemplate, $filename);
    }

    public function import(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new ReceivingDetailImport($id);
            Excel::import($import, $request->import_file);
            
            if($import->error) {
                return redirect()->back()->withErrors($import->error);
            }
            
            return redirect()->back()->with(['message' => 'Import success']);
        }
    }
}