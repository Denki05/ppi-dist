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

    const SATUAN = [
        'Pcs' => 1,
        'Lembar' => 2,
        'Lusin' => 3,
        'Liter' => 4,
        'Kg' => 5
    ];

    public function satuan()
    {
        return array_search($this->satuan, self::SATUAN);
    }

    public function vendor()
    {
        return $this->belongsTo('App\Entities\Master\Vendor', 'vendor_id');
    }
}
