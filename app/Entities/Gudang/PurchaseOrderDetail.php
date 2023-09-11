<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    protected $table = "purchase_order_detail";

    protected $fillable = [
        'po_id', 
        'product_packaging_id', 
        'qty', 
        'packaging_id', 
        'note_produksi',
        'note_repack', 
        'note', 
        'created_by',
        'updated_by',
    ];

    public function product_pack(){
    	return $this->BelongsTo('App\Entities\Master\ProductPack','product_packaging_id','id');
    }

    public function packaging(){
    	return $this->BelongsTo('App\Entities\Master\Packaging','packaging_id','id');
    }

    public function po(){
    	return $this->BelongsTo('App\Entities\Master\PurchaseOrder','po_id','id');
    }
}
