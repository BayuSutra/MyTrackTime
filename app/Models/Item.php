<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_code',
        'device_id',
        'name',
        'description',
        'icon',
        'icon_color',
        'status',
        'payment_status',
        'price',
        'activated_at',
        'expired_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'activated_at' => 'datetime',
        'expired_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke ItemPhoto
    public function photos()
    {
        return $this->hasMany(ItemPhoto::class);
    }

    public function mainPhoto()
    {
        return $this->hasOne(ItemPhoto::class)->where('type', 'main');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Relasi ke GPS Data via device_id
    public function trackings()
    {
        return $this->hasMany(GPSTracking::class, 'device_id', 'device_id');
    }

    // Latest tracking via device_id
    public function latestTracking()
    {
        return $this->hasOne(GPSTracking::class, 'device_id', 'device_id')
            ->latest('tracking_time');
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->payment_status === 'paid';
    }

    public function canMonitor()
    {
        return $this->isActive() && $this->activated_at && $this->expired_at > now();
    }
}