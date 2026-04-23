<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycVerificationResource\Pages;
use App\Models\KycVerification;
use App\Services\SmileId\StatusSyncService;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KycVerificationResource extends Resource
{
    protected static ?string $model = KycVerification::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Vérifications KYC';

    protected static ?string $modelLabel = 'Vérification KYC';

    protected static ?string $pluralModelLabel = 'Vérifications KYC';

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 12;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Résumé')
                    ->schema([
                        TextEntry::make('job_id')->label('Job ID (partner)'),
                        TextEntry::make('user.id')->label('Utilisateur ID'),
                        TextEntry::make('user.email')->label('Email utilisateur'),
                        TextEntry::make('user.phone')->label('Téléphone utilisateur'),
                        TextEntry::make('product_type')->label('Produit'),
                        TextEntry::make('document_type')->label('Type de document'),
                        TextEntry::make('country_code')->label('Pays (document)'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'under_review' => 'warning',
                                'pending' => 'gray',
                                default => 'info',
                            }),
                        TextEntry::make('submitted_at')->label('Soumis le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('verified_at')->label('Traité le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')->label('Mis à jour le')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
                Section::make('Pièce et photos soumises')
                    ->schema([
                        ViewEntry::make('media_preview')
                            ->label('')
                            ->view('filament.infolists.entries.kyc-media-preview')
                            ->columnSpanFull(),
                    ]),
                Section::make('Résultat Smile ID (extrait JSON)')
                    ->schema([
                        TextEntry::make('smile_result_json')
                            ->label('')
                            ->columnSpanFull()
                            ->formatStateUsing(function (?string $state): string {
                                if ($state === null || $state === '') {
                                    return '—';
                                }
                                $decoded = json_decode($state, true);
                                if (! is_array($decoded)) {
                                    return $state;
                                }

                                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: $state;
                            }),
                    ])
                    ->collapsed(),
                Section::make('Payload callback (brut)')
                    ->schema([
                        TextEntry::make('callback_payload_json')
                            ->label('')
                            ->columnSpanFull()
                            ->formatStateUsing(function (?string $state): string {
                                if ($state === null || $state === '') {
                                    return '—';
                                }
                                $decoded = json_decode($state, true);
                                if (! is_array($decoded)) {
                                    return $state;
                                }

                                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: $state;
                            }),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('user')
                    ->label('')
                    ->view('filament.tables.columns.owner-with-avatar'),
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Utilisateur')
                    ->state(fn (KycVerification $record): string => $record->user?->getFilamentName() ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->where('firstname', 'like', "%{$search}%")
                                ->orWhere('lastname', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('job_id')->label('Job ID')->searchable()->limit(24),
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
                Tables\Columns\TextColumn::make('verified_at')->label('Traité le')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('MAJ')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Refusé',
                        'under_review' => 'En revue',
                        'completed' => 'Terminé',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('update_status')
                    ->label('Mettre à jour statut')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
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
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKycVerifications::route('/'),
            'view' => Pages\ViewKycVerification::route('/{record}'),
        ];
    }
}
