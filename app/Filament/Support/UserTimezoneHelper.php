<?php

namespace App\Filament\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Fuseau horaire et formatage des dates pour l'utilisateur connecté à Filament.
 */
class UserTimezoneHelper
{
  /**
   * Retourne le fuseau horaire de l'utilisateur connecté ou celui de l'application.
   */
  public static function resolve(): string
  {
    $user = Auth::user();
    if ($user && filled($user->timezone ?? null)) {
      return (string) $user->timezone;
    }

    return (string) config('app.timezone', 'UTC');
  }

  /**
   * Liste des fuseaux courants pour le profil admin.
   *
   * @return array<string, string>
   */
  public static function options(): array
  {
    return [
      'Africa/Kinshasa' => 'Kinshasa (WAT)',
      'Africa/Lubumbashi' => 'Lubumbashi (CAT)',
      'Africa/Johannesburg' => 'Johannesburg (SAST)',
      'Europe/Paris' => 'Paris (CET/CEST)',
      'UTC' => 'UTC',
    ];
  }

  /**
   * Formate une date/heure dans le fuseau de l'utilisateur connecté.
   */
  public static function formatDateTime(mixed $value, string $format = 'd/m/Y H:i'): string
  {
    if ($value === null || $value === '') {
      return '—';
    }

    $carbon = $value instanceof CarbonInterface
      ? $value->copy()
      : Carbon::parse($value);

    return $carbon->timezone(self::resolve())->format($format);
  }
}
