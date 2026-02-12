<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = [
        'created_by',
        'updated_by',
        'status_id',
        'reference',
        'provider_reference',
        'phone',
        'amount_customer',
        'amount',
        'currency',
        'channel',
        'gateway',
        'ride_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_customer' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class, 'ride_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'payment_id');
    }
}
