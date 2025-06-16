<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ReceivingDetail extends Model
{
    protected $fillable = [
        'receiving_id', 
        'po_id',
        'product_packaging_id',
        'quantity_po',
        'quantity_ri',
        'selisih',
        'no_batch',
        'note'
    ];
    protected $table = 'receiving_detail';

    public function receiving()
    {
        return $this->belongsTo('App\Entities\Gudang\Receiving', 'receiving_id');
    }

    public function purchase_order()
    {
        return $this->belongsTo('App\Entities\Gudang\PurchaseOrder', 'po_id');
    }

    public function product_pack()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }
}