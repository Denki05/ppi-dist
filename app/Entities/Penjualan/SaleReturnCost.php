<?php

namespace App\Entities\Penjualan;

use App\Entities\Model;
use App\Entities\Account\Superuser as AccountSuperuser;

class SaleReturnCost extends Model
{
    protected $fillable = [
        'retur_id',
        'discount_1_persen',
        'discount_2_persen',
        'discount_1',
        'discount_2',
        'discount_idr',
        'voucher_idr',
        'purchase_total_idr',
    ];
    protected $table = 'penjualan_retur_cost';

    public function retur()
    {
        return $this->belongsTo('App\Entities\Penjualan\SaleReturn', 'retur_id');
    }

    public function createdBySuperuser()
    {
        $superuser = AccountSuperuser::find($this->created_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }
}
