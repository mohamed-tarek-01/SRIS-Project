<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class TrafficViewController extends Controller
{
    public function index()
    {
        return view('traffic');
    }
}
