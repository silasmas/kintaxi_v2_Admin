<?php

namespace App\Models;

use App\Filament\Support\CurrencyFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Currency extends Model
{
    protected $fillable = [
        'created_by',
        'updated_by',
        'currency_name',
        'currency_acronym',
        'rating',
        'icon',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted(): void
    {
        static::saved(fn () => CurrencyFormatter::clearCache());
        static::deleted(fn () => CurrencyFormatter::clearCache());
    }
}
