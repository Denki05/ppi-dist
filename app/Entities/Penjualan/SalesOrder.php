<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
	use SoftDeletes;
    protected $table = "penjualan_so";
    protected $fillable =[
    	'so_code',
    	'code',
    	'sales_id',
    	'sales_senior_id',
    	'origin_warehouse_id',
    	'destination_warehouse_id',
    	'customer_id',
        'customer_other_address_id',
        'ekspedisi_id',
        'vendor_id',
        'so_date',
    	'type_transaction',
        'rekening_id',
        'type_so',
    	'idr_rate',
    	'tl',
    	'sales',
    	'status',
        'shipping_cost_buyer',
        'condition',
    	'payment_status',
        'catatan',
    	'so_for',
        'indent_status', 
        'count_rev',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];
    const STATUS = [
    	1 => 'CASH',
    	2 => 'TEMPO',
    	3 => 'MARKETING'
    ];
    const BRAND_TYPE = [
    	1 => 'Senses',
    	2 => 'GCF',
    	3 => 'PPI',
    	4 => 'LONGDA'
    ];

    const SALES_SENIOR = [
        'Ivan' => 1,
        'Erwin' => 2,
        'Nia' => 'Nia',
        'Super Administrator' => 4,
    ];

    const SALES = [
        'Lindy' => 1,
        'Erwin' => 2,
        'Nia' => 3,
        'Super Administrator' => 4,
    ];
    
    const STEP = [
    	1 => 'AWAL',
    	2 => 'LANJUTAN',
        3 => 'AWAL PERLU REVISI',
        4 => 'TUTUP',
        5 => 'HOLD',
    	9 => 'MUTASI',
    ];

    const PAYMENT_STATUS = [
    	0 => 'NEW',
    	1 => 'PAID',
        2 => 'COPY',
    ];

    const TYPE_TRANSACTION = [
    	1 => 'CASH',
        2 => 'TEMPO',
        3 => 'MARKETPLACE',
    ];

    const CONDITION = [
    	0 => 'DELETED',
    	1 => 'ACTIVED',
        2 => 'HOLD',
    ];

    const COUNT_REV = [
    	0 => 'FALSE',
    	1 => 'TRUE',
    ];

    const SHIPPING_COST_BUYER = [
    	0 => 'NO',
    	1 => 'YES',
    ];

    const REKENING = [
        0 => '4720 2369 88 - IRWAN LINAKSITA',
        1 => '7881 0374 95 - IDA ELISA',
    ];

    const INDENT_STATUS = [
        'full' => 1,
        'partly' => 2,
        'completed' => 3,
    ];

    public function customer(){
    	return $this->BelongsTo('App\Entities\Master\Customer','customer_id','id');
    }
    public function member(){
    	return $this->BelongsTo('App\Entities\Master\CustomerOtherAddress','customer_other_address_id','id');
    }
    public function customer_gudang(){
    	return $this->BelongsTo('App\Entities\Master\Warehouse','destination_warehouse_id','id');
    }
    public function sales_senior(){
    	return $this->BelongsTo('App\Entities\Master\Sales','sales_senior_id','id');
    }
    public function sales(){
    	return $this->BelongsTo('App\Entities\Master\Sales','sales_id','id');
    }
    public function origin_warehouse(){
        return $this->BelongsTo('App\Entities\Master\Warehouse','origin_warehouse_id','id');
    }
    public function ekspedisi(){
        return $this->BelongsTo('App\Entities\Master\Ekspedisi','ekspedisi_id','id');
    }
    public function vendor(){
        return $this->BelongsTo('App\Entities\Master\Vendor','vendor_id','id');
    }
    public function so_detail(){
    	return $this->hasMany('App\Entities\Penjualan\SalesOrderItem','so_id');
    }
    public function do(){
        return $this->hasMany('App\Entities\Penjualan\PackingOrder', 'so_id', 'id');
    }

    public function proforma(){
    	return $this->hasMany('App\Entities\Penjualan\SoProforma','so_id');
    }
    public function so_brand_type()
    {
        if (isset($this->brand_type)) {
            return (object) self::BRAND_TYPE[$this->brand_type];
        } else {
            return null;
        }
    }
    public function so_status()
    {
        return (object) self::STEP[$this->status];
    }

    public function shipp_cost_buyer()
    {
        return (object) self::shipping_cost_buyer[$this->shipping_cost_buyer];
    }

    public function so_condition()
    {
        return (object) self::CONDITION[$this->condition];
    }

    public function so_payment()
    {
        return (object) self::PAYMENT_STATUS[$this->payment_status];
    }

    public function so_rekening()
    {
        return (object) self::REKENING[$this->rekening];
    }

    public function so_revisi()
    {
        return (object) self::COUNT_REV[$this->count_rev];
    }

    public function so_sales()
    {
        return array_search($this->sales_id, self::SALES);
    }

    public function so_sales_senior()
    {
        return array_search($this->sales_senior_id, self::SALES_SENIOR);
    }

    public function so_indent_status()
    {
        return array_search($this->indent_status, self::INDENT_STATUS);
    }

    public function user_update(){
        return $this->BelongsTo('App\Entities\Account\Superuser','updated_by','id');
    }

    public function store()
    {
      return $this->belongsTo(Customer::class);
    }
}
