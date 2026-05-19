<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsMessageLogResource\Pages;
use App\Models\SmsMessageLog;
use App\Services\KeccelSmsService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Throwable;

/**
 * Historique des SMS sortants (envoyés, livrés, échecs).
 */
class SmsMessageLogResource extends Resource
{
  protected static ?string $model = SmsMessageLog::class;

  protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

  protected static ?string $navigationLabel = 'Historique SMS';

  protected static ?string $navigationGroup = 'SMS';

  protected static ?string $modelLabel = 'Historique SMS';

  protected static ?string $pluralModelLabel = 'Historique SMS';

  protected static ?int $navigationSort = 3;

  public static function canCreate(): bool
  {
    return false;
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultSort('created_at', 'desc')
      ->columns([
        Tables\Columns\TextColumn::make('created_at')
          ->label('Date')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
        Tables\Columns\TextColumn::make('context')
          ->label('Contexte')
          ->badge()
          ->placeholder('—')
          ->searchable(),
        Tables\Columns\TextColumn::make('operator.name')
          ->label('Opérateur')
          ->placeholder('Config .env'),
        Tables\Columns\TextColumn::make('recipient')
          ->label('Destinataire')
          ->searchable(),
        Tables\Columns\TextColumn::make('status')
          ->label('Statut envoi')
          ->badge()
          ->color(fn (string $state): string => match ($state) {
            'delivered', 'sent' => 'success',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'gray',
          }),
        Tables\Columns\TextColumn::make('delivery_status')
          ->label('Livraison (lu)')
          ->badge()
          ->placeholder('—')
          ->color(fn (?string $state): string => match ($state) {
            'DELIVERED' => 'success',
            'FAILED', 'ERROR' => 'danger',
            'PENDING' => 'warning',
            default => 'gray',
          })
          ->description(fn (?string $state): ?string => $state === 'DELIVERED' ? 'Message livré / lu côté opérateur' : null),
        Tables\Columns\TextColumn::make('http_status')
          ->label('HTTP')
          ->badge()
          ->placeholder('—'),
        Tables\Columns\TextColumn::make('message')
          ->label('Message')
          ->limit(50)
          ->searchable(),
        Tables\Columns\TextColumn::make('provider_reference')
          ->label('Référence')
          ->placeholder('—')
          ->toggleable(),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->label('Statut envoi')
          ->options([
            'pending' => 'En attente',
            'sent' => 'Envoyé',
            'delivered' => 'Livré',
            'failed' => 'Échec',
          ]),
        Tables\Filters\SelectFilter::make('delivery_status')
          ->label('Livraison')
          ->options([
            'PENDING' => 'En attente',
            'DELIVERED' => 'Livré (lu)',
            'FAILED' => 'Échec',
            'ERROR' => 'Erreur',
          ]),
        Tables\Filters\SelectFilter::make('context')
          ->label('Contexte')
          ->options([
            'dashboard_sms_test' => 'Test dashboard',
            'operator_connection_test' => 'Test opérateur',
            'scheduled_ride_driver_assignment' => 'Affectation course planifiée',
          ]),
      ])
      ->actions([
        Tables\Actions\Action::make('checkDelivery')
          ->label('Vérifier livraison')
          ->icon('heroicon-o-arrow-path')
          ->visible(fn (SmsMessageLog $record): bool => filled($record->provider_reference))
          ->action(function (SmsMessageLog $record): void {
            try {
              $updated = app(KeccelSmsService::class)->refreshDelivery($record);
            } catch (Throwable $e) {
              report($e);
              Notification::make()
                ->title('Livraison non vérifiée')
                ->body($e->getMessage())
                ->danger()
                ->send();

              return;
            }

            Notification::make()
              ->title('Statut de livraison actualisé')
              ->body('Statut : '.($updated->delivery_status ?: 'inconnu'))
              ->success()
              ->send();
          }),
        Tables\Actions\Action::make('details')
          ->label('Détails')
          ->icon('heroicon-o-eye')
          ->modalHeading('Détails de l’envoi SMS')
          ->modalContent(fn (SmsMessageLog $record) => view('filament.resources.sms-message-logs.details', [
            'record' => $record,
          ])),
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListSmsMessageLogs::route('/'),
    ];
  }
}
