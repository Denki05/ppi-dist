<?php

namespace App\Entities\Master;

use App\Entities\Model;

class CustomerContact extends Model
{
    protected $fillable = ['customer_id', 'customer_other_address_id', 'contact_id', 'status'];
    protected $table = 'master_customer_contacts';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function member()
    {
        return $this->belongTo('App\Entities\Master\CustomerOtherAddress');
    }
}
