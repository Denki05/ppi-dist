<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'code', 'name', 'address',
        'email', 'phone', 'owner_name', 'website',
        'description', 'status', 'type'
    ];
    
    protected $table = 'master_vendors';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];
    const TYPE = [
    	'Non Ekspedisi' => 0,
        'Ekspedisi' => 1
    ];

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function details()
    {
        return $this->hasMany('App\Entites\Master\VendorDetail', 'vendor_id');
    }
}
