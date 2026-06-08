<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class VehiclesViewController extends Controller
{
    public function index()
    {
        return view('vehicles');
    }
}
