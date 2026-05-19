<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SmsMessageLogResource;
use App\Services\KeccelSmsService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

/**
 * Page de test d'envoi SMS via Keccel.
 */
class SmsTest extends Page
{
  protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

  protected static ?string $navigationLabel = 'Test SMS';

  protected static ?string $navigationGroup = 'SMS';

  protected static ?int $navigationSort = 2;

  protected static string $view = 'filament.pages.sms-test';

  protected function getHeaderActions(): array
  {
    return [
      Action::make('history')
        ->label('Historique SMS')
        ->icon('heroicon-o-inbox-stack')
        ->url(SmsMessageLogResource::getUrl('index')),
      Action::make('sendTest')
        ->label('Envoyer un SMS test')
        ->icon('heroicon-o-paper-airplane')
        ->modalHeading('Tester l’envoi SMS Keccel')
        ->form([
          TextInput::make('phone')
            ->label('Téléphone destinataire')
            ->placeholder('2438XXXXXXXX')
            ->required(),
          Textarea::make('message')
            ->label('Message')
            ->default('Test SMS KinTaxi Admin')
            ->required()
            ->rows(3),
        ])
        ->action(function (array $data): void {
          try {
            app(KeccelSmsService::class)->send(
              (string) $data['phone'],
              (string) $data['message'],
              'dashboard_sms_test'
            );
          } catch (Throwable $e) {
            report($e);
            Notification::make()
              ->title('Échec d’envoi SMS')
              ->body($e->getMessage())
              ->danger()
              ->send();

            return;
          }

          Notification::make()
            ->title('SMS envoyé')
            ->body('Le message a été transmis à Keccel. Consultez l’historique pour le statut de livraison.')
            ->success()
            ->send();
        }),
    ];
  }
}
