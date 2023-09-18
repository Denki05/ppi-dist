<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Master\ProductPack;

class PurchaseOrderDetail extends Model
{
    protected $table = "purchase_order_detail";

    protected $fillable = [
        'po_id', 
        'brand_lokal_id', 
        'product_packaging_id', 
        'quantity', 
        'packaging_id', 
        'note_produksi',
        'note_repack', 
        'note', 
        'created_by',
        'updated_by',
    ];

    public function product_pack()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }

    public function purchase_order()
    {
        return $this->belongsTo('App\Entities\Gudang\PurchaseOrder', 'po_id');
    }
}
