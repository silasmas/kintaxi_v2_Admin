<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Widgets\UserStatsOverview;
use App\Filament\Support\UserRoleQueryHelper;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

/**
 * Liste des utilisateurs avec onglets par rôle.
 */
class ListUsers extends ListRecords
{
  protected static string $resource = UserResource::class;

  /**
   * Vue avec indicateur de chargement au changement d'onglet.
   *
   * @var view-string
   */
  protected static string $view = 'filament.resources.pages.list-users';

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }

  protected function getHeaderWidgets(): array
  {
    return [
      UserStatsOverview::class,
    ];
  }

  /**
   * Onglets : tous, chauffeurs, clients (passagers), administrateurs.
   *
   * @return array<string, Tab>
   */
  public function getTabs(): array
  {
    return [
      'all' => Tab::make('Tous')
        ->icon('heroicon-o-users')
        ->badge(UserRoleQueryHelper::countByRoleTab('all')),
      'driver' => Tab::make('Chauffeurs')
        ->icon('heroicon-o-truck')
        ->badge(UserRoleQueryHelper::countByRoleTab('driver'))
        ->modifyQueryUsing(fn (Builder $query): Builder => UserRoleQueryHelper::applyDrivers($query)),
      'passenger' => Tab::make('Clients')
        ->icon('heroicon-o-user')
        ->badge(UserRoleQueryHelper::countByRoleTab('passenger'))
        ->modifyQueryUsing(fn (Builder $query): Builder => UserRoleQueryHelper::applyPassengers($query)),
      'admin' => Tab::make('Administrateurs')
        ->icon('heroicon-o-shield-check')
        ->badge(UserRoleQueryHelper::countByRoleTab('admin'))
        ->modifyQueryUsing(fn (Builder $query): Builder => UserRoleQueryHelper::applyAdmins($query)),
    ];
  }
}
