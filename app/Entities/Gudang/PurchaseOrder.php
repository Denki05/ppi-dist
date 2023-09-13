<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class PurchaseOrder extends Model
{

    protected $table = "purchase_order";
    protected $fillable = [
    	'code',
        'warehouse_id', 
        'brand_lokal_id',
        'etd', 
        'note', 
        'edit_counter', 
        'edit_marker', 
        'status', 
        'created_by', 
        'updated_by',
        'created_date',
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2,
        'DRAFT' => 3,
    ];

    public function warehouse(){
    	return $this->BelongsTo('App\Entities\Master\Warehouse','warehouse_id','id');
    }

    public function purchase_order_detail(){
        return $this->hasMany('App\Entities\Gudang\PurchaseOrderDetail', 'po_id', 'id');
    }
    
    public function updateBySuperuser()
    {
        $superuser = Superuser::find($this->updated_by);

        if($superuser){
            return $superuser->name ?? $superuser->username;
        }
    }

    public function requestBySuperuser()
    {
        $superuser = Superuser::find($this->updated_by);

        if($superuser){
            return $superuser->name ?? $superuser->username;
        }
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }
}
