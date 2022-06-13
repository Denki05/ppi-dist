<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'customer_id', 'status', 'created_at', 'updated_at', 'deleted_at'];
    protected $table = 'master_stores';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function customer()
    {
        return $this->BelongsTo('App\Entities\Master\Customer');
    }
}
