<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Account\Superuser as AccountSuperuser;

class SalesOrderKontrakPivot extends Model
{
    use SoftDeletes;
    
    public $incrementing = false;
    protected $table = "penjualan_so_kontrak_pivot";
    protected $fillable = [
        'so_item_id', 
        'so_kontrak_id', 
        'so_kontrak_item_id', 
    ];
}