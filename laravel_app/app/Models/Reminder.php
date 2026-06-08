<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'title', 'type', 'due_date', 'due_odometer', 'is_completed'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}