<?php

namespace App\Entities\Master;

use App\Entities\Model;

class CustomerTypePivot extends Model
{
    protected $fillable = ['customer_id', 'type_id'];
    protected $table = 'master_customer_type_pivot';
}
