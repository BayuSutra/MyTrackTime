<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GPSTracking extends Model
{
    use HasFactory;

    protected $table = 'gps_data';

    protected $fillable = [
        'device_id',
        'latitude',
        'longitude',
        'altitude',
        'speed',
        'accuracy',
        'battery',
        'tracking_time',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'altitude' => 'float',
        'speed' => 'float',
        'accuracy' => 'float',
        'tracking_time' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'device_id', 'device_id');
    }
}