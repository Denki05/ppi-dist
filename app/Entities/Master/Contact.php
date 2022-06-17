<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [ 'customer_id', 'name', 'phone', 'email', 'position', 'dob', 'npwp', 'ktp', 'address', 'status'];
    protected $table = 'master_contacts';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    function member(){
		return $this->belongsTo('App\Entities\Master\Customer','customer_id');
	}
}
