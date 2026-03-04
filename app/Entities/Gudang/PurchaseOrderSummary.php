<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class PurchaseOrderSummary extends Model
{

    protected $table = "purchase_order_summary";
    protected $fillable = [
    	'product_packaging_id',
        'po_id',
        'quantity',
        'status'
    ];

    const STATUS = [
        'DONE' => 1,
        'UNDONE' => 2,
    ];

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id');
    }

    public function po()
    {
        return $this->belongsTo('App\Entities\Gudang\PurchaseOrder', 'po_id');
    }
}