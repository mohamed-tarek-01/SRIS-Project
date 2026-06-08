<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class CarDashboardViewController extends Controller
{
    public function index()
    {
        return view('car_dashboard');
    }
}

