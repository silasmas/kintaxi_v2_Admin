<?php

namespace App\Filament\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Filtres réutilisables sur le statut des véhicules (table status).
 */
class VehicleStatusHelper
{
  /**
   * Restreint la requête aux véhicules au statut validé / confirmé.
   *
   * @param  Builder<\App\Models\Vehicle>|Relation<\App\Models\Vehicle, mixed, mixed>  $query
   * @return Builder<\App\Models\Vehicle>|Relation<\App\Models\Vehicle, mixed, mixed>
   */
  public static function applyValidated(Builder|Relation $query): Builder|Relation
  {
    return $query->whereHas('status', function (Builder $statusQuery): void {
      $statusQuery->where(function (Builder $inner): void {
        $inner
          ->where('status_name', 'like', '%activé%')
          ->orWhere('status_name', 'like', '%confirmé%')
          ->orWhere('status_name', 'like', '%récu%');
      });
    });
  }

  /**
   * Restreint la requête aux véhicules au statut échoué / refusé / suspendu.
   *
   * @param  Builder<\App\Models\Vehicle>|Relation<\App\Models\Vehicle, mixed, mixed>  $query
   * @return Builder<\App\Models\Vehicle>|Relation<\App\Models\Vehicle, mixed, mixed>
   */
  public static function applyFailed(Builder|Relation $query): Builder|Relation
  {
    return $query->whereHas('status', function (Builder $statusQuery): void {
      $statusQuery->where(function (Builder $inner): void {
        $inner
          ->where('status_name', 'like', '%echoué%')
          ->orWhere('status_name', 'like', '%échoué%')
          ->orWhere('status_name', 'like', '%annulé%')
          ->orWhere('status_name', 'like', '%suspendu%');
      });
    });
  }

  /**
   * Restreint la requête aux véhicules en attente de validation.
   *
   * @param  Builder<\App\Models\Vehicle>|Relation<\App\Models\Vehicle, mixed, mixed>  $query
   * @return Builder<\App\Models\Vehicle>|Relation<\App\Models\Vehicle, mixed, mixed>
   */
  public static function applyPending(Builder|Relation $query): Builder|Relation
  {
    return $query->whereHas('status', function (Builder $statusQuery): void {
      $statusQuery->where('status_name', 'like', '%attente%');
    });
  }
}
