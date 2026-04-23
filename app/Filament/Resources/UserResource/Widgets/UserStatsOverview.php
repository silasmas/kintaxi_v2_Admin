<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\Ride;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalUsers = User::query()->count();
        $drivers = $this->usersByRole('chauff');
        $clients = $this->usersByRole('client');

        $male = User::query()->where('gender', 'M')->count();
        $female = User::query()->where('gender', 'F')->count();
        $otherGender = User::query()->whereNotIn('gender', ['M', 'F'])->count();

        $orderedClientIds = Ride::query()
            ->whereNotNull('passenger_id')
            ->distinct('passenger_id')
            ->pluck('passenger_id')
            ->all();

        $clientsOrdered = User::query()
            ->whereIn('id', $orderedClientIds)
            ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%client%'))
            ->count();

        $driversKyc = User::query()
            ->where('kyc_verified', true)
            ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
            ->count();

        $clientsKyc = User::query()
            ->where('kyc_verified', true)
            ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%client%'))
            ->count();

        $driversKycApproved = User::query()
            ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
            ->whereHas('latestKycVerification', fn ($q) => $q->where('status', 'approved'))
            ->count();

        $driversKycInProgress = User::query()
            ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
            ->whereHas('latestKycVerification', fn ($q) => $q->whereIn('status', ['pending', 'under_review']))
            ->count();

        return [
            Stat::make('Total utilisateurs', (string) $totalUsers)
                ->description('Comptes enregistrés')
                ->color('primary'),
            Stat::make('Chauffeurs / Clients', "{$drivers} / {$clients}")
                ->description('Répartition par rôle')
                ->color('info'),
            Stat::make('Sexe (H/F/Autre)', "{$male} / {$female} / {$otherGender}")
                ->description('Répartition de genre')
                ->color('gray'),
            Stat::make('Clients ayant commandé', "{$clientsOrdered} / {$clients}")
                ->description('Ont déjà passé une course')
                ->color('success'),
            Stat::make('KYC chauffeurs / clients', "{$driversKyc} / {$clientsKyc}")
                ->description('Comptes vérifiés KYC')
                ->color('warning'),
            Stat::make('KYC chauffeurs validé / en cours', "{$driversKycApproved} / {$driversKycInProgress}")
                ->description('Selon le dernier statut KYC')
                ->color('danger'),
        ];
    }

    private function usersByRole(string $keyword): int
    {
        return User::query()
            ->whereHas('role', fn ($q) => $q->where('role_name', 'like', "%{$keyword}%"))
            ->count();
    }
}
