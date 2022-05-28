<?php

namespace App\Entities\Master;

use App\Entities\Model;

class VendorContact extends Model
{
    protected $fillable = ['vendor_id', 'contact_id'];
    protected $table = 'master_vendor_contacts';
}
