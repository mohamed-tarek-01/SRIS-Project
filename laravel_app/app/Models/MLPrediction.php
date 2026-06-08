<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MLPrediction extends Model
{
    protected $table = 'ml_predictions';

    protected $fillable = [
        'user_id',
        'model_type',
        'image_path',
        'prediction_result',
        'confidence_score',
        'execution_time_ms',
        'input_type',
        'station_id',
        'location_text',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'prediction_result' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who made this prediction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the station associated with this prediction
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get readable model type name
     */
    public function getModelNameAttribute(): string
    {
        $names = [
            'plate' => 'License Plates',
            'cracks' => 'Road Cracks',
            'accident' => 'Accident Detection',
            'fire_smoke' => 'Fire & Smoke',
            'traffic' => 'Traffic Signs',
            'vehicles' => 'Vehicle Detection',
            'car_damage' => 'Car Damage',
            'dashboard' => 'Dashboard Lights',
        ];

        return $names[$this->model_type] ?? ucfirst($this->model_type);
    }

    /**
     * Get color for this model type
     */
    public function getColorAttribute(): string
    {
        $colors = [
            'plate' => 'blue',
            'cracks' => 'rose',
            'accident' => 'red',
            'fire_smoke' => 'orange',
            'traffic' => 'teal',
            'vehicles' => 'purple',
            'car_damage' => 'slate',
            'dashboard' => 'amber',
        ];

        return $colors[$this->model_type] ?? 'slate';
    }

    /**
     * Get icon for this model type
     */
    public function getIconAttribute(): string
    {
        $icons = [
            'plate' => 'scan-text',
            'cracks' => 'map',
            'accident' => 'car-front',
            'fire_smoke' => 'flame',
            'traffic' => 'signpost',
            'vehicles' => 'car',
            'car_damage' => 'wrench',
            'dashboard' => 'gauge',
        ];

        return $icons[$this->model_type] ?? 'activity';
    }
}
