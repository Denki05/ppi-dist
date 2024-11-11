<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'pic', 'officer', 'status'];
    protected $table = 'master_employee';

    const STATUS = [
        'INACTIVE' => 0,
        'ACTIVE' => 1
    ];
}
