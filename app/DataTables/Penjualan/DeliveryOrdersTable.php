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
                'code',
                'customer_other_address_id', 
                'print_count',
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
                    'code',
                    'customer_other_address_id', 
                    'print_count',
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
                    'code',
                    'customer_other_address_id', 
                    'print_count',
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
            case 'history':
                $model = PackingOrder::select(
                    'id', 
                    'do_code', 
                    'code',
                    'customer_other_address_id', 
                    'date_sent',
                    'updated_at',
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
                    ->where('status', 6);

                  if($request->from??false){
                      $model = $model->whereDate("created_at", ">=", $request->from)->whereDate("created_at", "<=", $request->to);
                  }
              break;
            default:
            $model = PackingOrder::select(
                'id', 
                'do_code', 
                'code',
                'customer_other_address_id', 
                'print_count',
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

        $table->editColumn('date_sent', function (PackingOrder $model) {
            if (empty($model->date_sent)) {
                return '-';
            }
            return Carbon::parse($model->date_sent)->format('j F Y');
        });

        $table->addColumn('action', function (PackingOrder $model) {
            $kerjakan = route('superuser.penjualan.delivery_order.detail', $model->id);
            $print_manifest = route('superuser.penjualan.delivery_order.print_manifest', $model->id);

            switch ($model->status) {
                case $model->status == "READY":
                    // Belum pernah print SPK -> tombol Kerjakan dikunci,
                    // arahkan dulu ke tombol Print SPK.
                    if ((int) $model->print_count === 0) {
                        return "
                            <a href=\"{$print_manifest}\" target=\"_blank\">
                                <button type=\"button\" class=\"btn btn-warning btn-sm btn-flat\" title=\"Print SPK dulu\">
                                    <i class=\"fas fa-print\"></i> Print SPK
                                </button>
                            </a>
                            <button type=\"button\" class=\"btn btn-secondary btn-sm btn-flat\" title=\"Print SPK dulu sebelum bisa dikerjakan\" disabled>
                                <i class=\"fas fa-box\"></i>
                            </button>
                        ";
                    }

                    // Sudah pernah print SPK -> tombol Kerjakan aktif.
                    return "
                        <a href=\"{$print_manifest}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-outline-secondary btn-sm btn-flat\" title=\"Print Ulang SPK\">
                                <i class=\"fas fa-print\"></i>
                            </button>
                        </a>
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
                                <i class=\"fa fa-check\"></i>
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
                            <button type=\"button\" class=\"btn btn-outline-secondary btn-sm btn-flat\" title=\"Lihat Detail\">
                                <i class=\"fas fa-eye\"></i>
                            </button>
                        </a>
                    ";
            }
        });
        $table->rawColumns(['action']);

        return $table->make(true);
    }
}