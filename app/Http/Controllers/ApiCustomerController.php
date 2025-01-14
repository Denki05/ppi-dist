<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Master\Customer;

class ApiCustomerController extends Controller
{
    public function getApiData()
    {
        $results = Customer::all(); // Fetch all customer records
        return response()->json($results); // Return data as JSON
    }
}