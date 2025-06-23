<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use App\Entities\Gudang\MutasiOut;
use App\Entities\Gudang\MutasiOutDetail;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Warehouse;
use Validator;
use Carbon\Carbon;
use Auth;
use DB;

class MutasiOutController extends Controller
{
    public function __construct(){
        $this->view = "superuser.gudang.mutasi_out.";
        $this->route = "superuser.gudang.mutasi_out";
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

    public function search_sku(Request $request)
    {
        if (!$request->warehouse) {
            return ['results' => []];
        }

        $products = ProductPack::where('master_products_packaging.name', 'LIKE', '%' . $request->input('q', '') . '%')
            ->join('master_product_min_stocks', function ($join) use ($request) {
                $join->on('master_products_packaging.id', '=', 'master_product_min_stocks.product_packaging_id')
                    ->where('master_product_min_stocks.warehouse_id', $request->warehouse)
                    ->where('master_product_min_stocks.quantity', '>', 0);
            })
            ->join('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->get([
                'master_products_packaging.id    as id',
                'master_products_packaging.code  as code',
                'master_products_packaging.name  as name',
                'master_packaging.pack_name      as pack',
                'master_product_min_stocks.quantity as stock',
            ])
            ->map(function ($row) {
                // kolom “text” yang akan ditampilkan default oleh Select2
                $row->text = "{$row->code} – {$row->name} ({$row->pack})";
                return $row;
            });

        return ['results' => $products];
    }


    public function index(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view($this->view."index");
    }

    public function create(Request $request)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['warehouse_to'] = Warehouse::pluck('name', 'id');
        $data['warehouse_from'] = Warehouse::pluck('name', 'id');

        return view($this->view."create", $data);
    }
}