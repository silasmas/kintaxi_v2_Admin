<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\RideResource;
use App\Filament\Support\CurrencyFormatter;
use App\Filament\Support\StatusColorHelper;
use App\Models\Ride;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Courses effectuées en tant que chauffeur.
 */
class RidesAsDriverRelationManager extends RelationManager
{
  protected static string $relationship = 'ridesAsDriver';

  protected static ?string $title = 'Courses (chauffeur)';

  public function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
        Tables\Columns\TextColumn::make('ride_status')
          ->label('Statut')
          ->badge()
          ->formatStateUsing(fn (string $state): string => StatusColorHelper::rideStatusLabel($state))
          ->color(fn (string $state): string => StatusColorHelper::rideStatusColor($state)),
        Tables\Columns\ViewColumn::make('passenger')
          ->label('Client')
          ->view('filament.tables.columns.owner-with-avatar'),
        CurrencyFormatter::configureMoneyColumn(
          Tables\Columns\TextColumn::make('cost')->label('Coût')
        ),
        Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i'),
      ])
      ->defaultSort('created_at', 'desc')
      ->actions([
        Tables\Actions\Action::make('voir')
          ->label('Voir')
          ->icon('heroicon-m-arrow-top-right-on-square')
          ->url(fn (Ride $record): string => RideResource::getUrl('view', ['record' => $record])),
      ]);
  }
}
