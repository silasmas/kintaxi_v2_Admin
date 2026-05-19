<?php

namespace App\Filament\Support;

use App\Models\Ride;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filtres et compteurs pour les onglets de la liste des courses.
 */
class RideQueryHelper
{
  /**
   * @param  'all'|'direct'|'scheduled'  $tab
   * @param  Builder<Ride>  $query
   * @return Builder<Ride>
   */
  public static function applyScheduledTab(Builder $query, string $tab): Builder
  {
    return match ($tab) {
      'direct' => $query->where(function (Builder $inner): void {
        $inner->where('is_scheduled', false)->orWhereNull('is_scheduled');
      }),
      'scheduled' => $query->where('is_scheduled', true),
      default => $query,
    };
  }

  /**
   * @param  'all'|'direct'|'scheduled'  $tab
   */
  public static function countByTab(string $tab): int
  {
    $query = Ride::query();

    return self::applyScheduledTab($query, $tab)->count();
  }
}
