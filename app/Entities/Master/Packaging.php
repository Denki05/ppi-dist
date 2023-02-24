<?php

namespace App\Entities\Master;

use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    protected $fillable = ['unit_id', 'pack_no', 'pack_name', 'pack_value', 'description', 'status'];
    protected $table = 'master_packaging';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function category()
    {
        return $this->hasMany('App\Entities\Master\ProductCategory', 'packaging_id');
    }

    public function unit()
    {
        return $this->belongsTo('App\Entities\Master\Unit', 'unit_id');
    }
}
