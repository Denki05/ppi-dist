<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Account\Superuser as AccountSuperuser;

class SalesOrderKontrak extends Model
{
    use SoftDeletes;
    
    public $incrementing = false;
    protected $table = "penjualan_so_kontrak";
    protected $fillable = [
        'code', 
        'brand_name', 
        'contract_range', 
        'customer_other_address_id', 
        'sales_senior', 
        'sales_junior',
        'condition', 
        'status',
        'catatan', 
        'note', 
        'created_by', 
        'updated_by', 
        'deleted_by', 
        'acc_by',
    ];

    const STATUS = [
        'DELETED' => 0,
    	'ACTIVE' => 1,
    	'ACC' => 2,
    	'COMPLETE' => 3,
    ];

    const CONDITION = [
        'INVALID' => 0,
        'VALID' => 1,
    ];

    const TYPE_TRANSACTION = [
        'CASH' => 1,
        'TEMPO' => 2,
        'COD' => 3,
        'MARKETPLACE' => 4,
    ];

    const SALES_SENIOR = [
        'Ivan' => 1,
        'Nia' => 2,
        'Lindy' => 3,
    ];

    const SALES = [
        'Lindy' => 1,
        'Rita' => 2,
        'Super Administrator' => 3,
        'Santi' => 4,
        'Rudy' => 5,
    ];

    const CONTRACT_RANGE = [
        '1 Bulan' => 1,  
        '2 Bulan' => 2,  
        '3 Bulan' => 3,  
        '4 Bulan' => 4,  
        '5 Bulan' => 5,  
        '6 Bulan' => 6,  
        '7 Bulan' => 7,  
        '8 Bulan' => 8,  
        '9 Bulan' => 9,  
        '10 Bulan' => 10,  
        '11 Bulan' => 11,
        '12 Bulan' => 12,
    ];

    public function item(){
        return $this->hasOne('App\Entities\Penjualan\SalesOrderKontrakItem', 'so_kontrak_id', 'id');
    }
    
    public function member(){
        return $this->BelongsTo('App\Entities\Master\CustomerOtherAddress','customer_other_address_id','id');
    }

    public function vendor(){
        return $this->BelongsTo('App\Entities\Master\Vendor','vendor_id','id');
    }

    public function warehouse(){
        return $this->BelongsTo('App\Entities\Master\Warehouse','warehouse_id','id');
    }

    public function sales_senior()
    {
        return array_search($this->sales_senior_id, self::SALES_SENIOR);
    }

    public function sales()
    {
        return array_search($this->sales_id, self::SALES);
    }

    public function user_create(){
        return $this->BelongsTo('App\Entities\Account\Superuser','created_by','id');
    }

    public function user_update(){
        return $this->BelongsTo('App\Entities\Account\Superuser','updated_by','id');
    }

    public function user_acc(){
        return $this->BelongsTo('App\Entities\Account\Superuser','acc_by','id');
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }
}