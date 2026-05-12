<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\KycVerificationResource;
use App\Filament\Support\UpdateKycVerificationStatusTableAction;
use App\Models\KycVerification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class KycVerificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'kycVerifications';

    protected static ?string $title = 'Vérifications KYC (Smile ID)';

    protected static ?string $recordTitleAttribute = 'job_id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_id')->label('Job ID')->searchable()->limit(28),
                Tables\Columns\TextColumn::make('document_type')->label('Document')->toggleable(),
                Tables\Columns\TextColumn::make('country_code')->label('Pays')->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'under_review' => 'warning',
                        'pending' => 'gray',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('submitted_at')->label('Soumis le')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('verified_at')->label('Traité le')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('updated_at')->label('MAJ')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('voir_kyc')
                    ->label('Voir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (KycVerification $record): string => KycVerificationResource::getUrl('view', ['record' => $record])),
                UpdateKycVerificationStatusTableAction::make(),
            ]);
    }
}
