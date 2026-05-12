<?php

namespace App\Models;

use App\Enums\LoyaltyRedemptionCurrency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyRedemptionHistory extends Model
{
    protected $table = 'loyalty_redemption_history';

    protected $fillable = [
        'user_id',
        'points_redeemed',
        'conversion_rate_applied',
        'amount_usd',
        'amount_cdf',
        'daily_exchange_rate',
        'wallet_balance_before',
        'wallet_balance_after',
        'currency_used',
        'reference_loyalty_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'amount_cdf' => 'decimal:2',
            'daily_exchange_rate' => 'decimal:4',
            'wallet_balance_before' => 'decimal:2',
            'wallet_balance_after' => 'decimal:2',
            'currency_used' => LoyaltyRedemptionCurrency::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function loyaltyMovement(): BelongsTo
    {
        return $this->belongsTo(LoyaltyHistory::class, 'reference_loyalty_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
