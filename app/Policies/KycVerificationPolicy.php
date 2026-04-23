<?php

namespace App\Policies;

use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KycVerificationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_kyc_verification');
    }

    public function view(User $user, KycVerification $kycVerification): bool
    {
        return $user->can('view_kyc_verification');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, KycVerification $kycVerification): bool
    {
        return false;
    }

    public function delete(User $user, KycVerification $kycVerification): bool
    {
        return $user->can('delete_kyc_verification');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_kyc_verification');
    }

    public function forceDelete(User $user, KycVerification $kycVerification): bool
    {
        return $user->can('force_delete_kyc_verification');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_kyc_verification');
    }

    public function restore(User $user, KycVerification $kycVerification): bool
    {
        return $user->can('restore_kyc_verification');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_kyc_verification');
    }

    public function replicate(User $user, KycVerification $kycVerification): bool
    {
        return $user->can('replicate_kyc_verification');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_kyc_verification');
    }
}
