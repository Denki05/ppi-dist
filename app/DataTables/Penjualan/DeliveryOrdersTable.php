<?php

namespace App\DataTables\Penjualan;

use App\DataTables\Table;
use App\Entities\Penjualan\PackingOrder;
use Carbon\Carbon;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use DB;

class DeliveryOrdersTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        switch ($this->show) {
            case 'default':
              $model = PackingOrder::select(
                'id', 
                'do_code', 
                'customer_other_address_id', 
                DB::raw('
                    CASE 
                        WHEN status = 3 THEN "READY"
                        WHEN status = 4 THEN "PACKED"
                        WHEN status = 5 THEN "DELIVERING"
                        WHEN status = 6 THEN "DELIVERED"
                        ELSE "-"
                    END AS status
                '),
                'created_at')
                ->where('status', 3);
              
              if($request->from??false){
                  $model = $model->whereDate("created_at", ">=", $request->from)->whereDate("created_at", "<=", $request->to);
              }
              break;
            case 'acc':
                $model = PackingOrder::select(
                    'id', 
                    'do_code', 
                    'customer_other_address_id', 
                    DB::raw('
                        CASE 
                            WHEN status = 3 THEN "READY"
                            WHEN status = 4 THEN "PACKED"
                            WHEN status = 5 THEN "DELIVERING"
                            WHEN status = 6 THEN "DELIVERED"
                            ELSE "-"
                        END AS status
                    '),
                    'created_at')
                    ->where('status', 4);
                  
                  if($request->from??false){
                      $model = $model->whereDate("created_at", ">=", $request->from)->whereDate("created_at", "<=", $request->to);
                  }
              break;
            case 'all':
                $model = PackingOrder::select(
                    'id', 
                    'do_code', 
                    'customer_other_address_id', 
                    DB::raw('
                        CASE 
                            WHEN status = 3 THEN "READY"
                            WHEN status = 4 THEN "PACKED"
                            WHEN status = 5 THEN "DELIVERING"
                            WHEN status = 6 THEN "DELIVERED"
                            ELSE "-"
                        END AS status
                    '),
                    'created_at')
                    // ->whereIn('status', [5, 6]);
                    ->where('status', 5);
                  
                  if($request->from??false){
                      $model = $model->whereDate("created_at", ">=", $request->from)->whereDate("created_at", "<=", $request->to);
                  }
              break;
            case 'update':
                $model = PackingOrder::select(
                    'id', 
                    'do_code', 
                    'customer_other_address_id', 
                    DB::raw('
                        CASE 
                            WHEN status = 3 THEN "READY"
                            WHEN status = 4 THEN "PACKED"
                            WHEN status = 5 THEN "DELIVERING"
                            WHEN status = 6 THEN "DELIVERED"
                            ELSE "-"
                        END AS status
                    '),
                    'created_at'
                )
                ->where('status', 6);
            
                // Jika user memilih tanggal manually
                if ($request->from ?? false) {
            
                    $model = $model->whereDate("created_at", ">=", $request->from)
                                   ->whereDate("created_at", "<=", $request->to);
            
                } else {
            
                    // Default: ambil bulan berjalan (current month)
                    $model = $model->whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year);
                }
            
            break;            
            default:
            $model = PackingOrder::select(
                'id', 
                'do_code', 
                'customer_other_address_id', 
                DB::raw('
                    CASE 
                        WHEN status = 3 THEN "READY"
                        WHEN status = 4 THEN "PACKED"
                        WHEN status = 5 THEN "DELIVERING"
                        WHEN status = 6 THEN "DELIVERED"
                        ELSE "-"
                    END AS status
                '),
                'created_at')
                ->where('status', 3);
              
              if($request->from??false){
                  $model = $model->whereDate("created_at", ">=", $request->from)->whereDate("created_at", "<=", $request->to);
              }
              break;
          }
  
          return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));
        $table->addIndexColumn();

        $table->setRowClass(function (PackingOrder $model) {
            return '';
        });

        $table->editColumn('customer_other_address_id', function (PackingOrder $model) {
            $member = $model->member;
            
            if ($member) {
                $name = $member->name;
                $city = $member->text_kota;
                
                return implode(' ', [$name, $city]);
            }
            
            return '';
        });

        $table->editColumn('created_at', function (PackingOrder $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (PackingOrder $model) {
            $kerjakan = route('superuser.penjualan.delivery_order.detail', $model->id);
            // $print_sj = route('superuser.penjualan.delivery_order.detail', $model->id);
            // $update_resi = route('superuser.penjualan.delivery_order.detail', $model->id);

            switch ($model->status) {
                case $model->status == "READY":
                    return "
                        <a href=\"{$kerjakan}\">
                            <button type=\"button\" class=\"btn btn-primary btn-sm btn-flat\" title=\"Kerjakan\">
                                <i class=\"fas fa-box\"></i>
                            </button>
                        </a>
                    ";

                case $model->status == "PACKED":
                    return "
                        <a href=\"{$kerjakan}\">
                            <button type=\"button\" class=\"btn btn-primary btn-sm btn-flat\" title=\"Surat Jalan\">
                                <i class=\"fas fa-shipping-timed\"></i>
                            </button>
                        </a>
                    ";

                case $model->status == "DELIVERING":
                    return "
                        <a href=\"{$kerjakan}\">
                            <button type=\"button\" class=\"btn btn-primary btn-sm btn-flat\" title=\"Update Resi\">
                                <i class=\"fas fa-money\"></i>
                            </button>
                        </a>
                    ";

                case $model->status == "DELIVERED":
                        return "
                            <a href=\"{$kerjakan}\">
                                <button type=\"button\" class=\"btn btn-primary btn-sm btn-flat\" title=\"Update Resi\">
                                    <i class=\"fas fa-money\"></i>
                                </button>
                            </a>
                        ";
            }
        });
        $table->rawColumns(['action']);

        return $table->make(true);
    }
}