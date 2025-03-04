<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MitraDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mitra_id', 
        'customer_other_address_id', 
        'status',
        'created_by', 
        'updated_by', 
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $table = 'master_mitra_detail';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function mitra()
    {
        return $this->belongsTo('App\Entities\Master\Mitra', 'mitra_id');
    }

    public function customers()
    {
        return $this->belongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id', 'id');
    }
}