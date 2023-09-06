<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Master\Customer;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\Company;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderCost;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderLogPrint;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SoProforma;
use App\Entities\Penjualan\SoProformaDetail;
use App\Entities\Finance\Invoicing;
use App\Entities\Master\Vendor;
use App\Entities\Master\Warehouse;
use App\Entities\Gudang\StockMove;
use App\Entities\Setting\UserMenu;
use App\Repositories\CodeRepo;
use Illuminate\Support\Collection;
use Validator;
use PDF;
use DB;
use Auth;
use COM;


class DeliveryOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.penjualan.delivery_order.";
        $this->route = "superuser.penjualan.delivery_order";
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
    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $field = $request->input('field');
        $search = $request->input('search');
        $type_transaction = $request->input('type_transaction');
        $table = PackingOrder::where(function($query2) use($field,$search){
                                if(!empty($field) && !empty($search)) {
                                    $fieldDb = '';
                                    $ids = array();
                                    if ($field == 'customer') {
                                        $customerDb = Customer::where('name', 'like', '%'.$search.'%')->get();
                                        for($c=0; $c<count($customerDb); $c++) $ids[$c] = $customerDb[$c]->id;
                                        $fieldDb = 'customer_id';
                                    } else if ($field == 'sales') {
                                        $salesDb = Sales::where('name', 'like', '%'.$search.'%')->get();
                                        for($c=0; $c<count($salesDb); $c++) $ids[$c] = $salesDb[$c]->id;
                                        $fieldDb = 'sales_id';
                                    } else if ($field == 'transaksi') {
                                        if (str_contains('cash', strtolower($search))) {
                                            $ids = [1];
                                        } else if (str_contains('tempo', strtolower($search))) {
                                            $ids = [2];
                                        } else if (str_contains('marketplace', strtolower($search))) {
                                            $ids = [3];
                                        }
                                        $fieldDb = 'type_transaction';
                                    } else if ($field == 'referensiSO') {
                                        // Cari SO > lalu cari SO Item > lalu cari DO Item > lalu cari Id nya

                                        // Cari SO
                                        $salesOrderDb = SalesOrder::where('code', 'like', '%'.$search.'%')->get();
                                        $salesOrderId = array();
                                        for($c=0; $c<count($salesOrderDb); $c++) $salesOrderId[$c] = $salesOrderDb[$c]->id;

                                        // Cari SO Item
                                        $salesOrderItemDb = SalesOrderItem::whereIn('so_id', $salesOrderId)->get();
                                        $salesOrderItemId = array();
                                        for($c=0; $c<count($salesOrderItemDb); $c++) $salesOrderItemId[$c] = $salesOrderItemDb[$c]->id;

                                        // Cari DO Item
                                        $packingOrderItemDb = PackingORderItem::whereIn('so_item_id', $salesOrderItemId)->get();
                                        for($c=0; $c<count($packingOrderItemDb); $c++) $ids[$c] = $packingOrderItemDb[$c]->do_id;

                                        $fieldDb = 'id';
                                    }
                                    
                                    if ($fieldDb != '') {
                                        $query2->where(function($query3)  use ($field, $fieldDb, $ids){
                                            if ($field == 'sales') {
                                                $query3->where('sales_senior_id',$ids);
                                                $query3->orWhereIn('sales_id',$ids);
                                            } else {
                                                $query3->whereIn($fieldDb, $ids);
                                            }
                                        });
                                    } else {
                                        $query2->where($field, 'like', '%'.$search.'%');
                                    }
                                }
                            })
                            ->whereIn('status', [3, 4, 5, 6])
                            ->orderBy('id','DESC')
                            ->paginate(10);
        /*$table = PackingOrder::where(function($query2) use($customer_id){
                                if(!empty($customer_id)){
                                    $query2->where('customer_id',$customer_id);
                                }
                            })
                            ->where(function($query2) use($search){
                                if(!empty($search)){
                                    $query2->where('code','like','%'.$search.'%');
                                    $query2->orWhere('do_code','like','%'.$search.'%');
                                }
                            })
                            ->whereIn('status', [3, 4, 5, 6])
                            ->orderBy('id','DESC')
                            ->paginate(10);*/
        $table->withPath('delivery_order?field='.$field.'&search='.$search);
        $customer = Customer::all();
        $packing = PackingOrder::first();
        $data = [
            'table' => $table,
            'customer' => $customer,
            'packing' => $packing,
        ];
        return view($this->view."index",$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    public function detail($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();
        if(empty($result)){
            abort(404);
        }
        $data = [
            'result' => $result
        ];
        return view($this->view."detail_new",$data);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function print($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::where('id',$id)->first();
        
        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\do\\do.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\'.$result->do_code.'.pdf';

        $my_server = "DEV-SERVER"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";

        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{penjualan_do.id}= $result->id";


        //export to PDF process
        $creport->ExportOptions->DiskFileName=$my_pdf; //export to pdf
        $creport->ExportOptions->PDFExportAllPages=true;
        $creport->ExportOptions->DestinationType=1; // export to file
        $creport->ExportOptions->FormatType=31; // PDF type
        $creport->Export(false);

        //------ Release the variables ------
        $creport = null;
        $crapp = null;
        $ObjectFactory = null;

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\'.$result->do_code.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function packed(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $result = PackingOrder::where('id',$post["id"])->first();

            $do_cost = PackingOrderDetail::where('do_id', $result->id)->first();

            if($result->status == 1){
                return redirect()->route('superuser.penjualan.packing_order.index')->with('error','Tidak bisa mengirim packing order yang masih baru dibuat');
            }
            if(count($result->do_detail) == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Tidak ada item sama sekali');
            }
            if($do_cost->grand_total_idr == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Harga didalam packing list belum di set');
            }
            $update = PackingOrder::where('id',$post["id"])->update(['status' => 4]);

            // Create Invoice
            if ($result->type_transaction == 'TEMPO'){
                $data = [
                    'code' => CodeRepo::generateInvoicing($result->do_code),
                    'do_id' => $result->id,
                    'customer_other_address_id' => $result->customer_other_address_id,
                    'grand_total_idr' => $do_cost->grand_total_idr,
                    'created_by' => Auth::id(),
                ];
                
                $insert = Invoicing::create($data);
            }

            DB::commit();
            return redirect()->route('superuser.penjualan.delivery_order.index')->with('success','Delivery Order berhasil diubah ke packed');

        }catch(\Throwable $e){
            // DD($e);
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function sending(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $result = PackingOrder::where('id',$post["id"])->first();
            $do_cost = PackingOrderDetail::where('do_id', $result->id)->first();

            PackingOrder::where('id',$result->id)->update([
                'date_sent' => date('Y-m-d')
            ]);

            if($result->status == 1){
                return redirect()->route('superuser.penjualan.packing_order.index')->with('error','Tidak bisa mengirim packing order yang masih baru dibuat');
            }
            if(count($result->do_detail) == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Tidak ada item sama sekali');
            }
            if($do_cost->grand_total_idr == 0){
                return redirect()->route('superuser.penjualan.delivery_order.index')->with('error','Harga didalam packing list belum di set');
            }
            $update = PackingOrder::where('id',$post["id"])->update(['status' => 5]);

            DB::commit();
            return redirect()->route('superuser.penjualan.delivery_order.index')->with('success','Delivery Order berhasil diubah ke delivery!');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function upload_image(Request $request) {
        $post = $request->all();
        $request->validate([
            'do_id' => 'required'
        ]);

        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->back()->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        if($request->method() == "POST"){
            DB::beginTransaction();

            try {
                $image = $request->file('image');

                if(!empty($image)){
                    $extension = $image->getClientOriginalExtension();
                    $valid_ext = ['jpeg','png','jpg','gif'];
    
                    if(!in_array(strtolower($extension), $valid_ext)){
                        return redirect()->route('superuser.penjualan.delivery_order.detail')->with('error',"Format image diperbolehkan yaitu jpeg,jpg,png,gif");
                    }
                
                    $data = [
                        'image' => (empty($image)) ? null : $image->store('images/delivery_order/expedition_receipt', 'public'),
                        'updated_by' => Auth::id(),
                    ];

                    $update = PackingOrder::where('id',$post["do_id"])->update($data);
                    
                    DB::commit();

                    return redirect()->route('superuser.penjualan.delivery_order.detail', $post['do_id'])->with('success','Image berhasil diupload');
                }
            }   catch(\Throwable $e){
                DB::rollback();
                return redirect()->back()->with('error',$e->getMessage());
            }
        }

        ResultData:
        return response()->json($data_json,200);
    }

    public function sent(Request $request){
        $data_json = [];
        $post = $request->all();

        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                $data_json["IsError"] = TRUE;
                $data_json["Message"] = 'Anda tidak punya akses untuk membuka menu terkait';
                goto ResultData;
            }
        }

        if($request->method() == "POST"){
            DB::beginTransaction();
            try{
                $image = $request->file('image');

                if(!empty($image)){
                    $extension = $image->getClientOriginalExtension();
                    $valid_ext = ['jpeg','png','jpg','gif'];
    
                    if(!in_array(strtolower($extension), $valid_ext)){
                        return redirect()->route('superuser.penjualan.delivery_order.detail')->with('error',"Format image diperbolehkan yaitu jpeg,jpg,png,gif");
                    }
                
                    $data = [
                        'image' => (empty($image)) ? null : $image->store('images/delivery_order/expedition_receipt', 'public'),
                        'updated_by' => Auth::id(),
                    ];

                    $update = PackingOrder::where('id',$post["do_id"])->update($data);
                }

                $delivery_cost_idr = (empty($post["delivery_cost_idr"])) ? 0 : $post["delivery_cost_idr"]; 
                $other_cost_idr = (empty($post["other_cost_idr"])) ? 0 : $post["other_cost_idr"];

                $result_cost = PackingOrderDetail::where('do_id',$post["do_id"])->first();

                // $grand_total_idr = ceil($result_cost->grand_total_idr - $result_cost->delivery_cost_idr - $result_cost->other_cost_idr + $delivery_cost_idr + $other_cost_idr);

                $update_cost = PackingOrderDetail::where('do_id',$post["do_id"])->update([
                    'delivery_cost_note' => trim(htmlentities($post["delivery_cost_note"])),
                    // 'delivery_cost_idr' => $delivery_cost_idr,
                    'other_cost_note' => trim(htmlentities($post["other_cost_note"])),
                    'other_cost_idr' => $other_cost_idr,
                    // 'grand_total_idr' => $grand_total_idr,
                    'updated_by' => Auth::id(),
                ]);

                $detail_do = PackingOrder::where('id',$post["do_id"])->first();
                $detail_item = PackingOrderItem::where('do_id',$post["do_id"])->get();

                if(empty($detail_do->do_code)){
                    $detail_do->update([
                        'do_code' => CodeRepo::generateDO(),
                        'date_sent' => date('Y-m-d')
                    ]);
                }

                $detail_do = PackingOrder::where('id',$post["do_id"])->first();

                foreach ($detail_item as $key => $value) {
                    // Definisi stock sebelum pemotongan
                    $stock_product = ProductMinStock::where('product_id',$value->product_id)
                                                    ->where('warehouse_id',$detail_do->warehouse_id)
                                                    ->sum('quantity');
                    // Definisi membaca product dan warehouse
                    $move = StockMove::where('product_id',$value->product_id)
                                        ->where('warehouse_id',$detail_do->warehouse_id)->get();
                    // defisini stock in atau out
                    $move_in = $move->sum('stock_in');
                    $move_out = $move->sum('stock_out');
                    // Pemotongan stock
                    $sisa = (int)$stock_product + $move_in - $move_out - $value->qty;
                    // Pencatatan stock setelah di potong
                    $insert_stock_move = StockMove::create([
                        'code_transaction' => $detail_do->do_code,
                        'warehouse_id' => $detail_do->warehouse_id,
                        'product_id' => $value->product_id,
                        'stock_out' => $value->qty,
                        'stock_balance' => $sisa,
                        'created_by' => Auth::id()
                    ]);
                }


                $update = PackingOrder::where('id',$post["do_id"])->update(['status' => 6]);
              
                DB::commit();

                return redirect()->route('superuser.penjualan.delivery_order.index')->with('success','DO berhasil update resi!');

            }catch(\Throwable $e){
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

    public function get_cost(Request $request){
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
            try{
                if(empty($post["do_id"])){
                    $data_json["IsError"] = TRUE;
                    $data_json["Message"] = "Order ID tidak boleh kosong";
                    goto ResultData;
                }

                $get = PackingOrderCost::where('do_id',$post["do_id"])->get();

                $data_json["IsError"] = FALSE;
                $data_json["Data"] = $get;

            }catch(\Throwable $e){
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
    
    Public function print_label($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::find($id);

        if($result == null){
            abort(404);
        }

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\do\\label_penerima.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelKirim-'.$result->member->name.'.pdf';

        $my_server = "DEV-SERVER"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";


        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{penjualan_do.id}= $result->id";


        //export to PDF process
        $creport->ExportOptions->DiskFileName=$my_pdf; //export to pdf
        $creport->ExportOptions->PDFExportAllPages=true;
        $creport->ExportOptions->DestinationType=1; // export to file
        $creport->ExportOptions->FormatType=31; // PDF type
        $creport->Export(false);

        //------ Release the variables ------
        $creport = null;
        $crapp = null;
        $ObjectFactory = null;

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\do\\export\\LabelKirim-'.$result->member->name.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function print_manifest($id){
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $result = PackingOrder::find($id);
        
        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\packing_plan\\packing_plan_rev.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\packing_plan\\export\\'.$result->code.'.pdf';

        $my_server = "DEV-SERVER"; 
        $my_user = "root"; 
        $my_password = ""; 
        $my_database = "ppi-dist";
        $COM_Object = "CrystalDesignRunTime.Application";


        //-Create new COM object-depends on your Crystal Report version
        $crapp= New COM($COM_Object) or die("Unable to Create Object");
        $creport = $crapp->OpenReport($my_report,1); // call rpt report

        //- Set database logon info - must have
        $creport->Database->Tables(1)->SetLogOnInfo($my_server, $my_database, $my_user, $my_password);

        //- field prompt or else report will hang - to get through
        $creport->EnableParameterPrompting = FALSE;
        $creport->RecordSelectionFormula = "{penjualan_do.id}= $result->id";


        //export to PDF process
        $creport->ExportOptions->DiskFileName=$my_pdf; //export to pdf
        $creport->ExportOptions->PDFExportAllPages=true;
        $creport->ExportOptions->DestinationType=1; // export to file
        $creport->ExportOptions->FormatType=31; // PDF type
        $creport->Export(false);

        //------ Release the variables ------
        $creport = null;
        $crapp = null;
        $ObjectFactory = null;

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\packing_plan\\export\\'.$result->code.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function cancel_proses(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_approve == 0){
                return redirect()->route('superuser.penjualan.sales_order.index_lanjutan')->with('error','Anda tidak mempunyai akses untuk melakukan proses ini!');
            }
        }

        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $result = PackingOrder::where('id',$post["id"])->first();

            if($result->status == 2){
                return redirect()->route('superuser.penjualan.sales_order.index_lanjutan')->with('error','Tidak bisa Cancel DO yang masih baru dibuat');
            }
            if(count($result->do_detail) == 0){
                return redirect()->route('superuser.penjualan.sales_order.index_lanjutan')->with('error','Tidak ada item sama sekali');
            }
            if($result->do_cost->grand_total_idr == 0){
                return redirect()->route('superuser.penjualan.sales_order.index_lanjutan')->with('error','Harga didalam packing list belum di set');
            }
            $update = PackingOrder::where('id',$post["id"])->update([
                'status' => 7,
                'count_cancel' => 1
            ]);

            DB::commit();
            return redirect()->route('superuser.penjualan.sales_order.index_lanjutan')->with('success','DO berhasil di Cancel!');

            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function do_edit(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $post = $request->all();
        $result = PackingOrder::where('id', $post["id"])->first();
        $ekspedisi = Vendor::where('type', 1)->get();
        $warehouse = Warehouse::get();

        if(empty($result)){
            abort(404);
        }
        $data = [
            'result' => $result,
            'ekspedisi' => $ekspedisi,
            'warehouse' => $warehouse,
        ];
        return view($this->view."do_update",$data);
    }

    public function do_update(Request $request)
    {
        if ($request->ajax()) {
            $failed = "";

            DB::beginTransaction();

            try{
                // akses
                if(Auth::user()->is_superuser == 0){
                    if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                        return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                    }
                }

                $result = PackingOrder::where('id', $request->id)->first();

                if ($result === null) {
                    abort(404);
                }

                if($result->count_cancel == 0){
                    return redirect()->route('superuser.penjualan.sales_order.index_lanjutan')->with('error','DO belum di cancel');
                }else{
                    // update do
                    if(empty($request->idr_rate)){
                        $response['failed'] = 'IDR RATE tidak boleh kosong!';
                        return $this->response(200, $response);
                    }

                    if(empty($request->warehouse_id)){
                        $response['failed'] = 'WAREHOUSE tidak boleh kosong!';
                        return $this->response(200, $response);
                    }

                    $result->warehouse_id = $request->warehouse_id;
                    $result->idr_rate = $request->idr_rate;
                    $result->count_cancel = 2;
                    $result->status = 4;
                    $result->updated_by = Auth::id();

                    // update do item
                    $check_cost = PackingOrderDetail::where('id', $request->cost_id)->first();
                    $check_po_item = PackingOrderItem::where('do_id',$check_cost->do_id)->get();

                    if(count($check_po_item) <= 0){
                        $response['failed'] = 'Item DO tidak ditemukan';
                        return $this->response(200, $response);
                    }

                    foreach($request->repeater as $key => $value ){
                        if(empty($value["usd_disc"])){
                            continue;
                        }

                        $packingOrderItem = PackingOrderItem::where('id', $value["id"])->first();
                        if (empty($packingOrderItem) || !isset($packingOrderItem)) continue;

                        if ($packingOrderItem->percent_disc > 0) {
                            $total_disc = floatval(($value["usd_disc"] + (($packingOrderItem->price - $value["usd_disc"]) * ($packingOrderItem->percent_disc/100))) * $value["do_qty"]);
                        } else {
                            $total_disc = floatval($value["usd_disc"] * $value["do_qty"]);
                        }
                        $data = [
                            'usd_disc' => $value["usd_disc"],
                            'qty' => $value["do_qty"],
                            'total_disc' => $total_disc,
                            'total' => ($packingOrderItem->price * $value["do_qty"]) - $total_disc,
                        ];

                        // DD($value["do_qty"]);
                        $update = PackingOrderItem::where('id', $value["id"])->update($data);
                    }

                    // update do cost
                    $detail_po = PackingOrder::where('id', $check_cost->do_id)->first();
                    $detail_po_item = PackingOrderItem::where('do_id', $check_cost->do_id)->get();

                    $idr_total = 0;
                    foreach ($detail_po_item as $key => $row) {
                        $idr_total += ceil((($row->price * $detail_po->idr_rate) * $row->qty) - ($row->total_disc * $detail_po->idr_rate)); 
                    }

                    $discount_1 = $request->disc_agen_percent / 100;
                    $discount_2 = $request->disc_tambahan / 100;
                    $discount_idr = $request->disc_idr;
                    $voucher_idr = $request->voucher_idr;
                    $delivery_cost_idr = $request->delivery_cost_idr;
                    $other_cost_idr = $request->resi_ongkir;

                    $total_discount_idr = ceil(( $idr_total * $discount_1 ) + (($idr_total - ($idr_total * $discount_1)) * $discount_2) + $discount_idr);
                    
                    $purchase_total_idr = ceil($idr_total - $total_discount_idr - $voucher_idr);
                    $grand_total_idr = ceil($purchase_total_idr + $delivery_cost_idr + $other_cost_idr);
                    
                    if($total_discount_idr > $grand_total_idr){
                        $response['failed'] = 'Total Discount melebihi IDR total item pembelian';
                        return $this->response(200, $response);
                    }

                    $data = [
                        'discount_1' => $request->disc_agen_percent,
                        'discount_1_idr' => $request->disc_amount2_idr,
                        'discount_2' => $request->disc_tambahan,
                        'discount_2_idr' => $request->disc_kemasan_idr,
                        'discount_idr' => $discount_idr,
                        'total_discount_idr' => $total_discount_idr,
                        'voucher_idr' => $voucher_idr,
                        'purchase_total_idr' => $purchase_total_idr,
                        'delivery_cost_idr' => $delivery_cost_idr,
                        'other_cost_idr' => $other_cost_idr,
                        'grand_total_idr' => $grand_total_idr,
                        'updated_by' => Auth::id()
                    ];
                    $update = PackingOrderDetail::where('do_id', $request->id)->update($data);

                    // update Proforma
                    if($detail_po->proforma->grand_total_idr > 0){
                        $data = [
                            'grand_total_idr' => $grand_total_idr,
                        ];

                        $updateProforma = SoProforma::where('do_id', $request->id)->update($data);

                        // update proforma detail
                        foreach($request->repeater as $key => $value ){

                            $get = SoProforma::where('do_id', $request->id)->first();
                            $getDetail = SoProformaDetail::where('so_proforma_id', $get->id)->get();

                            $data = [
                                'qty' => $value["do_qty"],
                            ];

                            $update = SoProformaDetail::where('so_proforma_id', $get->id)->update($data);
                        }
                    }
                }

                if ($failed) {
                    $response['failed'] = $failed;

                    return $this->response(200, $response);
                }

                if($result->save()){
                    DB::commit();
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success, DO berhasil di update',
                    ];
                    
                    $response['redirect_to'] = route('superuser.penjualan.sales_order.index_lanjutan');
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
}
