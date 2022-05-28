<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchOffice extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'contact_person', 'phone', 'address', 'description', 'status'];
    protected $table = 'master_branch_offices';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function warehouses()
    {
        return $this->hasMany('App\Entities\Master\Warehouse');
    }
}
