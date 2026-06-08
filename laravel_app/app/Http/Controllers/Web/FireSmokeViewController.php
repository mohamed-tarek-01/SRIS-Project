<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class FireSmokeViewController extends Controller
{
    public function index()
    {
        return view('fire_smoke');
    }
}
