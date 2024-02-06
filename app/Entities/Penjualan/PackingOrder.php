<?php

namespace App\Entities\Penjualan;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingOrder extends Model
{
    use SoftDeletes;

	protected $appends = ['img_resi'];
    protected $table = "penjualan_do";
	public static $directory_image = 'images/delivery_order/expedition_receipt/';
    protected $fillable = [
    	'code',
        'do_code',
    	'warehouse_id',
    	'customer_id',
    	'customer_other_address_id', 
    	'other_address', 
        'ekspedisi_id',
        'vendor_id',
    	'idr_rate',
    	'status',
		'count_cancel',
    	'type_transaction',
        'note',
		'print_count',
        'date_sent',
    	'image',
		'so_id',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];
    const STATUS = [
    	1 => 'CASH',
    	2 => 'TEMPO',
    	3 => 'MARKETING'
    ];
    const STATUS_PENGIRIMAN = [
    	1 => [
    		'class' => 'secondary',
    		'msg' => 'Created'
    	],
    	2 => [
    		'class' => 'warning',
    		'msg' => 'Billed'
    	],
    	3 => [
    		'class' => 'info',
    		'msg' => 'Ready'
    	],
    	4 => [
    		'class' => 'primary',
    		'msg' => 'Packed'
    	],
    	5 => [
    		'class' => 'danger',
    		'msg' => 'Delivering'
    	],
    	6 => [
    		'class' => 'success',
    		'msg' => 'Delivered'
    	],
		7 => [
    		'class' => 'warning',
    		'msg' => 'Revisi'
    	],
    ];

	public function getImgResiAttribute()
    {
        if (!$this->image OR !file_exists(Self::$directory_image.$this->image)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image);
    }

    public function do_detail(){
    	return $this->hasMany('App\Entities\Penjualan\PackingOrderItem','do_id', 'id');
    }
    public function customer(){
    	return $this->BelongsTo('App\Entities\Master\Customer','customer_id','id');
    }
    public function so(){
    	return $this->BelongsTo('App\Entities\Penjualan\SalesOrder','so_id','id');
    }
    public function member(){
        return $this->BelongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id', 'id');
    }
    public function warehouse(){
    	return $this->BelongsTo('App\Entities\Master\Warehouse','warehouse_id','id');
    }
    // public function do_cost(){
    // 	return $this->hasMany('App\Entities\Penjualan\PackingOrderDetail', 'do_id');
    // }
	public function do_detail_cost(){
		return $this->hasMany('App\Entities\Penjualan\PackingOrderDetail', 'do_id', 'id');
	}

	// public function do_detail(){
	// 	return $this->hasMany('App\Entities\Penjualan\PackingOrderDetail', 'do_id', 'id');	
	// }

    public function do_other_cost(){
        return $this->hasMany('App\Entities\Penjualan\PackingOrderCost','do_id');
	}
    // public function do_type_transaction(){
    // 	return (object) self::STATUS[$this->type_transaction];
    // }
    public function do_status(){
    	return (object) self::STATUS_PENGIRIMAN[$this->status];
    }
    public function log_print(){
    	return $this->hasMany('App\Entities\Penjualan\PackingOrderLogPrint','do_id');
    }
    public function invoicing(){
        return $this->BelongsTo('App\Entities\Finance\Invoicing','id','do_id');
    }
	public function proforma(){
        return $this->BelongsTo('App\Entities\Penjualan\SoProforma','id','do_id');
    }
    public function getIdrRateAttribute($value)
    {
        return floatval($value);
    }
    public function ekspedisi(){
        return $this->BelongsTo('App\Entities\Master\Ekspedisi','ekspedisi_id','id');
    }
	public function vendor(){
        return $this->BelongsTo('App\Entities\Master\Vendor','vendor_id','id');
    }
}
