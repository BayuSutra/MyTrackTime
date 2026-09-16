<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'transaction_code',
        'type',
        'amount',
        'payment_method',
        'payment_channel',
        'payment_account',
        'payment_account_name',
        'payment_code',
        'payment_date',
        'status',
        'payment_data',
        'payment_receipt',
        'payment_verified_by',
        'payment_verified_at',
        'notes',
        'expired_at',
        'payment_expired_at',
    ];

    protected $casts = [
        'payment_data' => 'array',
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'expired_at' => 'datetime',
        'payment_expired_at' => 'datetime',
        'payment_verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending' || $this->status === 'processing';
    }

    public function isExpired()
    {
        return $this->expired_at && $this->expired_at < now();
    }

    public function isPaymentExpired()
    {
        return $this->payment_expired_at && $this->payment_expired_at < now();
    }
}