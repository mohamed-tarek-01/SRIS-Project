<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'origin', 'destination', 'distance_km',
        'fuel_consumed', 'fuel_cost', 'start_time'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}