<?php

namespace App\Policies;

use App\Models\AppPreference;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppPreferencePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_app::preference');
    }

    public function view(User $user, AppPreference $appPreference): bool
    {
        return $user->can('view_app::preference');
    }

    public function create(User $user): bool
    {
        return $user->can('create_app::preference');
    }

    public function update(User $user, AppPreference $appPreference): bool
    {
        return $user->can('update_app::preference');
    }

    public function delete(User $user, AppPreference $appPreference): bool
    {
        return $user->can('delete_app::preference');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_app::preference');
    }

    public function forceDelete(User $user, AppPreference $appPreference): bool
    {
        return $user->can('force_delete_app::preference');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_app::preference');
    }

    public function restore(User $user, AppPreference $appPreference): bool
    {
        return $user->can('restore_app::preference');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_app::preference');
    }

    public function replicate(User $user, AppPreference $appPreference): bool
    {
        return $user->can('replicate_app::preference');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_app::preference');
    }
}
