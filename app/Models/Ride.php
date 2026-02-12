<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ride extends Model
{
    protected $fillable = [
        'ride_status',
        'vehicle_category_id',
        'vehicle_id',
        'passenger_id',
        'driver_id',
        'distance',
        'cost',
        'estimated_cost',
        'payment_method',
        'paid',
        'payment_id',
        'commission',
        'start_location',
        'end_location',
        'pickup_location',
        'pickup_data',
        'driver_location',
        'estimated_duration',
        'actual_duration',
        'waiting_time',
        'is_scheduled',
        'scheduled_time',
        'cancellation_reason',
        'canceled_by',
    ];

    protected function casts(): array
    {
        return [
            'distance' => 'decimal:2',
            'cost' => 'decimal:2',
            'estimated_duration' => 'decimal:2',
            'actual_duration' => 'decimal:2',
            'waiting_time' => 'decimal:2',
            'paid' => 'boolean',
            'is_scheduled' => 'boolean',
            'scheduled_time' => 'datetime',
        ];
    }

    public function vehicleCategory(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'ride_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
