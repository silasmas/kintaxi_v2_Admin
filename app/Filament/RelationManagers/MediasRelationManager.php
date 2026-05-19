<?php

namespace App\Filament\RelationManagers;

use App\Models\Media;
use App\Models\User;
use App\Models\Vehicle;
use App\Filament\Resources\MediaResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Liste les médias (photos/vidéos) liés à un utilisateur ou un véhicule.
 */
class MediasRelationManager extends RelationManager
{
  protected static string $relationship = 'media';

  protected static ?string $title = 'Médias';

  protected static ?string $recordTitleAttribute = 'name';

  public function form(Form $form): Form
  {
    return MediaResource::form($form);
  }

  public function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ViewColumn::make('path')
          ->label('Aperçu')
          ->view('filament.tables.columns.media-thumb-hover')
          ->grow(false),
        Tables\Columns\TextColumn::make('name')->label('Nom')->searchable(),
        Tables\Columns\TextColumn::make('type')->label('Type')->badge(),
        Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
      ])
      ->defaultSort('created_at', 'desc')
      ->headerActions([
        Tables\Actions\CreateAction::make()
          ->mutateFormDataUsing(function (array $data): array {
            $owner = $this->getOwnerRecord();
            if ($owner instanceof User) {
              $data['user_id'] = $owner->id;
            }
            if ($owner instanceof Vehicle) {
              $data['vehicle_id'] = $owner->id;
              $data['user_id'] = $owner->user_id;
            }

            return $data;
          }),
      ])
      ->actions([
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ]);
  }
}
