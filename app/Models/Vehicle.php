<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    protected $fillable = [
        'status_id',
        'created_by',
        'updated_by',
        'user_id',
        'model',
        'mark',
        'color',
        'registration_number',
        'vin_number',
        'manufacture_year',
        'fuel_type',
        'cylinder_capacity',
        'engine_power',
        'shape_id',
        'category_id',
        'nb_places',
    ];

    protected function casts(): array
    {
        return [
            'cylinder_capacity' => 'decimal:2',
            'engine_power' => 'decimal:2',
        ];
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shape(): BelongsTo
    {
        return $this->belongsTo(VehicleShape::class, 'shape_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(FileModel::class);
    }

    public function features(): HasOne
    {
        return $this->hasOne(VehicleFeature::class);
    }

    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }
}
