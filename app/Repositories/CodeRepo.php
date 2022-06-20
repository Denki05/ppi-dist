<?php

namespace App\Repositories;

use App\Entities\Master\BranchOffice;
use App\Entities\Master\BrandReference;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerCategory;
use App\Entities\Master\CustomerType;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\ProductType;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\Unit;
use App\Entities\Master\Vendor;
use App\Entities\Master\Warehouse;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\DeliveryOrderMutation;
use App\Entities\Penjualan\Canvasing;
use App\Entities\Finance\Invoicing;
use App\Entities\Finance\Payable;
use App\Entities\Gudang\StockAdjustment;
use App\Entities\Master\CustomerOtherAddress;

class CodeRepo
{
    private static function generate($pre = '', $class)
    {
        $count = $class::withTrashed()->count() + 1;
        $code = $pre . sprintf('%05d', $count);

        return $code;
    }

    public static function generateBranchOffice()
    {
        return self::generate('B', BranchOffice::class);
    }

    public static function generateBrandReference()
    {
        return self::generate('BR', BrandReference::class);
    }

    public static function generateSubBrandReference()
    {
        return self::generate('SBR', SubBrandReference::class);
    }

    public static function generateCustomer()
    {
        return self::generate('C', Customer::class);
    }

    public static function generateCustomerCategory()
    {
        return self::generate('CC', CustomerCategory::class);
    }

    public static function generateCustomerType()
    {
        return self::generate('CT', CustomerType::class);
    }

    public static function generateProductCategory()
    {
        return self::generate('PC', ProductCategory::class);
    }

    public static function generateProductType()
    {
        return self::generate('PT', ProductType::class);
    }

    public static function generateWarehouse()
    {
        return self::generate('WH', Warehouse::class);
    }

    public static function generateVendor()
    {
        return self::generate('V', Vendor::class);
    }
    public static function generateSO(){
        return self::generate('SO', SalesOrder::class);   
    }
    public static function generatePO(){
        return self::generate('PRE', PackingOrder::class);   
    }
    public static function generateDO(){
        $count = PackingOrder::withTrashed()
                              ->where('status','>',1)
                              ->whereYear('updated_at',date('Y'))
                              ->whereMonth('updated_at',date('m'))
                              ->get();
                                   
        if(count($count) > 0 ){
            $count = count($count) + 1;

            $code = 'DO-' .date('my')."-".sprintf('%05d', $count);
        }
        else{
            $code = 'DO-' .date('my')."-".sprintf('%05d', 1);
        }
        return $code;

    }
    public static function generateDOM(){
        return self::generate('MT', DeliveryOrderMutation::class);   
    }
    public static function generateCanvasing(){
        return self::generate('SM', Canvasing::class);   
    }
    public static function generateInvoicing($do_code){
        $split = explode("-", $do_code);

        if(count($split) == 1){

            $split = explode("DO", $do_code);
            $code = 'INV'.$split[1];    
        }
        else{
            $split = explode("-", $do_code);
            $code = 'INV-' .$split[1]."-".$split[2];
        }
        return $code;
    }
    public static function generateStockAdjustment(){
        return self::generate('STADJ', StockAdjustment::class);   
    }
    public static function generatePayable(){
        return self::generate('PY', Payable::class);   
    }

    public static function generateStore()
    {
        return self::generate('STR', CustomerOtherAddress::class);
    }
}