<?php

namespace App\Entities\Master;

use App\Entities\Model;

class VendorProduct extends Model
{
    
    protected $fillable = ['vendor_id', 'product_id'];
    protected $table = 'vendor_products';
}
