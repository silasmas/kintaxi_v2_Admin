<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\DocumentResource;
use App\Filament\Support\StatusColorHelper;
use App\Models\Document;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Liste les documents liés à un utilisateur ou un véhicule.
 */
class DocumentsRelationManager extends RelationManager
{
  protected static string $relationship = 'documents';

  protected static ?string $title = 'Documents';

  protected static ?string $recordTitleAttribute = 'type';

  public function form(Form $form): Form
  {
    return DocumentResource::form($form);
  }

  public function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ViewColumn::make('file_thumb')
          ->label('')
          ->state(fn (Document $record): string => (string) $record->file_url)
          ->view('filament.tables.columns.url-image-hover')
          ->grow(false),
        Tables\Columns\TextColumn::make('type')
          ->label('Type')
          ->badge()
          ->formatStateUsing(fn (string $state): string => match ($state) {
            'id_card' => 'CNI',
            'driving_license' => 'Permis',
            'vehicle_registration' => 'Carte grise',
            'vehicle_insurance' => 'Assurance',
            default => $state,
          }),
        Tables\Columns\TextColumn::make('vehicle.registration_number')
          ->label('Véhicule')
          ->placeholder('—'),
        Tables\Columns\IconColumn::make('verified')->label('Vérifié')->boolean(),
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
        Tables\Actions\Action::make('voir')
          ->label('Voir')
          ->icon('heroicon-m-arrow-top-right-on-square')
          ->url(fn (Document $record): string => DocumentResource::getUrl('view', ['record' => $record])),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ]);
  }
}
