<?php

namespace App\Services;

use App\Filament\Support\UserTimezoneHelper;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Notifie par SMS un chauffeur lors de l'affectation à une course planifiée.
 */
class ScheduledRideDriverNotificationService
{
  public function __construct(
    protected KeccelSmsService $smsService
  ) {}

  /**
   * Envoie le SMS d'affectation si la course est planifiée et le chauffeur a un téléphone.
   *
   * @return bool true si l'envoi a réussi
   */
  public function notifyDriverAssignment(Ride $ride, User $driver): bool
  {
    if (! $ride->is_scheduled || blank($driver->phone)) {
      return false;
    }

    $scheduledLabel = UserTimezoneHelper::formatDateTime($ride->scheduled_time);
    $message = sprintf(
      'KinTaxi: course planifiee #%d vous est affectee pour le %s. Ouvrez l\'app chauffeur pour les details.',
      $ride->id,
      $scheduledLabel
    );

    try {
      $this->smsService->send(
        (string) $driver->phone,
        $message,
        'scheduled_ride_driver_assignment'
      );

      return true;
    } catch (Throwable $e) {
      Log::warning('SMS affectation course planifiée échoué', [
        'ride_id' => $ride->id,
        'driver_id' => $driver->id,
        'error' => $e->getMessage(),
      ]);

      return false;
    }
  }
}
