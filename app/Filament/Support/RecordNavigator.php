<?php

namespace App\Filament\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trouve l'enregistrement précédent et suivant pour la navigation entre fiches.
 */
class RecordNavigator
{
  /**
   * @param  Builder<Model>  $query
   * @return array{previous: ?Model, next: ?Model}
   */
  public static function adjacent(Model $record, Builder $query): array
  {
    $key = $record->getKey();
    $table = $record->getTable();

    $previous = (clone $query)
      ->where($table.'.id', '<', $key)
      ->orderByDesc($table.'.id')
      ->first();

    $next = (clone $query)
      ->where($table.'.id', '>', $key)
      ->orderBy($table.'.id')
      ->first();

    return [
      'previous' => $previous,
      'next' => $next,
    ];
  }
}
