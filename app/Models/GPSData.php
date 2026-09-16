<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GPSData extends Model
{
    use HasFactory;
    
    protected $table = 'gps_data';
    protected $fillable = ['device_id', 'latitude', 'longitude', 'speed', 'battery', 'tracking_time'];
    
    protected $casts = [
        'tracking_time' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
    
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}