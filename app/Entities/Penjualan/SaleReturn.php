<?php

namespace App\Entities\Penjualan;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Account\Superuser as AccountSuperuser;

class SaleReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'retur_code', 'invoice_id', 'customer_id', 'warehouse_id',
        'description', 'status', 'return_date'];
    protected $table = 'penjualan_retur';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    public function invoice()
    {
        return $this->belongsTo('App\Entities\Finance\Invoicing', 'invoice_id');
    }

    public function sale_return_details()
    {
        return $this->hasMany('App\Entities\Penjualan\SaleReturnDetail');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse','warehouse_id');
    }

    public function createdBySuperuser()
    {
        $superuser = AccountSuperuser::find($this->created_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }

}
