<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;

class ApiInvoiceController extends Controller
{
    public function getApiData()
    {
        $results = PackingOrder::with(['do_detail', 'do_detail_cost'])->whereYear('created_at', 2024)->get();
        return response()->json($results);
    }
}