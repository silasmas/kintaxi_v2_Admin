<?php

namespace App\Filament\Support;

use App\Models\VehicleCategory;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filtres sur les catégories véhicule (exclusion économique).
 */
class VehicleCategoryQueryHelper
{
  /**
   * Catégories confort et supérieures (hors économique).
   *
   * @param  Builder<VehicleCategory>  $query
   * @return Builder<VehicleCategory>
   */
  public static function applyComfortAndAbove(Builder $query): Builder
  {
    return $query->where(function (Builder $inner): void {
      $inner
        ->whereRaw('LOWER(category_name) NOT LIKE ?', ['%econom%'])
        ->whereRaw('LOWER(category_name) NOT LIKE ?', ['%économ%'])
        ->whereRaw('LOWER(category_name) NOT LIKE ?', ['%eco %']);
    });
  }

  /**
   * @return array<int, int>
   */
  public static function comfortAndAboveCategoryIds(): array
  {
    return VehicleCategory::query()
      ->tap(fn (Builder $q) => self::applyComfortAndAbove($q))
      ->pluck('id')
      ->all();
  }
}
