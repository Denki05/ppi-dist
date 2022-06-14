<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code'];
    protected $table = 'master_stores';

    public function customer(){
        return $this->hasMany('App\Entities\Master\Customer','store_id');
    }
}
