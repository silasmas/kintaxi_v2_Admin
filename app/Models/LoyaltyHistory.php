<?php

namespace App\Models;

use App\Enums\LoyaltyTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyHistory extends Model
{
    protected $table = 'loyalty_history';

    protected $fillable = [
        'user_id',
        'points_earned',
        'points_before_transaction',
        'points_after_transaction',
        'transaction_type',
        'reference_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'transaction_type' => LoyaltyTransactionType::class,
            'reference_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function redemptionRecords(): HasMany
    {
        return $this->hasMany(LoyaltyRedemptionHistory::class, 'reference_loyalty_id');
    }
}
