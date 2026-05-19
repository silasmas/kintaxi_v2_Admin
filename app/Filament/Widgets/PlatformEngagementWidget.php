<?php

namespace App\Filament\Widgets;

use App\Filament\Support\VehicleStatusHelper;
use App\Models\Ride;
use App\Models\User;
use App\Models\Vehicle;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Statistiques filtrées chauffeurs / clients / véhicules pour le tableau de bord.
 */
class PlatformEngagementWidget extends BaseWidget
{
  use HasWidgetShield;

  protected static ?int $sort = 0;

  protected int|string|array $columnSpan = 'full';

  protected ?string $heading = 'Engagement utilisateurs & véhicules';

  protected ?string $description = 'Répartition des courses et validations véhicules.';

  protected static ?string $pollingInterval = null;

  /**
   * @return array<Stat>
   */
  protected function getStats(): array
  {
    $baseUrl = '/admin';

    $driversWithVehicles = User::query()
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
      ->whereHas('vehicles')
      ->count();

    $driversWithoutVehicles = User::query()
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
      ->whereDoesntHave('vehicles')
      ->count();

    $validatedVehicles = VehicleStatusHelper::applyValidated(Vehicle::query())->count();
    $failedVehicles = VehicleStatusHelper::applyFailed(Vehicle::query())->count();

    $clientsWithRides = User::query()
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%client%'))
      ->whereHas('ridesAsPassenger')
      ->count();

    $clientsWithoutRides = User::query()
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%client%'))
      ->whereDoesntHave('ridesAsPassenger')
      ->count();

    $driversWithRides = User::query()
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
      ->whereHas('ridesAsDriver')
      ->count();

    $driversWithoutRides = User::query()
      ->whereHas('role', fn ($q) => $q->where('role_name', 'like', '%chauff%'))
      ->whereDoesntHave('ridesAsDriver')
      ->count();

    return [
      Stat::make('Chauffeurs avec véhicules', (string) $driversWithVehicles)
        ->description("Sans véhicule : {$driversWithoutVehicles}")
        ->descriptionIcon('heroicon-m-truck')
        ->color('primary')
        ->url("{$baseUrl}/users?tableFilters[has_vehicles][value]=1", false),

      Stat::make('Véhicules validés / échoués', "{$validatedVehicles} / {$failedVehicles}")
        ->description('Selon le statut en base')
        ->descriptionIcon('heroicon-m-check-badge')
        ->color('success')
        ->url("{$baseUrl}/vehicles", false),

      Stat::make('Clients avec courses', (string) $clientsWithRides)
        ->description("Sans course : {$clientsWithoutRides}")
        ->descriptionIcon('heroicon-m-user-group')
        ->color('info')
        ->url("{$baseUrl}/users?tableFilters[has_rides_as_passenger][value]=1", false),

      Stat::make('Chauffeurs avec courses', (string) $driversWithRides)
        ->description("Sans course : {$driversWithoutRides}")
        ->descriptionIcon('heroicon-m-map-pin')
        ->color('warning')
        ->url("{$baseUrl}/users?tableFilters[has_rides_as_driver][value]=1", false),

      Stat::make('Total courses', (string) Ride::count())
        ->description('Toutes les courses')
        ->descriptionIcon('heroicon-m-flag')
        ->color('gray')
        ->url("{$baseUrl}/rides", false),
    ];
  }
}
