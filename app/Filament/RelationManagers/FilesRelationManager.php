<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\FileModelResource;
use App\Filament\Support\StatusColorHelper;
use App\Models\FileModel;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Liste les fichiers liés à un véhicule ou à un chauffeur (via ses véhicules).
 */
class FilesRelationManager extends RelationManager
{
  protected static string $relationship = 'files';

  protected static ?string $title = 'Fichiers';

  protected static ?string $recordTitleAttribute = 'file_name';

  public function form(Form $form): Form
  {
    return FileModelResource::form($form);
  }

  public function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('file_name')->label('Nom')->searchable()->limit(40),
        Tables\Columns\TextColumn::make('vehicle.registration_number')
          ->label('Véhicule')
          ->placeholder('—'),
        Tables\Columns\TextColumn::make('status.status_name')
          ->label('Statut')
          ->badge()
          ->formatStateUsing(fn (?string $state): string => \App\Models\Status::formatShort($state))
          ->color(fn (?string $state): string => StatusColorHelper::statusNameColor($state)),
        Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
      ])
      ->defaultSort('created_at', 'desc')
      ->headerActions([
        Tables\Actions\CreateAction::make()
          ->mutateFormDataUsing(function (array $data): array {
            $owner = $this->getOwnerRecord();
            if ($owner instanceof \App\Models\Vehicle) {
              $data['vehicle_id'] = $owner->id;
            }

            return $data;
          }),
      ])
      ->actions([
        Tables\Actions\Action::make('voir')
          ->label('Voir')
          ->icon('heroicon-m-arrow-top-right-on-square')
          ->url(fn (FileModel $record): string => FileModelResource::getUrl('view', ['record' => $record])),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ]);
  }
}
