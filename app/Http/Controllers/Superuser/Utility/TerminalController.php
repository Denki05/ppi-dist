<?php

namespace App\Http\Controllers\Superuser\Utility;

use App\Http\Controllers\Controller;

class TerminalController extends Controller
{
    public function __invoke()
    {
        return view('superuser.utility.terminal');
    }
}
