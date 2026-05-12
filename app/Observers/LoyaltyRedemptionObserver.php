<?php

namespace App\Observers;

use App\Models\LoyaltyRedemptionHistory;

class LoyaltyRedemptionObserver
{
    /**
     * Met à jour le portefeuille utilisateur selon le solde après conversion (création uniquement).
     */
    public function created(LoyaltyRedemptionHistory $redemption): void
    {
        $redemption->user()->update([
            'wallet_balance' => $redemption->wallet_balance_after,
        ]);
    }
}
