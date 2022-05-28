<?php

namespace App\Http\Controllers\Superuser\Repo;

use App\Http\Controllers\Controller;
use App\Repositories\ProductRepo;
use Exception;
use Illuminate\Http\Request;
use App\Helper\RepoHelper;

class MasterController extends Controller
{
    public function product_type(Request $request) 
    {
        return ProductRepo::productType(RepoHelper::id($request), RepoHelper::condition(($request)));
    }
}
