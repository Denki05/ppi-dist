<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'score', 'description', 'status'];
    protected $table = 'master_customer_categories';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function types()
    {
        return $this->belongsToMany('App\Entities\Master\CustomerType', 'master_customer_category_types', 'category_id', 'type_id')->withPivot('id');
    }

    public function customer()
    {
        return $this->hasMany('App\Entities\Master\Customer', 'category_id')->orderBy('name');
    }
}
