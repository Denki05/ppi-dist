<?php

namespace App\Entities\Account;

use App\Observers\ResponsibleUserObserver;
use Illuminate\Database\Eloquent\Model;

class SalesPerson extends Model
{
    protected $table = 'sales_persons';

    public static function boot() {
        parent::boot();

        static::observe(new ResponsibleUserObserver());
    }

    public function zones()
    {
        return $this->hasMany('App\Entities\Account\SalesPersonZone');
    }

    public function warehouses()
    {
        return $this->belongsToMany('App\Entities\Master\Warehouse', 'sales_person_warehouses', 'sales_person_id', 'warehouse_id')->withPivot('id');
    }

    public function branch_offices()
    {
        return $this->belongsToMany('App\Entities\Master\BranchOffice', 'sales_person_branch_offices', 'sales_person_id', 'branch_office_id')->withPivot('id');
    }
}
