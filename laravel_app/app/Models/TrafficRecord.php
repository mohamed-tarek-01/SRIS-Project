<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrafficRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'national_id',
        'plate_number',
        'vehicle_type',
    ];
}
