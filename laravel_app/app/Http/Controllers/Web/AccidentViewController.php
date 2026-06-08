<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AccidentViewController extends Controller
{
    public function index()
    {
        return view('accident');
    }
}

