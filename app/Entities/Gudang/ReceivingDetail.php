<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceivingDetail extends Model
{
	use SoftDeletes;
    protected $table = "receiving_detail";
    protected $fillable = [
    	'receiving_id',
        'po_id', 
        'po_detail_id',
        'product_pacakging_id', 
        'qty', 
        'sj_po', 
        'note',
    ];

    public function receiving(){
    	return $this->belongsTo('App\Entities\Gudang\Receiving','receiving_id','id');
    }

    public function po(){
        return $this->belongsTo('App\Entities\Gudang\PurchaseOrder', 'po_id', 'id');
    }

    public function po_deetail(){
        return $this->belongsTo('App\Entities\Gudang\PurchaseOrderDetail', 'po_detail_id', 'id');
    }

    public function product_pack(){
    	return $this->belongsTo('App\Entities\Master\ProductPack','product_pacakging_id','id');
    }
}
