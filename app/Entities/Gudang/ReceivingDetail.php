<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ReceivingDetail extends Model
{
    protected $fillable = [
        'receiving_id', 'po_id', 'po_detail_id', 'product_packaging_id', 'quantity', 'total_quantity_ri', 'total_quantity_colly', 'delivery_cost', 'sj_po' ,'description', 'total_ri_idr', 'grand_total'
    ];
    protected $table = 'receiving_detail';

    public static function boot() {
        parent::boot();

        static::deleting(function($receiving_detail) {
             $receiving_detail->collys()->delete();
        });
    }

    public function receiving()
    {
        return $this->belongsTo('App\Entities\Gudang\Receiving', 'receiving_id');
    }

    public function purchase_order()
    {
        return $this->belongsTo('App\Entities\Gudang\PurchaseOrder', 'po_id');
    }

    public function ppb_detail()
    {
        return $this->belongsTo('App\Entities\Gudang\PurchaseOrderDetail', 'ppb_detail_id');
    }

    public function product_pack()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id');
    }

    public function collys()
    {
        return $this->hasMany('App\Entities\Gudang\ReceivingDetailColly');
    }

    public function total_reject_ri($detail_id) {
        $total = ReceivingDetailColly::where('receiving_detail_id', $detail_id)->where('is_reject', '1')->sum('quantity_ri');
        
        return $total ?? null;
    }

    public function total_reject_colly($detail_id) {
        $total = ReceivingDetailColly::where('receiving_detail_id', $detail_id)->where('is_reject', '1')->sum('quantity_colly');
        
        return $total ?? null;
    }
}