<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\SoProforma;
use App\Entities\Penjualan\SoProformaDetail;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Setting\UserMenu;
use Auth;
use DB;

class ProformaController extends Controller
{
    public function __construct(){
        $this->view = "superuser.finance.proforma.";
        $this->route = "superuser.finance.proforma";
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
           if(empty($this->access)){
               return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
           }
        }

        $proforma = SoProforma::get();
        $data = [
            'proforma' => $proforma
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

    public function cancel(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        DB::beginTransaction();
        try{

            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();

            $proforma = SoProforma::where('id', $post["id"])->first();

            if ($proforma->status === 1){
                return redirect()->back()->with('error','Failed to delete because it was paid off');
            }elseif($proforma->status == 1){
                $update = SoProforma::where('id', $proforma->id)->update(['deleted_by' => Auth::id(), 'status' => 3]);
                $delete = SoProforma::where('id', $proforma->id)->delete();

                $delete_item = SoProformaDetail::where('so_proforma_id', $proforma->id)->delete();
            }

            $so = SalesOrder::where('id', $proforma->id)->first();
            $update_so = SalesOrder::where('id', $so->id)->update(['status' => 2]);

            $po = PackingOrder::where('so_id', $so->id)->first();

            if($po->status === 2){
                $update_po = PackingOrder::where('id', $po->id)->update(['deleted_by' => Auth::id()]);
                $delete_po = PackingOrder::where('id', $po->id)->delete();

                $delete_detail = PackingOrderDetail::where('do_id', $po->id)->delete();
                $delete_item = PackingOrderItem::where('do_id', $po->id)->delete();
            }
            
            DB::commit();
            return redirect()->back()->with('success','Proforma berhasil di Cancel!');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
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
        //
    }
}
