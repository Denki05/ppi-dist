<?php

namespace App\Entities\Master;

use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    protected $fillable = ['unit_id', 'pack', 'pack_value', 'status'];
    protected $table = 'master_packaging';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    const PACK = [
        1 => '100gr (0.1)',
        2 => '500gr (0.5)',
        3 => 'Jerigen 5kg (5)',
        4 => 'Alumunium 5kg (5)',
        5 => 'Jerigen 25kg (25)',
        6 => 'Drum 25kg (25)',
        7 => 'Free'
    ];

    const PACK_VALUE = [
        1 => 0.1,
        2 => 0.5,
        3 => 5,
        4 => 5,
        5 => 25,
        6 => 25,
        7 => 'Free'
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
