<?php

namespace App\Repositories;

use App\Entities\Master\BranchOffice;
use App\Entities\Master\BrandReference;
use App\Entities\Master\Contact;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerCategory;
use App\Entities\Master\CustomerType;
use App\Entities\Master\Product;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\ProductType;
use App\Entities\Master\Question;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\Unit;
use App\Entities\Master\Vendor;
use App\Entities\Master\Warehouse;
use App\Entities\Master\CustomerOtherAddress;

class MasterRepo
{
    public static function brand_references()
    {   
        return BrandReference::where('status', BrandReference::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function sub_brand_references()
    {   
        return SubBrandReference::where('status', SubBrandReference::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function products()
    {   
        return Product::where('status', Product::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function product_categories()
    {   
        return ProductCategory::where('status', ProductCategory::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function product_types()
    {   
        return ProductType::where('status', ProductType::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function customers()
    {
        return Customer::where('status', Customer::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function customer_categories()
    {   
        return CustomerCategory::where('status', CustomerCategory::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function customer_types()
    {   
        return CustomerType::where('status', CustomerType::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function units()
    {   
        return Unit::where('status', Unit::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function warehouses()
    {   
        return Warehouse::where('status', Warehouse::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function branch_offices()
    {
        return BranchOffice::where('status', BranchOffice::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function contacts()
    {
        return Contact::where('status', Contact::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function vendors()
    {
        return Vendor::where('status', Vendor::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function questions()
    {   
        return Question::orderBy('name')->get();
    }

    public static function store()
    {
        return CustomerOtherAddress::where('status', CustomerOtherAddress::STATUS['ACTIVE'])->orderBy('name')->get();
    }

    public static function member()
    {
        return Customer::where('status', Customer::STATUS['ACTIVE'])->orderBy('name')->get();
    }
}