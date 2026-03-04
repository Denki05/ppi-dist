<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingQcLogs;

class QualityControlDetail extends Model
{
    protected $table = 'receiving_detail';
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
    
    public function receiving()
    {
        return $this->belongsTo(QualityControl::class); 
    }

    public function purchase_order()
    {
        return $this->belongsTo('App\Entities\Gudang\PurchaseOrder', 'po_id');
    }

    public function product_pack()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }

    public function qcLogs()
    {
        return $this->hasMany(QualityControlLogs::class, 'receiving_details_id');
    }
}