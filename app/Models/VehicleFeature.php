<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleFeature extends Model
{
    protected $table = 'vehicles_features';

    protected $fillable = [
        'created_by',
        'updated_by',
        'vehicle_id',
        'is_clean',
        'has_helmet',
        'has_airbags',
        'has_seat_belt',
        'has_ergonomic_seat',
        'has_air_conditioning',
        'has_soundproofing',
        'has_sufficient_space',
        'has_quality_equipment',
        'has_on_board_technologies',
        'has_interior_lighting',
        'has_practical_accessories',
        'has_driving_assist_system',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
