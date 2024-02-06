<?php

namespace App\Http\Controllers\Superuser\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Accounting\InvoiceTax;
use App\Entities\Accounting\InvoiceTaxDetail;
use App\Entities\Master\Mitra;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\ProductFinance;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;
use DB;

class InvoiceTaxController extends Controller
{
    public function __construct(){
        $this->view = "superuser.accounting.invoice_tax.";
        $this->route = "superuser.accounting.invoice_tax";
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

    public function search_invreal(Request $request)
    {
        $inv_real = PackingOrder::where('penjualan_do_details.status_resi', 1)
                                ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
                                ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                                ->select(
                                    'penjualan_do.id as id', 
                                    'penjualan_do.tax_beli as taxBeli', 
                                    'penjualan_do.tax_jual as taxJual', 
                                    'penjualan_do.do_code as invoiceReal', 
                                    'master_customer_other_addresses.name as customerName',
                                    'master_customer_other_addresses.text_kota as customerCity',
                                    'penjualan_do.idr_rate as kursRate',
                                )
                                ->get();

        // DD(DB::getQueryLog());
        $results = [];

        foreach ($inv_real as $item) {
            if($item->taxBeli == 0 OR $item->taxJual == 0 ){
                $results[] = [
                    'id' => $item->id,
                    'text' => $item->invoiceReal . ' - ' . $item->customerName . '  '. $item->customerCity,
                ];
            }
        }

        return ['results' => $results];
    }

    public function get_product(Request $request)
    {
        if ($request->ajax()) {
            $data = [];

            $packing_order = PackingOrder::find($request->id);
            $mpfinance = ProductFinance::get();

            foreach($packing_order->do_items as $row => $value){
                foreach($mpfinance as $item){
                    if($item->id === $value->product_pack->product_id){
                        $data[] = [
                            'id' => $item->id,
                            'name' => $item->name_product,
                            'code' => $item->code_product,
                            'kurs' => $value->do->idr_rate,
                            'qty' => $value->qty,
                            'selling_price_usd_drum' => $item->selling_price_usd_drum,
                            'buying_price_usd_drum' => $item->buying_price_usd_drum,
                            'selling_price_usd_unit' => $item->selling_price_usd_unit,
                            'buying_price_usd_unit' => $item->buying_price_usd_unit,
                        ];
                    }
                }
            }
            return response()->json(['code' => 200, 'data' => $data]);
        }
    }

    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['invoice_tax'] = InvoiceTax::get();

        return view($this->view."index", $data);
    }

    public function create(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['mitra'] = Mitra::get();

        return view($this->view."create", $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            DB::beginTransaction();
            $errors = [];

            $get_nota = PackingOrder::where('id', $request->delivery_order)->first();

            try{

                if($request->delivery_order == null){
                    $errors[] = 'INVOICE REAL, harus dipilih!';
                }

                if($request->idr_rate == null){
                    $errors[] = 'IDR RATE, harus diisi!';
                }

                if($request->mitra_id == null){
                    $errors[] = 'MITRA, HArus dipilih!';
                }

                if($request->type == null){
                    $errors[] = 'TYPE, harus diisi!';
                }

                if($request->invoice_tax_date == null){
                    $errors[] = 'TANGGAl, harus diisi!';
                }

                if($request->type == 1 AND $get_nota->tax_jual == 1){
                    $errors[] = 'INVOICE TAX JUAL, sudah terbuat!';
                }

                if($request->type == 2 AND $get_nota->tax_beli == 1){
                    $errors[] = 'INVOICE TAX BELI, sudah terbuat!';
                }

                $invoice_tax = new InvoiceTax;

                $invoice_tax->no_invoice_tax = $request->code;
                $invoice_tax->no_invoice_real = $request->delivery_order;
                $invoice_tax->customer_other_address_id =  $get_nota->customer_other_address_id;
                $invoice_tax->mitra_id = $request->mitra_id;
                $invoice_tax->tot_hit_baru = $request->sub_total_item;
                $invoice_tax->kurs = $request->idr_rate;
                $invoice_tax->invoice_tax_date = $request->invoice_tax_date;
                $invoice_tax->type = $request->type;
                $invoice_tax->note = $request->note;
                $invoice_tax->created_by = Auth::id();
                if ($invoice_tax->save()) {
                    if ($request->sku) {
                        foreach ($request->sku as $key => $value) {
                            if ($request->sku[$key] && $request->quantity[$key]) {
                                // check price
                                $productName = ProductFinance::where('id', $request->sku[$key])->first();

                                if($request->price_satuan[$key] == 0){
                                    $errors[] = 'Harga <b>'.$productName->name_product.'</b> , kosong silahkan setting dahulu di Setting Price!';
                                }

                                $invoice_tax_detail = new InvoiceTaxDetail;
                                $invoice_tax_detail->invoice_tax_id = $invoice_tax->id;
                                $invoice_tax_detail->product_tax_id = $request->sku[$key];
                                $invoice_tax_detail->qty = $request->quantity[$key];
                                if($request->type == 1){
                                    $invoice_tax_detail->selling_price_tax = $request->price_satuan[$key];
                                    $invoice_tax_detail->buying_price_tax = 0;
                                }elseif($request->type == 2){
                                    $invoice_tax_detail->selling_price_tax = 0;
                                    $invoice_tax_detail->buying_price_tax = $request->price_satuan[$key];
                                }
                                $invoice_tax_detail->kurs = $invoice_tax->kurs;
                                $invoice_tax_detail->subtotal = $request->subtotal[$key];
                                $invoice_tax_detail->created_by = Auth::id();
                                $invoice_tax_detail->save();
                            }
                        }
                    }

                    // update do/nota
                    $get_nota->mitra_id = $request->mitra_id;
                    $get_nota->status_mitra = 1;
                    if($request->type == 1){
                        $get_nota->tax_jual = 1;
                    }else{
                        $get_nota->tax_beli = 1;
                    }
                    $get_nota->save();

                    if($errors) {
                        $response['notification'] = [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => $errors,
                        ];
    
                        return $this->response(400, $response);
                    } else {
                        DB::commit();
                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
            
                        $response['redirect_to'] = route('superuser.accounting.invoice_tax.index');
                        return $this->response(200, $response);
                    }
                }
            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
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
}
