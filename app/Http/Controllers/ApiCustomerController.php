<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Product;
use App\Entities\Master\BrandLokal;
use DB;

class ApiCustomerController extends Controller
{
    public function getApiDataCustomer()
    {
        $results = Customer::all(); // Fetch all customer records
        return response()->json($results); // Return data as JSON
    }

    public function getApiDataProduct()
    {

        $results = Product::whereIn('brand_name', ['Senses', 'GCF'])            
            ->get();


        return response()->json($results); // Return data as JSON
    }

    public function getApiDataBrand()
    {

        $results = BrandLokal::whereIn('brand_name', ['Senses', 'GCF'])            
            ->get();


        return response()->json($results); // Return data as JSON
    }

    public function getApiMember()
    {
        $results = DB::table('provinsi')
            ->leftJoin('kabupaten', 'provinsi.prov_id', '=', 'kabupaten.prov_id')
            ->leftJoin('master_customer_other_addresses', 'kabupaten.city_id', '=', 'master_customer_other_addresses.kota')
            ->select(
                'provinsi.prov_name AS provinsi',
                'kabupaten.city_name AS kota',
                'master_customer_other_addresses.name',
                'master_customer_other_addresses.officer',
            )
            ->get();

        return response()->json($results); // Return data as JSON
    }
}
