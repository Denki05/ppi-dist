<?php

namespace App\Http\Controllers\Superuser\Account;

use Illuminate\Http\Request;
use App\Entities\Account\LogActivitys;
use App\Http\Controllers\Controller;

class LogActivityController extends Controller
{
    public function index()
    {
        $data['logs'] = LogActivitys::orderBy('created_at', 'DESC')->get();
        return view('superuser.account.log_activity.index', $data);
    }
}