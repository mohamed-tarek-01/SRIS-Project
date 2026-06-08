<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class CracksViewController extends Controller
{
    public function index()
    {
        return view('cracks');
    }
}

