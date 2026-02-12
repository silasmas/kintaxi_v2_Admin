<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleShape extends Model
{
    protected $table = 'vehicles_shapes';

    protected $fillable = [
        'created_by',
        'updated_by',
        'shape_name',
        'shape_description',
        'photo',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'shape_id');
    }
}
