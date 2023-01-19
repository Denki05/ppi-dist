<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'abbreviation', 'description', 'status'];
    protected $table = 'master_units';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function packaging()
    {
        return $this->hasMany('App\Entities\Master\Packaging', 'unit_id');
    }
}
