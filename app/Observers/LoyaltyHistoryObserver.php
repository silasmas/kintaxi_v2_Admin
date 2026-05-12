<?php

namespace App\Observers;

use App\Models\LoyaltyHistory;

class LoyaltyHistoryObserver
{
    /**
     * Aligne le solde fidélité utilisateur sur le snapshot après transaction (création uniquement).
     */
    public function created(LoyaltyHistory $history): void
    {
        $history->user()->update([
            'loyalty_point' => $history->points_after_transaction,
        ]);
    }
}
