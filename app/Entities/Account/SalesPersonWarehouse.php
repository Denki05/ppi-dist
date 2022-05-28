<?php

namespace App\Entities\Account;

use App\Entities\Model;

class SalesPersonWarehouse extends Model
{
    protected $fillable = ['sales_person_id', 'warehouse_id'];
    protected $table = 'sales_person_warehouses';
}
