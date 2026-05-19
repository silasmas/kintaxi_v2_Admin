<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Concerns\NavigatesBetweenRecords;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewUser extends ViewRecord
{
  use NavigatesBetweenRecords;

  protected static string $resource = UserResource::class;

  protected static string $view = 'filament.resources.pages.view-user';

  public function hasCombinedRelationManagerTabsWithContent(): bool
  {
    return true;
  }

  public function getContentTabLabel(): ?string
  {
    return 'Profil';
  }

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

    $this->record->load([
      'vehicles.shape',
      'currentVehicle',
      'transactions',
      'documents',
      'status',
      'role',
      'roles',
      'latestKycVerification',
    ]);
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
    /** @var User $record */
    return $record->getFilamentName();
  }

  protected function formatNavigatorPreview(Model $record): ?string
  {
    /** @var User $record */
    $role = $record->role?->role_name ?? '—';
    $contact = $record->email ?? $record->phone ?? '—';

    return "{$role} · {$contact}";
  }

  /**
   * @return array<int, array{user: User, role: string}>
   */
  protected function getNavigatorAvatars(Model $record): array
  {
    /** @var User $record */
    return [
      ['user' => $record, 'role' => 'Utilisateur'],
    ];
  }
}
