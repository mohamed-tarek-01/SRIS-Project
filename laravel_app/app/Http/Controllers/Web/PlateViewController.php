<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PlateViewController extends Controller
{
    public function index()
    {
        $stations = \App\Models\Station::orderBy('name')->get();
        return view('plate', compact('stations'));
    }
}

