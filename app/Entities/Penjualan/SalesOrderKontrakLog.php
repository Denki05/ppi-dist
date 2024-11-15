<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Account\Superuser as AccountSuperuser;

class SalesOrderKontrakLog extends Model
{
    use SoftDeletes;
    
    public $incrementing = false;
    protected $table = "penjualan_so_kontrak_log";
    protected $fillable = [
        'code', 
        'customer_other_address_id', 
        'so_kontrak_id', 
        'so_id', 
        'qty_worked', 
        'outstanding_qty',
    ];

   

    public function so_regular(){
        return $this->hasMany('App\Entities\Penjualan\SalesOrder', 'so_id', 'id');
    }

    public function so_kontrak(){
        return $this->hasMany('App\Entities\Penjualan\SalesOrder', 'so_kontrak_id', 'id');
    }
    
    public function member(){
        return $this->BelongsTo('App\Entities\Master\CustomerOtherAddress','customer_other_address_id','id');
    }
}