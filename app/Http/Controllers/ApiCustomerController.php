<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Product;
use App\Entities\Master\BrandLokal;

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
}
