<?php

namespace App\Filament\Support;

use App\Models\Ride;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;

/**
 * Chauffeurs éligibles pour l'affectation (confort+, disponibles, avec téléphone).
 */
class EligibleDriverQueryHelper
{
  /**
   * @param  Builder<User>  $query
   * @return Builder<User>
   */
  public static function applyEligibleDrivers(Builder $query, ?Ride $ride = null): Builder
  {
    $comfortCategoryIds = VehicleCategoryQueryHelper::comfortAndAboveCategoryIds();

    $query = UserRoleQueryHelper::applyDrivers($query)
      ->whereNotNull('phone')
      ->where('phone', '!=', '')
      ->where(function (Builder $vehicleScope) use ($comfortCategoryIds): void {
        $vehicleScope
          ->whereHas('vehicles', function (Builder $vehicleQuery) use ($comfortCategoryIds): void {
            $vehicleQuery->whereIn('category_id', $comfortCategoryIds);
          })
          ->orWhereHas('currentVehicle', function (Builder $vehicleQuery) use ($comfortCategoryIds): void {
            $vehicleQuery->whereIn('category_id', $comfortCategoryIds);
          });
      })
      ->where(function (Builder $availability) use ($ride): void {
        $availability->whereDoesntHave('ridesAsDriver', function (Builder $activeRideQuery): void {
          $activeRideQuery->whereIn('ride_status', ['accepted', 'in_progress']);
        });
        if ($ride?->driver_id) {
          $availability->orWhere('id', $ride->driver_id);
        }
      });

    return $query;
  }

  /**
   * Résout le véhicule confort+ à associer à la course pour un chauffeur.
   */
  public static function resolveVehicleForDriver(User $driver, ?int $preferredCategoryId = null): ?Vehicle
  {
    $comfortCategoryIds = VehicleCategoryQueryHelper::comfortAndAboveCategoryIds();

    if ($driver->current_vehicle_id) {
      $current = $driver->currentVehicle;
      if ($current && in_array((int) $current->category_id, $comfortCategoryIds, true)) {
        if ($preferredCategoryId === null || (int) $current->category_id === $preferredCategoryId) {
          return $current;
        }
      }
    }

    $vehicleQuery = Vehicle::query()
      ->where('user_id', $driver->id)
      ->whereIn('category_id', $comfortCategoryIds);

    if ($preferredCategoryId !== null) {
      $vehicleQuery->where('category_id', $preferredCategoryId);
    }

    return $vehicleQuery->latest('id')->first();
  }
}
