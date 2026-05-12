<?php

namespace App\Enums;

enum LoyaltyRedemptionCurrency: string
{
    case Usd = 'USD';
    case Cdf = 'CDF';
    case Both = 'BOTH';

    public function label(): string
    {
        return match ($this) {
            self::Usd => 'USD (Dollar)',
            self::Cdf => 'CDF',
            self::Both => 'USD + CDF',
        };
    }
}
