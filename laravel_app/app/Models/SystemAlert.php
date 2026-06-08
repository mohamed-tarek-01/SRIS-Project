<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'location_text',
        'details',
        'is_read',
    ];

    protected $casts = [
        'details' => 'array',
        'is_read' => 'boolean',
    ];
}
