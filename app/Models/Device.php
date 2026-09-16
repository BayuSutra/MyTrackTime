<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    
    protected $fillable = ['device_id', 'name', 'status'];
    
    public function gpsData()
    {
        return $this->hasMany(GPSData::class);
    }
    
    public function latestLocation()
    {
        return $this->hasOne(GPSData::class)->latest('tracking_time');
    }
}