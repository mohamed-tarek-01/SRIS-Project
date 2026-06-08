<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'brand', 'model', 'year', 'plate_number',
        'fuel_type', 'engine_cc', 'fuel_efficiency', 'current_odometer', 'image_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }
}
