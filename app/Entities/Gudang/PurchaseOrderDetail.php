<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    protected $table = "purchase_order_detail";

    protected $fillable = [
        'po_id', 
        'product_id', 
        'qty', 
        'packaging_id', 
        'note_produksi',
        'note_repack', 
        'note', 
        'created_by',
        'updated_by',
    ];

    public function product(){
    	return $this->BelongsTo('App\Entities\Master\Product','product_id','id');
    }

    public function packaging(){
    	return $this->BelongsTo('App\Entities\Master\Packaging','packaging_id','id');
    }

    public function po(){
    	return $this->BelongsTo('App\Entities\Master\PurchaseOrder','po_id','id');
    }
}
