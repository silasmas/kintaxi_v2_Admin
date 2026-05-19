<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Concerns\NavigatesBetweenRecords;
use App\Filament\Resources\VehicleResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewVehicle extends ViewRecord
{
  use NavigatesBetweenRecords;

  protected static string $resource = VehicleResource::class;

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

    $this->record->load(['owner', 'status', 'category', 'shape']);
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
    /** @var Vehicle $record */
    return (string) ($record->registration_number ?? 'Véhicule #'.$record->id);
  }

  protected function formatNavigatorPreview(Model $record): ?string
  {
    /** @var Vehicle $record */
    $owner = $record->owner?->getFilamentName() ?? '—';
    $mark = trim(($record->mark ?? '').' '.($record->model ?? ''));

    return ($mark !== '' ? $mark.' · ' : '').'Propriétaire : '.$owner;
  }
}
