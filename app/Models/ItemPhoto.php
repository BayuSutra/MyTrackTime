<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'photo_path',
        'photo_name',
        'type',
        'order',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}