<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'location',
        'price',
        'latitude',
        'longitude',
    ];

    public function transactions()
    {
        return $this->hasMany(TollTransaction::class);
    }
}
