<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\FileModelResource;
use App\Filament\Support\StatusColorHelper;
use App\Models\FileModel;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Fichiers des véhicules possédés par un chauffeur (relation hasManyThrough).
 */
class VehicleFilesRelationManager extends RelationManager
{
  protected static string $relationship = 'vehicleFiles';

  protected static ?string $title = 'Fichiers véhicules';

  protected static ?string $recordTitleAttribute = 'file_name';

  public function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('file_name')->label('Nom')->searchable()->limit(40),
        Tables\Columns\TextColumn::make('vehicle.registration_number')->label('Véhicule'),
        Tables\Columns\TextColumn::make('status.status_name')
          ->label('Statut')
          ->badge()
          ->formatStateUsing(fn (?string $state): string => \App\Models\Status::formatShort($state))
          ->color(fn (?string $state): string => StatusColorHelper::statusNameColor($state)),
        Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
      ])
      ->defaultSort('created_at', 'desc')
      ->actions([
        Tables\Actions\Action::make('voir')
          ->label('Voir')
          ->icon('heroicon-m-arrow-top-right-on-square')
          ->url(fn (FileModel $record): string => FileModelResource::getUrl('view', ['record' => $record])),
      ]);
  }
}
