<?php

namespace App\Filament\Support;

use App\Models\KycVerification;
use App\Services\SmileId\StatusSyncService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

final class UpdateKycVerificationStatusTableAction
{
    public static function make(string $name = 'update_status'): Action
    {
        return Action::make($name)
            ->label('Mettre à jour statut')
            ->icon('heroicon-o-pencil-square')
            ->form([
                Forms\Components\Select::make('status')
                    ->label('Nouveau statut')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Refusé',
                        'under_review' => 'En revue',
                        'completed' => 'Terminé',
                    ])
                    ->required(),
            ])
            ->action(function (KycVerification $record, array $data): void {
                $newStatus = (string) ($data['status'] ?? 'pending');
                $record->update([
                    'status' => $newStatus,
                    'verified_at' => in_array($newStatus, ['approved', 'rejected', 'completed', 'under_review'], true) ? now() : null,
                ]);

                if ($record->user) {
                    if ($newStatus === 'approved') {
                        $record->user->update(['kyc_verified' => 1, 'kyc_verified_at' => now()]);
                    } elseif ($newStatus === 'rejected') {
                        $record->user->update(['kyc_verified' => 0, 'kyc_verified_at' => null]);
                    }
                }

                $remoteOk = app(StatusSyncService::class)->pushStatus($record, $newStatus);

                Notification::make()
                    ->title($remoteOk
                        ? 'Statut mis à jour en local et synchronisé à distance'
                        : 'Statut mis à jour en local (sync distante non disponible)')
                    ->success()
                    ->send();
            });
    }
}
