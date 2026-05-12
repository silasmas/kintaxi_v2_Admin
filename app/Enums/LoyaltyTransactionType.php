<?php

namespace App\Enums;

enum LoyaltyTransactionType: string
{
    case Ride = 'ride';
    case Referral = 'referral';
    case Bonus = 'bonus';
    case Redemption = 'redemption';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Ride => 'Course',
            self::Referral => 'Parrainage',
            self::Bonus => 'Bonus',
            self::Redemption => 'Conversion',
            self::Other => 'Autre',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Ride => 'info',
            self::Referral => 'success',
            self::Bonus => 'warning',
            self::Redemption => 'danger',
            self::Other => 'gray',
        };
    }
}
