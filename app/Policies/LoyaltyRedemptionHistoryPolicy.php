<?php

namespace App\Policies;

use App\Models\LoyaltyRedemptionHistory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoyaltyRedemptionHistoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_loyalty::redemption::history');
    }

    public function view(User $user, LoyaltyRedemptionHistory $loyaltyRedemptionHistory): bool
    {
        return $user->can('view_loyalty::redemption::history');
    }

    public function create(User $user): bool
    {
        return $user->can('create_loyalty::redemption::history');
    }

    public function update(User $user, LoyaltyRedemptionHistory $loyaltyRedemptionHistory): bool
    {
        return $user->can('update_loyalty::redemption::history');
    }

    public function delete(User $user, LoyaltyRedemptionHistory $loyaltyRedemptionHistory): bool
    {
        return $user->can('delete_loyalty::redemption::history');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_loyalty::redemption::history');
    }

    public function forceDelete(User $user, LoyaltyRedemptionHistory $loyaltyRedemptionHistory): bool
    {
        return $user->can('force_delete_loyalty::redemption::history');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_loyalty::redemption::history');
    }

    public function restore(User $user, LoyaltyRedemptionHistory $loyaltyRedemptionHistory): bool
    {
        return $user->can('restore_loyalty::redemption::history');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_loyalty::redemption::history');
    }

    public function replicate(User $user, LoyaltyRedemptionHistory $loyaltyRedemptionHistory): bool
    {
        return $user->can('replicate_loyalty::redemption::history');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_loyalty::redemption::history');
    }
}
