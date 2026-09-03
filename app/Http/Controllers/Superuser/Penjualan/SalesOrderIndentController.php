<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Exports\Penjualan\SalesOrderIndentExport;
use App\Entities\Setting\UserMenu;
use Excel;
use Auth;
use DB;
use PDF;
use COM;
use Carbon;

class SalesOrderIndentController extends Controller
{
    public function __construct(){
        $this->view = "superuser.penjualan.sales_order_indent.";
        $this->route = "superuser.penjualan.sales_order_indent";
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
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $query = SalesOrder::where('so_indent', SalesOrder::INDENT['YES'])
            ->where('is_archived', 0);

        // Filter berdasarkan division: Developer/Management lihat semua, lainnya hanya lihat miliknya
        $userDivision = Auth::user()->division;
        if(!in_array($userDivision, ['Developer', 'Management'])){
            $query->where('created_by', Auth::id());
        }

        $sales_order = $query->get();

        $data = [
            'sales_order' => $sales_order,
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
    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
                }
            }

            DB::beginTransaction();
            try{

                $sales_order = SalesOrder::find($id);

                if($sales_order == null){
                    abort(404);
                }

                $sales_order->deleted_by = Auth::id();
                $sales_order->condition = 0;
                $sales_order->delete();


                foreach($sales_order->so_detail as $detail){
                    $item = SalesOrderItem::where('id', $detail->id)->get();

                    foreach($item as $data){
                        SalesOrderItem::find($data->id)->delete();
                    }
                }

                if($sales_order->save()){
                    DB::commit();
                    $response['redirect_to'] = route('superuser.penjualan.sales_order_indent.index');
                    return $this->response(200, $response);
                }

            }catch (\Exception $e) {
                DB::rollback();
                DD($e);
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

    public function export(Request $request)
    {
        $filename = 'Sales-Order-Indent-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new SalesOrderIndentExport, $filename);
    }

    public function print_out_indent(Request $request, $so_id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_print == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $sales_order = SalesOrder::find($so_id);

        $my_report = "C:\\xampp\\htdocs\\ppi-dist\public\\cr\\so\\so_indent.rpt"; 
        $my_pdf = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\so\\export\\'.$sales_order->so_code.'-INDENT'.'.pdf';

        //- Variables - Server Information 
        $my_server = "LOCAL"; 
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
        $creport->RecordSelectionFormula = "{penjualan_so.id}= $sales_order->id";

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

        $file = 'C:\\xampp\\htdocs\\ppi-dist\\public\\cr\\so\\export\\'.$sales_order->so_code.'-INDENT'.'.pdf';

        header("Content-Description: File Transfer"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 
        ob_clean();
        flush();
        readfile ($file);
        exit();
    }

    public function proses_ready(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate input directly
            $validated = $request->validate([
                'so_item_id.*' => 'required|exists:penjualan_so_item,id',
                'qty.*' => 'required|integer|min:0',
            ]);

            $so_item_ids = $validated['so_item_id'];
            $qtys = $validated['qty'];

            // Retrieve all sales order items in a single query to improve performance
            $salesOrderItems = SalesOrderItem::whereIn('id', $so_item_ids)
                                            ->pluck('qty', 'id')
                                            ->toArray();  // Efficient query to get 'id' => 'qty' pairs

            foreach ($so_item_ids as $id) {
                $qty = $qtys[$id] ?? null;

                // Check if quantity is valid (already validated by the request)
                if ($qty === null || $qty < 0) {
                    session()->flash('notification', [
                        'alert' => 'notify',
                        'type' => 'error',
                        'content' => 'Jumlah tidak valid untuk item yang dipilih!',
                    ]);

                    return $this->response(400, $response);
                }

                // Get the previous qty and calculate qty_worked
                $qty_before = $salesOrderItems[$id] ?? 0;  // Default to 0 if not found

                // Debug: Check if the qty_before and qty are correct
                \Log::info('Item ID: ' . $id . ' - qty_before: ' . $qty_before . ' - qty: ' . $qty);

                $qty_worked = $qty_before - $qty;  // Calculate worked quantity

                // Debug: Log the value of qty_worked
                \Log::info('qty_worked for item ID ' . $id . ': ' . $qty_worked);

                // Update the sales order item with both qty and qty_worked
                $updateResult = SalesOrderItem::where('id', $id)
                                            ->update([
                                                'qty' => $qty,
                                                'qty_worked' => $qty_worked,
                                            ]);

                // Check if the update was successful
                if ($updateResult === 0) {
                    \Log::error('Failed to update SalesOrderItem for item ID ' . $id);
                }
            }

            DB::commit();

            // Set success notification in session and redirect
            session()->flash('notification', [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Success',
            ]);

            return redirect()->route('superuser.penjualan.sales_order_indent.index');
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollback();

            // Error response
            session()->flash('notification', [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => 'An error occurred: ' . $e->getMessage(),
            ]);

            return redirect()->route('superuser.penjualan.sales_order_indent.index');
        }
    }

    /**
     * Archive satu SO Indent manual
     */
    public function archive_one($id)
    {
        $sales_order = SalesOrder::findOrFail($id);

        $sales_order->update([
            'is_archived' => 1,
            'archived_at' => now(),
        ]);

        return redirect()->route('superuser.penjualan.sales_order_indent.index')
            ->with('success', 'SO Indent berhasil diarsipkan.');
    }

    /**
     * Kembalikan SO Indent dari archive (restore)
     */
    public function restore($id)
    {
        $sales_order = SalesOrder::findOrFail($id);

        $sales_order->update([
            'is_archived' => 0,
            'archived_at' => null,
        ]);

        return redirect()->route('superuser.penjualan.sales_order_indent.archive')
            ->with('success', 'SO Indent berhasil dikembalikan.');
    }

    /**
     * Tampilkan riwayat SO Indent yang sudah diarsipkan (invisible)
     */
    public function archive()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $query = SalesOrder::where('so_indent', SalesOrder::INDENT['YES'])
            ->where('is_archived', 1);

        // Filter berdasarkan division: Developer/Management lihat semua, lainnya hanya lihat miliknya
        $userDivision = Auth::user()->division;
        if(!in_array($userDivision, ['Developer', 'Management'])){
            $query->where('created_by', Auth::id());
        }

        $archives = $query->orderBy('archived_at', 'desc')->get();

        $data = [
            'archives' => $archives,
        ];

        return view($this->view . 'archive', $data);
    }

    /**
     * Print ulang estimate PDF dari data archive
     */
    public function archive_print_estimate($id)
    {
        $so = SalesOrder::with(['so_detail.product_pack', 'member'])
            ->findOrFail($id);

        // Gunakan SalesOrderCalculationService
        $kalkulasiService = new \App\Services\SalesOrderCalculationService();
        $data_kalkulasi = $kalkulasiService->calculateEstimate($so);

        $terbilang = trim(\App\CustomHelper::terbilang($data_kalkulasi['grand_total']));

        $pdf = \PDF::loadView('superuser.penjualan.sales_order.pdf_sales_estimate', [
            'so'             => $so,
            'data_kalkulasi' => $data_kalkulasi,
            'terbilang'      => $terbilang,
            'idr_rate'       => $data_kalkulasi['idr_rate']
        ])->setPaper('A5', 'landscape');

        return $pdf->stream('Sales_Estimate_Archive_' . $so->so_code . '.pdf');
    }
}
