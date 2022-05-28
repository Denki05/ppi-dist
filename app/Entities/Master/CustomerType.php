<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerType extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'status'];
    protected $table = 'master_customer_types';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function customers()
    {
        // return $this->hasMany('App\Entities\Master\Customer', 'type_id')->orderBy('name');
        return $this->belongsToMany('App\Entities\Master\Customer', 'master_customer_type_pivot', 'type_id', 'customer_id')->withPivot('id');
    }
}
