<?php

namespace App\Filament\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filtres réutilisables sur le rôle applicatif (table users_roles).
 */
class UserRoleQueryHelper
{
  /**
   * Applique un filtre de rôle sur la requête utilisateurs.
   *
   * @param  'all'|'driver'|'passenger'|'admin'  $type
   */
  public static function applyRoleFilter(Builder $query, string $type): Builder
  {
    return match ($type) {
      'driver' => self::applyDrivers($query),
      'passenger' => self::applyPassengers($query),
      'admin' => self::applyAdmins($query),
      default => $query,
    };
  }

  /**
   * Filtre les chauffeurs.
   */
  public static function applyDrivers(Builder $query): Builder
  {
    return $query->whereHas('role', fn (Builder $roleQuery): Builder => self::whereRoleNameMatches(
      $roleQuery,
      ['chauff', 'driver']
    ));
  }

  /**
   * Filtre les clients / passagers.
   */
  public static function applyPassengers(Builder $query): Builder
  {
    return $query->whereHas('role', fn (Builder $roleQuery): Builder => self::whereRoleNameMatches(
      $roleQuery,
      ['client', 'passager', 'passenger']
    ));
  }

  /**
   * Filtre les administrateurs.
   */
  public static function applyAdmins(Builder $query): Builder
  {
    return $query->whereHas('role', fn (Builder $roleQuery): Builder => self::whereRoleNameMatches(
      $roleQuery,
      ['admin', 'super', 'administrateur']
    ));
  }

  /**
   * Compte les utilisateurs pour un onglet.
   *
   * @param  'all'|'driver'|'passenger'|'admin'  $type
   */
  public static function countByRoleTab(string $type): int
  {
    $query = User::query();

    return self::applyRoleFilter($query, $type)->count();
  }

  /**
   * @param  array<int, string>  $keywords
   */
  private static function whereRoleNameMatches(Builder $roleQuery, array $keywords): Builder
  {
    return $roleQuery->where(function (Builder $inner) use ($keywords): void {
      foreach ($keywords as $keyword) {
        $inner->orWhere('role_name', 'like', '%'.$keyword.'%');
      }
    });
  }
}
