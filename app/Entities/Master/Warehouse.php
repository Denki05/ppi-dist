<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = ['type', 'branch_office_id', 'warehouse_roles', 'code', 'name', 'contact_person', 'phone', 'address', 'description', 'status'];
    protected $table = 'master_warehouses';

    const TYPE = [
        'HEAD_OFFICE' => 1,
        'BRANCH_OFFICE' => 2,
        'GENERAL' => 3
    ];

    const ROLE = [
        'STOCK' => 'STOCK',
        'CENTRAL' => 'CENTRAL',
        'DOCUMENT' => 'DOCUMENT',
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function branch_office()
    {
        return $this->BelongsTo('App\Entities\Master\BranchOffice');
    }

    public function hasRole($role)
    {
        if (!$this->warehouse_roles) {
            return false;
        }

        $roles = array_map('trim', explode(',', $this->warehouse_roles));
        return in_array($role, $roles);
    }

    public function scopeForStockMutation($query)
    {
        return $query->where(function ($q) {
            $q->where('warehouse_roles', self::ROLE['STOCK'])
            ->orWhere('warehouse_roles', 'LIKE', self::ROLE['STOCK'].',%')
            ->orWhere('warehouse_roles', 'LIKE', '%,'.self::ROLE['STOCK'].',%')
            ->orWhere('warehouse_roles', 'LIKE', '%,'.self::ROLE['STOCK']);
        });
    }

}
