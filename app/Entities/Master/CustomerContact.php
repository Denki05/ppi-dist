<?php

namespace App\Entities\Master;

use App\Entities\Model;

class CustomerContact extends Model
{
    protected $fillable = ['customer_id', 'contact_id'];
    protected $table = 'master_customer_contacts';
}
