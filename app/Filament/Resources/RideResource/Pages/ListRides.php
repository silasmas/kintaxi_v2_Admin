<?php

namespace App\Filament\Resources\RideResource\Pages;

use App\Filament\Resources\RideResource;
use App\Filament\Support\RideQueryHelper;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

/**
 * Liste des courses avec onglets direct / planifié.
 */
class ListRides extends ListRecords
{
  protected static string $resource = RideResource::class;

  protected function getHeaderActions(): array
  {
    return [];
  }

  /**
   * @return array<string, Tab>
   */
  public function getTabs(): array
  {
    return [
      'all' => Tab::make('Toutes les courses')
        ->icon('heroicon-o-queue-list')
        ->badge(RideQueryHelper::countByTab('all')),
      'direct' => Tab::make('Courses directes')
        ->icon('heroicon-o-bolt')
        ->badge(RideQueryHelper::countByTab('direct'))
        ->modifyQueryUsing(fn (Builder $query): Builder => RideQueryHelper::applyScheduledTab($query, 'direct')),
      'scheduled' => Tab::make('Commandées')
        ->icon('heroicon-o-calendar-days')
        ->badge(RideQueryHelper::countByTab('scheduled'))
        ->modifyQueryUsing(fn (Builder $query): Builder => RideQueryHelper::applyScheduledTab($query, 'scheduled')),
    ];
  }
}
