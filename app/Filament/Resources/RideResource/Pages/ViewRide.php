<?php

namespace App\Filament\Resources\RideResource\Pages;

use App\Filament\Concerns\NavigatesBetweenRecords;
use App\Filament\Resources\RideResource;
use App\Models\Ride;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewRide extends ViewRecord
{
  use NavigatesBetweenRecords;

  protected static string $resource = RideResource::class;

  protected static string $view = 'filament.resources.pages.view-record-with-navigation';

  protected function getHeaderActions(): array
  {
    return [
      Actions\EditAction::make(),
      Actions\DeleteAction::make(),
    ];
  }

  public function mount(int|string $record): void
  {
    parent::mount($record);

    $this->record->load(['passenger', 'driver', 'vehicle', 'reviews']);
  }

  /**
   * @return array<string, mixed>
   */
  protected function getViewData(): array
  {
    return array_merge(parent::getViewData(), [
      'navigator' => $this->buildNavigatorViewData(),
    ]);
  }

  protected function formatNavigatorLabel(Model $record): string
  {
    /** @var Ride $record */
    return 'Course #'.$record->id;
  }

  protected function formatNavigatorPreview(Model $record): ?string
  {
    /** @var Ride $record */
    $status = match ($record->ride_status) {
      'requested' => 'Demandée',
      'accepted' => 'Acceptée',
      'in_progress' => 'En cours',
      'completed' => 'Terminée',
      'canceled' => 'Annulée',
      default => $record->ride_status,
    };
    $client = $record->passenger?->getFilamentName() ?? '—';
    $driver = $record->driver?->getFilamentName() ?? '—';

    return "{$status} · {$client} → {$driver}";
  }

  protected function prepareNavigatorRecord(Model $record): Model
  {
    /** @var Ride $record */
    $record->loadMissing(['passenger', 'driver']);

    return $record;
  }

  /**
   * @return array<int, array{user: User, role: string}>
   */
  protected function getNavigatorAvatars(Model $record): array
  {
    /** @var Ride $record */
    $avatars = [];
    if ($record->passenger) {
      $avatars[] = ['user' => $record->passenger, 'role' => 'Client'];
    }
    if ($record->driver) {
      $avatars[] = ['user' => $record->driver, 'role' => 'Chauffeur'];
    }

    return $avatars;
  }
}
