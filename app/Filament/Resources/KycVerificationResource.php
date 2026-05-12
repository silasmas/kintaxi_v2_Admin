<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycVerificationResource\Pages;
use App\Filament\Support\UpdateKycVerificationStatusTableAction;
use App\Models\KycVerification;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
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
                Section::make('Synthèse Smile ID')
                    ->description('Métadonnées lisibles (sans JSON brut). Les fichiers visualisables figurent dans la section « Pièce et photos soumises ».')
                    ->schema([
                        ViewEntry::make('smile_summary')
                            ->label('')
                            ->view('filament.infolists.entries.kyc-smile-summary')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(false),
                Section::make('Données techniques (JSON brut)')
                    ->description('Réservé au débogage — inclut résultat Smile et payload callback.')
                    ->schema([
                        TextEntry::make('smile_result_json')
                            ->label('smile_result_json')
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
                        TextEntry::make('callback_payload_json')
                            ->label('callback_payload_json')
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
                    ->view('filament.tables.columns.owner-with-avatar')
                    ->sticky(),
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
                    })
                    ->sticky(),
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
                UpdateKycVerificationStatusTableAction::make(),
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
