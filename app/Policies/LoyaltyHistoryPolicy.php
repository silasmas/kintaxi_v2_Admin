<?php

namespace App\Policies;

use App\Models\LoyaltyHistory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoyaltyHistoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_loyalty::history');
    }

    public function view(User $user, LoyaltyHistory $loyaltyHistory): bool
    {
        return $user->can('view_loyalty::history');
    }

    public function create(User $user): bool
    {
        return $user->can('create_loyalty::history');
    }

    public function update(User $user, LoyaltyHistory $loyaltyHistory): bool
    {
        return $user->can('update_loyalty::history');
    }

    public function delete(User $user, LoyaltyHistory $loyaltyHistory): bool
    {
        return $user->can('delete_loyalty::history');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_loyalty::history');
    }

    public function forceDelete(User $user, LoyaltyHistory $loyaltyHistory): bool
    {
        return $user->can('force_delete_loyalty::history');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_loyalty::history');
    }

    public function restore(User $user, LoyaltyHistory $loyaltyHistory): bool
    {
        return $user->can('restore_loyalty::history');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_loyalty::history');
    }

    public function replicate(User $user, LoyaltyHistory $loyaltyHistory): bool
    {
        return $user->can('replicate_loyalty::history');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_loyalty::history');
    }
}
