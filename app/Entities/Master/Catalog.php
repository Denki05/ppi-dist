<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Catalog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'brand',
        'note',
        'print_count',
        'created_by',
        'status',
    ];
    protected $table = 'master_catalog';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function detail()
    {
        return $this->hasMany('App\Entities\Master\CatalogDetail', 'catalog_id');
    }
}
