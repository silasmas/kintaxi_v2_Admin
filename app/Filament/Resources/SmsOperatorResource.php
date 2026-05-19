<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsOperatorResource\Pages;
use App\Models\SmsOperator;
use App\Services\KeccelSmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Throwable;

/**
 * Gestion des opérateurs SMS Keccel (paramètres, solde, tests).
 */
class SmsOperatorResource extends Resource
{
  protected static ?string $model = SmsOperator::class;

  protected static ?string $navigationIcon = 'heroicon-o-signal';

  protected static ?string $navigationLabel = 'Opérateurs SMS';

  protected static ?string $navigationGroup = 'SMS';

  protected static ?string $modelLabel = 'Opérateur SMS';

  protected static ?string $pluralModelLabel = 'Opérateurs SMS';

  protected static ?int $navigationSort = 1;

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Section::make('Configuration opérateur')
        ->schema([
          Forms\Components\TextInput::make('name')
            ->label('Nom')
            ->default('Keccel')
            ->required()
            ->maxLength(120),
          Forms\Components\TextInput::make('provider')
            ->label('Fournisseur')
            ->default('keccel')
            ->required()
            ->maxLength(40),
          Forms\Components\TextInput::make('send_url')
            ->label('URL d’envoi')
            ->default(fn (): ?string => config('services.sms.url'))
            ->required()
            ->url(),
          Forms\Components\TextInput::make('balance_url')
            ->label('URL de consultation du solde')
            ->default(fn (): ?string => config('services.sms.balance_url'))
            ->url(),
          Forms\Components\TextInput::make('delivery_url')
            ->label('URL de vérification livraison')
            ->default(fn (): ?string => config('services.sms.delivery_url'))
            ->url(),
          Forms\Components\TextInput::make('token')
            ->label('Token API')
            ->default(fn (): ?string => config('services.sms.token'))
            ->required(),
          Forms\Components\TextInput::make('sender')
            ->label('Expéditeur')
            ->default(fn (): string => (string) config('services.sms.from', 'DGRAD'))
            ->required()
            ->maxLength(50),
          Forms\Components\Select::make('send_method')
            ->label('Méthode d’envoi')
            ->options([
              'POST' => 'POST',
              'GET' => 'GET',
            ])
            ->default('POST')
            ->required(),
          Forms\Components\Toggle::make('is_active')
            ->label('Opérateur actif')
            ->helperText('Un seul opérateur actif est utilisé pour les envois.')
            ->default(true),
        ])
        ->columns(2),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('name')->label('Nom')->searchable(),
        Tables\Columns\TextColumn::make('provider')->label('Fournisseur')->badge(),
        Tables\Columns\TextColumn::make('sender')->label('Expéditeur')->searchable(),
        Tables\Columns\TextColumn::make('send_method')->label('Méthode')->badge(),
        Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
        Tables\Columns\TextColumn::make('remaining_sms')
          ->label('SMS restants')
          ->placeholder('—')
          ->sortable(),
        Tables\Columns\TextColumn::make('last_balance_checked_at')
          ->label('Solde vérifié')
          ->dateTime('d/m/Y H:i')
          ->placeholder('—')
          ->sortable(),
        Tables\Columns\TextColumn::make('last_balance_response')
          ->label('Réponse solde')
          ->limit(45)
          ->placeholder('—')
          ->toggleable(),
      ])
      ->actions([
        Tables\Actions\Action::make('testConnection')
          ->label('Tester connexion')
          ->icon('heroicon-o-paper-airplane')
          ->form([
            Forms\Components\TextInput::make('phone')
              ->label('Téléphone destinataire')
              ->placeholder('2438XXXXXXXX')
              ->required(),
            Forms\Components\TextInput::make('message')
              ->label('Message test')
              ->default('Test connexion SMS KinTaxi')
              ->required(),
          ])
          ->action(function (SmsOperator $record, array $data): void {
            try {
              $service = app(KeccelSmsService::class);
              $service->send(
                (string) $data['phone'],
                (string) $data['message'],
                'operator_connection_test',
                $record
              );
              $log = $service->lastLog();
            } catch (Throwable $e) {
              report($e);
              Notification::make()
                ->title('Test connexion échoué')
                ->body($e->getMessage())
                ->danger()
                ->send();

              return;
            }

            Notification::make()
              ->title('Test connexion envoyé')
              ->body('HTTP : '.($log?->http_status ?: '—')."\nRéponse : ".($log?->provider_response ?: '—'))
              ->success()
              ->send();
          }),
        Tables\Actions\Action::make('refreshBalance')
          ->label('Actualiser solde')
          ->icon('heroicon-o-arrow-path')
          ->action(function (SmsOperator $record): void {
            $service = app(KeccelSmsService::class);
            try {
              $balance = $service->refreshBalance($record);
            } catch (Throwable $e) {
              report($e);
              $record->refresh();
              $description = $service->describeResponse($record->last_balance_response);
              Notification::make()
                ->title('Solde SMS non récupéré')
                ->body($e->getMessage()."\nType : ".$description['type'])
                ->danger()
                ->send();

              return;
            }

            $record->refresh();
            Notification::make()
              ->title('Solde SMS actualisé')
              ->body($balance === null ? 'Réponse reçue, solde non numérique.' : "SMS restants : {$balance}")
              ->success()
              ->send();
          }),
        Tables\Actions\EditAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListSmsOperators::route('/'),
      'create' => Pages\CreateSmsOperator::route('/create'),
      'edit' => Pages\EditSmsOperator::route('/{record}/edit'),
    ];
  }
}
