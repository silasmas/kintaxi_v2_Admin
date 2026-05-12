<?php

namespace App\Filament\Resources\KycVerificationResource\Pages;

use App\Filament\Resources\KycVerificationResource;
use App\Services\SmileId\JobStatusClient;
use App\Services\SmileId\JobStatusPayloadMerger;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewKycVerification extends ViewRecord
{
    protected static string $resource = KycVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh_smile_job')
                ->label('Rafraîchir depuis Smile ID')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn (): bool => filled(config('smileid.partner_id')) && filled(config('smileid.api_key')))
                ->requiresConfirmation()
                ->modalHeading('Récupérer les médias via job_status')
                ->modalDescription('Interroge l’API Smile ID (job_status) pour fusionner les liens signés dans cet enregistrement.')
                ->action(function (): void {
                    $record = $this->getRecord();
                    $client = app(JobStatusClient::class);
                    $res = $client->fetch($record, imageLinks: true, history: false);

                    if (! $res['ok'] || ! is_array($res['body'])) {
                        Notification::make()
                            ->title('Échec Smile ID job_status')
                            ->body($res['error'] ?? 'Réponse invalide.')
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    app(JobStatusPayloadMerger::class)->mergeInto($record, $res['body']);

                    $body = $res['body'];
                    $hasLinks = ! empty($body['image_links']) && is_array($body['image_links']);

                    $msg = [];
                    if (! empty($res['user_id_used'])) {
                        $msg[] = 'Identifiant Smile utilisé : '.$res['user_id_used'].'.';
                    }
                    $msg[] = $hasLinks
                        ? 'Des liens image ont été fusionnés dans l’enregistrement.'
                        : 'Smile a répondu OK mais sans image_links (job peut être incomplet ou sans prévisualisation côté Smile).';

                    Notification::make()
                        ->title('Données Smile ID fusionnées')
                        ->body(implode(' ', $msg))
                        ->success()
                        ->send();

                    $this->record->refresh();
                    $this->dispatch('$refresh');
                }),
        ];
    }
}
