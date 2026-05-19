<?php

namespace App\Filament\Resources\Concerns;

/**
 * Masque une ressource Filament du menu latéral tout en gardant les routes actives.
 */
trait HidesFromNavigation
{
  public static function shouldRegisterNavigation(): bool
  {
    return false;
  }
}
