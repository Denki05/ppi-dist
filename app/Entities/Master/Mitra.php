<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mitra extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 
        'code', 
        'alamat',
        'status', 
        'created_by', 
        'updated_by', 
        'created_at',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];

    protected $table = 'master_mitra';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function product_finance()
    {
        return $this->hasMany('App\Entities\Master\ProductFinance', 'mitra_id');
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }
}