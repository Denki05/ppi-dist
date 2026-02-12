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
        'ref_po_id',
        'etd', 
        'note', 
        'edit_counter', 
        'type',
        'sub_type',
        'count_send_spk',
        'kategori',
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
        'SENT' => 4,
    ];

    const TYPE = [
        'SPK' => 0,
        'PO' => 1,
    ];

    const KATEGORI = [
        'PRODUKSI_REPACK' => 0,
        'ORIGINAL_PACK' => 1,
    ];

    // const SUB_TYPE = [
    //     'SIRIE_NGINDEN' => 0,
    //     'ARAYA_NGINDEN' => 1,
    // ];

     const SUB_TYPE = [
        'INDUSTRI' => 1,
        'NON_INDUSTRI' => 0,
    ];

    public function warehouse(){
    	return $this->BelongsTo('App\Entities\Master\Warehouse','warehouse_id','id');
    }

    public function purchase_order_detail(){
        return $this->hasMany('App\Entities\Gudang\PurchaseOrderDetail', 'po_id', 'id');
    }

    public function receiving_detail(){
        return $this->hasMany('App\Entities\Gudang\ReceivingDetail', 'po_id', 'id');
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

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function kategori()
    {
        return array_search($this->kategori, self::KATEGORI);
    }

    public function sub_type()
    {
        return array_search($this->sub_type, self::SUB_TYPE);
    }
}