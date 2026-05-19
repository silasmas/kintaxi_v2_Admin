<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\VehicleResource;
use App\Filament\Support\StatusColorHelper;
use App\Models\Vehicle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Véhicules soumis par un chauffeur.
 */
class VehiclesRelationManager extends RelationManager
{
  protected static string $relationship = 'vehicles';

  protected static ?string $title = 'Véhicules soumis';

  protected static ?string $recordTitleAttribute = 'registration_number';

  public function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('registration_number')->label('Plaque')->searchable(),
        Tables\Columns\TextColumn::make('mark')->label('Marque'),
        Tables\Columns\TextColumn::make('model')->label('Modèle'),
        Tables\Columns\TextColumn::make('status.status_name')
          ->label('Statut')
          ->badge()
          ->formatStateUsing(fn (?string $state): string => \App\Models\Status::formatShort($state))
          ->color(fn (?string $state): string => StatusColorHelper::statusNameColor($state)),
        Tables\Columns\TextColumn::make('created_at')->label('Soumis le')->dateTime('d/m/Y H:i'),
      ])
      ->defaultSort('created_at', 'desc')
      ->actions([
        Tables\Actions\Action::make('voir')
          ->label('Voir')
          ->icon('heroicon-m-arrow-top-right-on-square')
          ->url(fn (Vehicle $record): string => VehicleResource::getUrl('view', ['record' => $record])),
      ]);
  }
}
