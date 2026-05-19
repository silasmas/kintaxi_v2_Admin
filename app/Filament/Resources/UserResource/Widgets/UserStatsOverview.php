<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Filament\Support\VehicleStatusHelper;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Statistiques filtrées affichées en tête de la liste des utilisateurs.
 */
class UserStatsOverview extends StatsOverviewWidget
{
  protected int|string|array $columnSpan = 'full';

  protected static ?string $pollingInterval = '30s';

  /**
   * @return array<Stat>
   */
  protected function getStats(): array
  {
    $totalUsers = User::query()->count();
    $drivers = $this->usersByRole('chauff');
    $clients = $this->usersByRole('client');

    $male = User::query()->where('gender', 'M')->count();
    $female = User::query()->where('gender', 'F')->count();
    $otherGender = User::query()->whereNotIn('gender', ['M', 'F'])->count();

    $clientsWithRides = User::query()
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%client%'))
      ->whereHas('ridesAsPassenger')
      ->count();

    $clientsWithoutRides = max(0, $clients - $clientsWithRides);

    $driversWithRides = User::query()
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
      ->whereHas('ridesAsDriver')
      ->count();

    $driversWithoutRides = max(0, $drivers - $driversWithRides);

    $driversWithVehicles = User::query()
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
      ->whereHas('vehicles')
      ->count();

    $validatedVehicles = VehicleStatusHelper::applyValidated(Vehicle::query())->count();
    $failedVehicles = VehicleStatusHelper::applyFailed(Vehicle::query())->count();

    $driversKyc = User::query()
      ->where('kyc_verified', true)
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
      ->count();

    $clientsKyc = User::query()
      ->where('kyc_verified', true)
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%client%'))
      ->count();

    return [
      Stat::make('Total utilisateurs', (string) $totalUsers)
        ->description('Comptes enregistrés')
        ->color('primary'),
      Stat::make('Chauffeurs / Clients', "{$drivers} / {$clients}")
        ->description('Répartition par rôle')
        ->color('info'),
      Stat::make('Chauffeurs avec véhicules', (string) $driversWithVehicles)
        ->description("Véhicules validés / échoués : {$validatedVehicles} / {$failedVehicles}")
        ->color('gray'),
      Stat::make('Clients avec / sans course', "{$clientsWithRides} / {$clientsWithoutRides}")
        ->description('Ont déjà commandé ou non')
        ->color('success'),
      Stat::make('Chauffeurs avec / sans course', "{$driversWithRides} / {$driversWithoutRides}")
        ->description('Ont déjà conduit ou non')
        ->color('warning'),
      Stat::make('KYC chauffeurs / clients', "{$driversKyc} / {$clientsKyc}")
        ->description('Comptes vérifiés KYC')
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
