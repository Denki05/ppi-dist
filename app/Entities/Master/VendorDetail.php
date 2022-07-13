<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorDetail extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'id', 'vendor_id', 'transaction', 'quantity', 'satuan', 'grand_total'
    ];
    
    protected $table = 'master_vendors_detail';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    const UNIT = [
        1 => 'Pcs',
        2 => 'Lembar',
        3 => 'Lusin',
        4 => 'Liter',
        5 => 'Kg'
    ];

    public function unit()
    {
        return array_search($this->unit, self::UNIT);
    }

    public function vendor()
    {
        return $this->belongsTo('App\Entities\Master\Vendor');
    }
}
